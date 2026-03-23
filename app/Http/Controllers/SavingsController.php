<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Category;
use App\Models\SavingsTransfer;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SavingsController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $selectedScope = $this->resolveSelectedScope($request->query('scope'));
        $anchorDate = $this->resolveAnchorDate($request->query('anchor'), $selectedScope);
        [$rangeStart, $rangeEnd] = $this->resolveScopeRange($anchorDate, $selectedScope);

        $savingsTransactions = Transaction::query()
            ->with(['category', 'account', 'destinationSavingsTransfer'])
            ->where('user_id', $user->id)
            ->where('type', 'savings')
            ->whereBetween('occurred_at', [
                $rangeStart,
                $rangeEnd,
            ])
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->get();

        $savingsTransfers = SavingsTransfer::query()
            ->with(['sourceCategory', 'destinationCategory', 'account', 'incomeTransaction', 'savingsTransaction'])
            ->where('user_id', $user->id)
            ->whereBetween('transferred_at', [
                $rangeStart,
                $rangeEnd,
            ])
            ->orderByDesc('transferred_at')
            ->orderByDesc('id')
            ->get();

        $categoryBalances = $this->buildCategoryBalances($savingsTransactions, $savingsTransfers);
        $historyItems = $this->buildHistoryItems($savingsTransactions, $savingsTransfers, $categoryBalances);
        $accounts = Account::query()
            ->where('user_id', $user->id)
            ->orderBy('name')
            ->pluck('name')
            ->values()
            ->all();

        return view('public.savings', [
            'savingsPayload' => [
                'totalWorth' => round($categoryBalances->sum('amount'), 2),
                'categories' => $categoryBalances->values()->all(),
                'history' => $historyItems->all(),
                'pieGradient' => $this->buildPieGradient($categoryBalances),
                'accounts' => $accounts,
                'defaultTransferDate' => $anchorDate->isSameMonth(now())
                    ? now()->format('Y-m-d')
                    : $rangeStart->copy()->format('Y-m-d'),
                'scope' => $selectedScope,
                'anchorDate' => $anchorDate->format('Y-m-d'),
                'periodLabel' => $this->formatPeriodLabel($anchorDate, $selectedScope),
                'previousPeriodUrl' => route('savings.index', [
                    'scope' => $selectedScope,
                    'anchor' => $this->shiftAnchorDate($anchorDate, $selectedScope, -1)->format('Y-m-d'),
                ]),
                'nextPeriodUrl' => route('savings.index', [
                    'scope' => $selectedScope,
                    'anchor' => $this->shiftAnchorDate($anchorDate, $selectedScope, 1)->format('Y-m-d'),
                ]),
                'scopeUrls' => [
                    'week' => route('savings.index', ['scope' => 'week', 'anchor' => $anchorDate->format('Y-m-d')]),
                    'month' => route('savings.index', ['scope' => 'month', 'anchor' => $anchorDate->format('Y-m-d')]),
                    'year' => route('savings.index', ['scope' => 'year', 'anchor' => $anchorDate->format('Y-m-d')]),
                ],
            ],
        ]);
    }

    public function createTransfer(Request $request): View
    {
        $requestedDateValue = $request->query('date');
        $prefilledTransferDateValue = now()->format('m/d/Y');

        if (is_string($requestedDateValue) && $requestedDateValue !== '') {
            try {
                $prefilledTransferDateValue = Carbon::createFromFormat('Y-m-d', $requestedDateValue)
                    ->format('m/d/Y');
            } catch (\Throwable) {
                $prefilledTransferDateValue = now()->format('m/d/Y');
            }
        }

        return view('public.savings-transfer-create', [
            ...$this->buildTransferFormViewData(
                auth()->id(),
                null,
                [
                    'transferTypeValue' => old('transfer_type', 'savings_to_savings'),
                    'transferDateValue' => old('transfer_date', $prefilledTransferDateValue),
                    'transferAmountValue' => old('amount', ''),
                    'transferCategoryValue' => old('source_category_id', ''),
                    'transferDestinationCategoryValue' => old('destination_category_id', ''),
                    'transferAccountValue' => old('account', ''),
                    'transferDescriptionValue' => old('description', ''),
                ]
            ),
        ]);
    }

    public function transfer(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'transfer_type' => ['required', 'in:savings_to_savings,savings_to_income'],
            'source_category_id' => ['required', 'integer'],
            'destination_category_id' => ['nullable', 'integer'],
            'amount' => ['required', 'string'],
            'transfer_date' => ['required', 'date_format:m/d/Y'],
            'account' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:255'],
            'receipt_photos' => ['nullable', 'array'],
            'receipt_photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'receipt_photo_camera' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'existing_receipt_photo_paths' => ['nullable', 'string'],
        ]);

        $sourceCategory = Category::query()
            ->where('user_id', $user->id)
            ->where('type', 'savings')
            ->whereKey($validated['source_category_id'])
            ->firstOrFail();

        $normalizedAmount = $this->normalizeAmount($validated['amount']);

        if ($normalizedAmount === null) {
            return back()
                ->withErrors([
                    'amount' => 'Please enter a valid amount.',
                ])
                ->withInput();
        }

        $transferAmount = round((float) $normalizedAmount, 2);

        $transferredAt = Carbon::createFromFormat('m/d/Y', $validated['transfer_date'])
            ->setTime(now()->hour, now()->minute, now()->second);

        $availableAmount = $this->resolveAvailableCategoryAmount($user->id, $sourceCategory->id);

        if ($transferAmount > $availableAmount) {
            return back()
                ->withErrors([
                    'transfer_amount' => 'The transfer amount is higher than the available savings in this category.',
                ])
                ->withInput();
        }

        if ($validated['transfer_type'] === 'savings_to_savings') {
            $destinationCategoryId = (int) ($validated['destination_category_id'] ?? 0);

            if ($destinationCategoryId <= 0) {
                return back()
                    ->withErrors([
                        'destination_category_id' => 'Please select a savings category to transfer into.',
                    ])
                    ->withInput();
            }

            if ($destinationCategoryId === (int) $sourceCategory->id) {
                return back()
                    ->withErrors([
                        'destination_category_id' => 'Please choose a different destination savings category.',
                    ])
                    ->withInput();
            }

            $destinationCategory = Category::query()
                ->where('user_id', $user->id)
                ->where('type', 'savings')
                ->whereKey($destinationCategoryId)
                ->firstOrFail();

            $this->createSavingsToSavingsTransfer(
                $user->id,
                $sourceCategory,
                $destinationCategory,
                $transferAmount,
                $transferredAt,
                $validated,
                $request
            );

            return redirect()
                ->route('savings.index')
                ->with('savings_success', 'Savings moved successfully.');
        }

        $accountName = trim((string) ($validated['account'] ?? ''));

        if ($accountName === '') {
            return back()
                ->withErrors([
                    'account' => 'Please select the income account to withdraw into.',
                ])
                ->withInput();
        }

        $account = Account::firstOrCreate([
            'user_id' => $user->id,
            'name' => $accountName,
        ]);

        $this->createSavingsToIncomeTransfer(
            $user->id,
            $sourceCategory,
            $account,
            $transferAmount,
            $transferredAt,
            $validated,
            $request
        );

        return redirect()
            ->route('savings.index')
            ->with('savings_success', 'Savings transferred to income successfully.');
    }

    public function editTransfer(SavingsTransfer $savingsTransfer): View
    {
        $this->authorizeSavingsTransfer($savingsTransfer);

        $savingsTransfer->load(['sourceCategory', 'destinationCategory', 'account', 'incomeTransaction', 'savingsTransaction']);

        return view('public.savings-transfer-create', [
            ...$this->buildTransferFormViewData(
                $savingsTransfer->user_id,
                $savingsTransfer,
                [
                    'transferTypeValue' => old(
                        'transfer_type',
                        $savingsTransfer->destination_category_id ? 'savings_to_savings' : 'savings_to_income'
                    ),
                    'transferDateValue' => old('transfer_date', $savingsTransfer->transferred_at->format('m/d/Y')),
                    'transferAmountValue' => old('amount', number_format((float) $savingsTransfer->amount, 2, '.', '')),
                    'transferCategoryValue' => old('source_category_id', (string) $savingsTransfer->source_category_id),
                    'transferDestinationCategoryValue' => old('destination_category_id', (string) $savingsTransfer->destination_category_id),
                    'transferAccountValue' => old('account', $savingsTransfer->account?->name ?? ''),
                    'transferDescriptionValue' => old('description', $savingsTransfer->description ?? ''),
                ]
            ),
        ]);
    }

    public function updateTransfer(Request $request, SavingsTransfer $savingsTransfer): RedirectResponse
    {
        $this->authorizeSavingsTransfer($savingsTransfer);

        $validated = $request->validate([
            'transfer_type' => ['required', 'in:savings_to_savings,savings_to_income'],
            'source_category_id' => ['required', 'integer'],
            'destination_category_id' => ['nullable', 'integer'],
            'amount' => ['required', 'string'],
            'transfer_date' => ['required', 'date_format:m/d/Y'],
            'account' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:255'],
            'receipt_photos' => ['nullable', 'array'],
            'receipt_photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'receipt_photo_camera' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'existing_receipt_photo_paths' => ['nullable', 'string'],
        ]);

        $user = auth()->user();
        $sourceCategory = Category::query()
            ->where('user_id', $user->id)
            ->where('type', 'savings')
            ->whereKey($validated['source_category_id'])
            ->firstOrFail();

        $normalizedAmount = $this->normalizeAmount($validated['amount']);

        if ($normalizedAmount === null) {
            return back()
                ->withErrors([
                    'amount' => 'Please enter a valid amount.',
                ])
                ->withInput();
        }

        $availableAmount = $this->resolveAvailableCategoryAmount($user->id, $sourceCategory->id, $savingsTransfer->id);
        $transferAmount = round((float) $normalizedAmount, 2);

        if ($transferAmount > $availableAmount) {
            return back()
                ->withErrors([
                    'transfer_amount' => 'The transfer amount is higher than the available savings in this category.',
                ])
                ->withInput();
        }

        $transferredAt = Carbon::createFromFormat('m/d/Y', $validated['transfer_date'])
            ->setTime(now()->hour, now()->minute, now()->second);

        if ($validated['transfer_type'] === 'savings_to_savings') {
            $destinationCategoryId = (int) ($validated['destination_category_id'] ?? 0);

            if ($destinationCategoryId <= 0) {
                return back()
                    ->withErrors([
                        'destination_category_id' => 'Please select a savings category to transfer into.',
                    ])
                    ->withInput();
            }

            if ($destinationCategoryId === (int) $sourceCategory->id) {
                return back()
                    ->withErrors([
                        'destination_category_id' => 'Please choose a different destination savings category.',
                    ])
                    ->withInput();
            }

            $destinationCategory = Category::query()
                ->where('user_id', $user->id)
                ->where('type', 'savings')
                ->whereKey($destinationCategoryId)
                ->firstOrFail();

            $this->updateSavingsToSavingsTransfer(
                $savingsTransfer,
                $user->id,
                $sourceCategory,
                $destinationCategory,
                $transferAmount,
                $transferredAt,
                $validated,
                $request
            );

            return redirect()
                ->route('savings.index')
                ->with('savings_success', 'Savings transfer updated successfully.');
        }

        $accountName = trim((string) ($validated['account'] ?? ''));

        if ($accountName === '') {
            return back()
                ->withErrors([
                    'account' => 'Please select the income account to withdraw into.',
                ])
                ->withInput();
        }

        $account = Account::firstOrCreate([
            'user_id' => $user->id,
            'name' => $accountName,
        ]);

        $this->updateSavingsToIncomeTransfer(
            $savingsTransfer,
            $user->id,
            $sourceCategory,
            $account,
            $transferAmount,
            $transferredAt,
            $validated,
            $request
        );

        return redirect()
            ->route('savings.index')
            ->with('savings_success', 'Savings transfer updated successfully.');
    }

    public function destroyTransfer(SavingsTransfer $savingsTransfer): RedirectResponse
    {
        $this->authorizeSavingsTransfer($savingsTransfer);

        DB::transaction(function () use ($savingsTransfer) {
            $this->deleteLinkedTransferTransaction($savingsTransfer->incomeTransaction);
            $this->deleteLinkedTransferTransaction($savingsTransfer->savingsTransaction);
            $savingsTransfer->delete();
        });

        return redirect()
            ->route('savings.index')
            ->with('savings_success', 'Savings transfer deleted successfully.');
    }

    private function createSavingsToSavingsTransfer(
        int $userId,
        Category $sourceCategory,
        Category $destinationCategory,
        float $transferAmount,
        Carbon $transferredAt,
        array $validated,
        Request $request
    ): void {
        DB::transaction(function () use (
            $userId,
            $sourceCategory,
            $destinationCategory,
            $transferAmount,
            $transferredAt,
            $validated,
            $request
        ) {
            $description = trim((string) ($validated['description'] ?? ''));
            $transferDescription = $description !== ''
                ? $description
                : 'Transferred from ' . $sourceCategory->name . ' to ' . $destinationCategory->name;
            $receiptPhotoPaths = $this->storeReceiptPhotos($request);

            $savingsTransaction = Transaction::create([
                'user_id' => $userId,
                'type' => 'savings',
                'amount' => $transferAmount,
                'category_id' => $destinationCategory->id,
                'account_id' => null,
                'occurred_at' => $transferredAt,
                'description' => $transferDescription,
                'receipt_photo_path' => $receiptPhotoPaths[0] ?? null,
                'receipt_photo_paths' => $receiptPhotoPaths,
            ]);

            SavingsTransfer::create([
                'user_id' => $userId,
                'source_category_id' => $sourceCategory->id,
                'destination_category_id' => $destinationCategory->id,
                'account_id' => null,
                'savings_transaction_id' => $savingsTransaction->id,
                'income_transaction_id' => null,
                'amount' => $transferAmount,
                'transferred_at' => $transferredAt,
                'description' => $transferDescription,
            ]);
        });
    }

    private function createSavingsToIncomeTransfer(
        int $userId,
        Category $sourceCategory,
        Account $account,
        float $transferAmount,
        Carbon $transferredAt,
        array $validated,
        Request $request
    ): void {
        $incomeCategory = Category::firstOrCreate([
            'user_id' => $userId,
            'type' => 'income',
            'name' => 'Savings Transfer',
        ]);

        DB::transaction(function () use (
            $userId,
            $sourceCategory,
            $account,
            $incomeCategory,
            $transferAmount,
            $transferredAt,
            $validated,
            $request
        ) {
            $description = trim((string) ($validated['description'] ?? ''));
            $transferDescription = $description !== ''
                ? $description
                : 'Transferred from savings: ' . $sourceCategory->name;
            $receiptPhotoPaths = $this->storeReceiptPhotos($request);

            $incomeTransaction = Transaction::create([
                'user_id' => $userId,
                'type' => 'income',
                'amount' => $transferAmount,
                'category_id' => $incomeCategory->id,
                'account_id' => $account->id,
                'occurred_at' => $transferredAt,
                'description' => $transferDescription,
                'receipt_photo_path' => $receiptPhotoPaths[0] ?? null,
                'receipt_photo_paths' => $receiptPhotoPaths,
            ]);

            SavingsTransfer::create([
                'user_id' => $userId,
                'source_category_id' => $sourceCategory->id,
                'destination_category_id' => null,
                'account_id' => $account->id,
                'savings_transaction_id' => null,
                'income_transaction_id' => $incomeTransaction->id,
                'amount' => $transferAmount,
                'transferred_at' => $transferredAt,
                'description' => $transferDescription,
            ]);
        });
    }

    private function updateSavingsToSavingsTransfer(
        SavingsTransfer $savingsTransfer,
        int $userId,
        Category $sourceCategory,
        Category $destinationCategory,
        float $transferAmount,
        Carbon $transferredAt,
        array $validated,
        Request $request
    ): void {
        DB::transaction(function () use (
            $savingsTransfer,
            $userId,
            $sourceCategory,
            $destinationCategory,
            $transferAmount,
            $transferredAt,
            $validated,
            $request
        ) {
            $description = trim((string) ($validated['description'] ?? ''));
            $transferDescription = $description !== ''
                ? $description
                : 'Transferred from ' . $sourceCategory->name . ' to ' . $destinationCategory->name;

            $this->deleteLinkedTransferTransaction($savingsTransfer->incomeTransaction);

            $savingsTransaction = $savingsTransfer->savingsTransaction;

            if (!$savingsTransaction || $savingsTransaction->user_id !== $userId) {
                $savingsTransaction = new Transaction([
                    'user_id' => $userId,
                    'type' => 'savings',
                ]);
            }

            $receiptPhotoPaths = $this->resolveUpdatedReceiptPhotoPaths($request, $savingsTransaction);

            $savingsTransaction->fill([
                'type' => 'savings',
                'amount' => $transferAmount,
                'category_id' => $destinationCategory->id,
                'account_id' => null,
                'occurred_at' => $transferredAt,
                'description' => $transferDescription,
                'receipt_photo_path' => $receiptPhotoPaths[0] ?? null,
                'receipt_photo_paths' => $receiptPhotoPaths,
            ]);
            $savingsTransaction->save();

            $savingsTransfer->update([
                'source_category_id' => $sourceCategory->id,
                'destination_category_id' => $destinationCategory->id,
                'account_id' => null,
                'savings_transaction_id' => $savingsTransaction->id,
                'income_transaction_id' => null,
                'amount' => $transferAmount,
                'transferred_at' => $transferredAt,
                'description' => $transferDescription,
            ]);
        });
    }

    private function updateSavingsToIncomeTransfer(
        SavingsTransfer $savingsTransfer,
        int $userId,
        Category $sourceCategory,
        Account $account,
        float $transferAmount,
        Carbon $transferredAt,
        array $validated,
        Request $request
    ): void {
        $incomeCategory = Category::firstOrCreate([
            'user_id' => $userId,
            'type' => 'income',
            'name' => 'Savings Transfer',
        ]);

        DB::transaction(function () use (
            $savingsTransfer,
            $userId,
            $sourceCategory,
            $account,
            $incomeCategory,
            $transferAmount,
            $transferredAt,
            $validated,
            $request
        ) {
            $description = trim((string) ($validated['description'] ?? ''));
            $transferDescription = $description !== ''
                ? $description
                : 'Transferred from savings: ' . $sourceCategory->name;

            $this->deleteLinkedTransferTransaction($savingsTransfer->savingsTransaction);

            $incomeTransaction = $savingsTransfer->incomeTransaction;

            if (!$incomeTransaction || $incomeTransaction->user_id !== $userId) {
                $incomeTransaction = new Transaction([
                    'user_id' => $userId,
                    'type' => 'income',
                ]);
            }

            $receiptPhotoPaths = $this->resolveUpdatedReceiptPhotoPaths($request, $incomeTransaction);

            $incomeTransaction->fill([
                'type' => 'income',
                'amount' => $transferAmount,
                'category_id' => $incomeCategory->id,
                'account_id' => $account->id,
                'occurred_at' => $transferredAt,
                'description' => $transferDescription,
                'receipt_photo_path' => $receiptPhotoPaths[0] ?? null,
                'receipt_photo_paths' => $receiptPhotoPaths,
            ]);
            $incomeTransaction->save();

            $savingsTransfer->update([
                'source_category_id' => $sourceCategory->id,
                'destination_category_id' => null,
                'account_id' => $account->id,
                'savings_transaction_id' => null,
                'income_transaction_id' => $incomeTransaction->id,
                'amount' => $transferAmount,
                'transferred_at' => $transferredAt,
                'description' => $transferDescription,
            ]);
        });
    }

    private function buildCategoryBalances(Collection $transactions, Collection $transfers): Collection
    {
        $baseAmounts = $transactions
            ->groupBy(fn (Transaction $transaction) => $transaction->category_id ?: 'uncategorized')
            ->map(function (Collection $items) {
                $category = $items->first()->category;
                $categoryName = $category?->name ?: 'Others';
                $categoryKey = $this->normalizeCategoryKey($categoryName);

                return [
                    'categoryId' => $category?->id,
                    'key' => $categoryKey,
                    'name' => $categoryName,
                    'amount' => (float) $items->sum('amount'),
                    'txCount' => $items->count(),
                    'iconPath' => $this->resolveSavingsIcon($categoryName),
                ];
            });

        $transferredAmounts = $transfers
            ->groupBy(fn (SavingsTransfer $transfer) => $transfer->source_category_id ?: 'uncategorized')
            ->map(fn (Collection $items) => (float) $items->sum('amount'));

        $palette = ['#0d47a1', '#1565c0', '#1e88e5', '#42a5f5', '#90caf9', '#d6ebff'];

        return $baseAmounts
            ->values()
            ->map(function (array $category, int $index) use ($transferredAmounts, $palette) {
                $categoryAmount = $category['amount'] - (float) ($transferredAmounts[$category['categoryId'] ?: 'uncategorized'] ?? 0);
                $category['amount'] = round(max($categoryAmount, 0), 2);
                $category['color'] = $palette[$index % count($palette)];
                return $category;
            })
            ->filter(fn (array $category) => $category['amount'] > 0)
            ->sortByDesc('amount')
            ->values();
    }

    private function buildTransferFormViewData(int $userId, ?SavingsTransfer $editingTransfer = null, array $overrides = []): array
    {
        $savingsTransactions = Transaction::query()
            ->with(['category', 'account', 'destinationSavingsTransfer'])
            ->where('user_id', $userId)
            ->where('type', 'savings')
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->get();

        $savingsTransfers = SavingsTransfer::query()
            ->with(['sourceCategory', 'destinationCategory', 'account', 'incomeTransaction', 'savingsTransaction'])
            ->where('user_id', $userId)
            ->when($editingTransfer, fn ($query) => $query->where('id', '!=', $editingTransfer->id))
            ->orderByDesc('transferred_at')
            ->orderByDesc('id')
            ->get();

        $transferCategories = $this->buildCategoryBalances($savingsTransactions, $savingsTransfers);

        if ($editingTransfer && $editingTransfer->sourceCategory) {
            $editingCategoryId = $editingTransfer->source_category_id;
            $categoryExists = $transferCategories->contains(
                fn (array $category) => (int) ($category['categoryId'] ?? 0) === (int) $editingCategoryId
            );

            if (!$categoryExists) {
                $transferCategories->push([
                    'categoryId' => $editingCategoryId,
                    'key' => $this->normalizeCategoryKey($editingTransfer->sourceCategory->name),
                    'name' => $editingTransfer->sourceCategory->name,
                    'amount' => round((float) $editingTransfer->amount, 2),
                    'txCount' => 0,
                    'iconPath' => $this->resolveSavingsIcon($editingTransfer->sourceCategory->name),
                    'color' => '#1e88e5',
                ]);
            }
        }

        $storedAccountOptions = Account::query()
            ->where('user_id', $userId)
            ->orderBy('name')
            ->pluck('name')
            ->values()
            ->all();

        $defaultIncomeAccountOptions = [
            'Cash',
            'Bank',
            'Card',
            'Petty Cash',
        ];

        $accountOptions = collect([
            ...$defaultIncomeAccountOptions,
            ...$storedAccountOptions,
        ])
            ->filter(fn ($accountName) => is_string($accountName) && trim($accountName) !== '')
            ->map(fn ($accountName) => trim($accountName))
            ->unique(fn ($accountName) => Str::lower($accountName))
            ->values()
            ->all();

        return array_merge([
            'transferCategories' => $transferCategories->sortBy('name')->values(),
            'accountOptions' => $accountOptions,
            'transferTypeValue' => 'savings_to_savings',
            'transferTypeLocked' => false,
            'transferDateValue' => '',
            'transferAmountValue' => '',
            'transferCategoryValue' => '',
            'transferDestinationCategoryValue' => '',
            'transferAccountValue' => '',
            'transferDescriptionValue' => '',
            'transferExistingPhotoPaths' => $this->resolveTransferExistingPhotoPaths($editingTransfer, $overrides['transferExistingPhotoPaths'] ?? old('existing_receipt_photo_paths')),
            'transferFormAction' => $editingTransfer
                ? route('savings.transfer.update', $editingTransfer)
                : route('savings.transfer'),
            'transferFormMethod' => $editingTransfer ? 'PUT' : 'POST',
            'transferPageTitle' => $editingTransfer ? 'Edit Transfer' : 'Transfer',
            'transferSubmitLabel' => $editingTransfer ? 'Save' : 'Confirm',
            'transferCancelUrl' => route('savings.index'),
        ], $overrides);
    }

    private function buildHistoryItems(Collection $transactions, Collection $transfers, Collection $categoryBalances): Collection
    {
        $categoryColors = $categoryBalances
            ->mapWithKeys(fn (array $category) => [$category['key'] => $category['color']])
            ->all();

        $depositItems = $transactions
            ->reject(fn (Transaction $transaction) => $transaction->destinationSavingsTransfer !== null)
            ->map(function (Transaction $transaction) use ($categoryColors) {
            $categoryName = $transaction->category?->name ?: 'Others';
            $categoryKey = $this->normalizeCategoryKey($categoryName);

            return [
                'id' => 'transaction-' . $transaction->id,
                'timestamp' => $transaction->occurred_at->timestamp,
                'dateLabel' => $transaction->occurred_at->format('D, F d'),
                'time' => $transaction->occurred_at->format('g:ia'),
                'direction' => 'in',
                'kind' => 'saved',
                'typeLabel' => 'Savings',
                'category' => $categoryName,
                'account' => $transaction->account?->name ?? '',
                'amount' => (float) $transaction->amount,
                'description' => $transaction->description ?? '',
                'iconPath' => $this->resolveSavingsIcon($categoryName),
                'categoryColor' => $categoryColors[$categoryKey] ?? '#2d9af0',
                'receiptPhotoUrls' => collect($this->getTransactionPhotoPaths($transaction))
                    ->map(fn (string $photoPath) => Storage::url($photoPath))
                    ->values()
                    ->all(),
                'editUrl' => route('transaction.edit', $transaction),
                'deleteUrl' => route('transaction.destroy', $transaction),
                'editLabel' => 'Edit Transaction',
                'deleteLabel' => 'Delete Transaction',
            ];
        });

        $transferItems = $transfers->map(function (SavingsTransfer $transfer) use ($categoryColors) {
            $categoryName = $transfer->sourceCategory?->name ?: 'Others';
            $categoryKey = $this->normalizeCategoryKey($categoryName);
            $isSavingsToSavings = $transfer->destinationCategory !== null;
            $targetLabel = $isSavingsToSavings
                ? ($transfer->destinationCategory?->name ?? 'Savings')
                : ($transfer->account?->name ?? '');
            $linkedTransaction = $isSavingsToSavings
                ? $transfer->savingsTransaction
                : $transfer->incomeTransaction;

            return [
                'id' => 'transfer-' . $transfer->id,
                'timestamp' => $transfer->transferred_at->timestamp,
                'dateLabel' => $transfer->transferred_at->format('D, F d'),
                'time' => $transfer->transferred_at->format('g:ia'),
                'direction' => 'out',
                'kind' => 'transfer',
                'typeLabel' => $isSavingsToSavings ? 'Transfer to Savings' : 'Withdraw to Income',
                'category' => $categoryName,
                'account' => $targetLabel,
                'amount' => (float) $transfer->amount,
                'description' => $transfer->description ?? '',
                'iconPath' => $this->resolveSavingsIcon($categoryName),
                'categoryColor' => $categoryColors[$categoryKey] ?? '#2d9af0',
                'receiptPhotoUrls' => $linkedTransaction
                    ? collect($this->getTransactionPhotoPaths($linkedTransaction))
                        ->map(fn (string $photoPath) => Storage::url($photoPath))
                        ->values()
                        ->all()
                    : [],
                'editUrl' => route('savings.transfer.edit', $transfer),
                'deleteUrl' => route('savings.transfer.destroy', $transfer),
                'editLabel' => 'Edit Transfer',
                'deleteLabel' => 'Delete Transfer',
            ];
        });

        return $depositItems
            ->concat($transferItems)
            ->sortByDesc('timestamp')
            ->values();
    }

    private function buildPieGradient(Collection $categories): string
    {
        if ($categories->isEmpty()) {
            return 'conic-gradient(#e9edf2 0deg 360deg)';
        }

        $total = (float) $categories->sum('amount');
        $currentAngle = 0.0;
        $segments = [];
        $useSeparators = $categories->count() > 1;
        $separatorSize = 1.2;

        foreach ($categories as $index => $category) {
            $portion = $total > 0 ? ((float) $category['amount'] / $total) * 360 : 0;
            $start = $currentAngle;
            $end = $index === $categories->count() - 1 ? 360 : $currentAngle + $portion;

            if ($useSeparators && $index === 0) {
                $segments[] = sprintf('#ffffff %.2fdeg %.2fdeg', 0, min($separatorSize, $end));
                $start += $separatorSize;
            } elseif ($useSeparators && $index > 0) {
                $segments[] = sprintf('#ffffff %.2fdeg %.2fdeg', $start, min($start + $separatorSize, $end));
                $start += $separatorSize;
            }

            $segments[] = sprintf('%s %.2fdeg %.2fdeg', $category['color'], $start, $end);
            $currentAngle = $end;
        }

        return 'conic-gradient(' . implode(', ', $segments) . ')';
    }

    private function resolveAvailableCategoryAmount(int $userId, int $categoryId, ?int $ignoredTransferId = null): float
    {
        $savedAmount = (float) Transaction::query()
            ->where('user_id', $userId)
            ->where('type', 'savings')
            ->where('category_id', $categoryId)
            ->sum('amount');

        $transferredAmount = (float) SavingsTransfer::query()
            ->where('user_id', $userId)
            ->where('source_category_id', $categoryId)
            ->when($ignoredTransferId, fn ($query) => $query->where('id', '!=', $ignoredTransferId))
            ->sum('amount');

        return max($savedAmount - $transferredAmount, 0);
    }

    private function getTransactionPhotoPaths(Transaction $transaction): array
    {
        $photoPaths = is_array($transaction->receipt_photo_paths)
            ? $transaction->receipt_photo_paths
            : [];

        if (empty($photoPaths) && $transaction->receipt_photo_path) {
            $photoPaths = [$transaction->receipt_photo_path];
        }

        return array_values(array_filter($photoPaths));
    }

    private function decodeExistingPhotoPaths(?string $rawValue): array
    {
        if (!$rawValue) {
            return [];
        }

        $decodedValue = json_decode($rawValue, true);

        if (!is_array($decodedValue)) {
            return [];
        }

        return array_values(array_filter($decodedValue));
    }

    private function normalizeAmount(string $amount): ?string
    {
        $normalizedAmount = preg_replace('/[^\d.]/', '', $amount);

        if (!$normalizedAmount || !is_numeric($normalizedAmount)) {
            return null;
        }

        return $normalizedAmount;
    }

    private function storeReceiptPhotos(Request $request): array
    {
        $storedPaths = [];

        $galleryFiles = $request->file('receipt_photos', []);

        if (is_array($galleryFiles)) {
            foreach ($galleryFiles as $uploadedFile) {
                if ($uploadedFile) {
                    $storedPaths[] = $uploadedFile->store('transaction-photos', 'public');
                }
            }
        }

        $cameraFile = $request->file('receipt_photo_camera');

        if ($cameraFile) {
            $storedPaths[] = $cameraFile->store('transaction-photos', 'public');
        }

        return array_values(array_filter($storedPaths));
    }

    private function resolveUpdatedReceiptPhotoPaths(Request $request, ?Transaction $transaction = null): array
    {
        $currentPhotoPaths = $transaction ? $this->getTransactionPhotoPaths($transaction) : [];
        $keptExistingPhotoPaths = $this->decodeExistingPhotoPaths(
            $request->input('existing_receipt_photo_paths')
        );

        $removedPhotoPaths = array_diff($currentPhotoPaths, $keptExistingPhotoPaths);

        foreach ($removedPhotoPaths as $removedPhotoPath) {
            Storage::disk('public')->delete($removedPhotoPath);
        }

        $newPhotoPaths = $this->storeReceiptPhotos($request);

        return array_values(array_filter([
            ...$keptExistingPhotoPaths,
            ...$newPhotoPaths,
        ]));
    }

    private function deleteLinkedTransferTransaction(?Transaction $transaction): void
    {
        if (!$transaction) {
            return;
        }

        foreach ($this->getTransactionPhotoPaths($transaction) as $photoPath) {
            Storage::disk('public')->delete($photoPath);
        }

        $transaction->delete();
    }

    private function resolveTransferExistingPhotoPaths(?SavingsTransfer $editingTransfer, mixed $oldExistingPhotoPaths = null): array
    {
        if (is_string($oldExistingPhotoPaths) && $oldExistingPhotoPaths !== '') {
            $decodedOldPhotoPaths = json_decode($oldExistingPhotoPaths, true);

            if (is_array($decodedOldPhotoPaths)) {
                return array_values(array_filter($decodedOldPhotoPaths));
            }
        }

        $linkedTransaction = $editingTransfer?->incomeTransaction ?? $editingTransfer?->savingsTransaction;

        if (!$linkedTransaction) {
            return [];
        }

        return $this->getTransactionPhotoPaths($linkedTransaction);
    }

    private function authorizeSavingsTransfer(SavingsTransfer $savingsTransfer): void
    {
        abort_unless(
            auth()->check() && auth()->id() === $savingsTransfer->user_id,
            403
        );
    }

    private function normalizeCategoryKey(string $categoryName): string
    {
        return Str::of($categoryName)
            ->lower()
            ->trim()
            ->replace('&', 'and')
            ->replace('/', ' ')
            ->replace('-', ' ')
            ->squish()
            ->replace(' ', '_')
            ->value();
    }

    private function resolveSavingsIcon(string $categoryName): string
    {
        $normalized = Str::of($categoryName)
            ->lower()
            ->trim()
            ->replace('&', 'and')
            ->replace('/', '')
            ->replace('-', '')
            ->replace(' ', '')
            ->value();

        $icons = [
            'emergency' => '/projectassets/icons/savings.svg',
            'retirement' => '/projectassets/icons/savings.svg',
            'travel' => '/projectassets/icons/savings.svg',
            'investment' => '/projectassets/icons/savings.svg',
            'insurance' => '/projectassets/icons/savings.svg',
            'family' => '/projectassets/icons/savings.svg',
            'goal' => '/projectassets/icons/savings.svg',
            'house' => '/projectassets/icons/savings.svg',
            'gadget' => '/projectassets/icons/savings.svg',
            'car' => '/projectassets/icons/savings.svg',
            'others' => '/projectassets/icons/others.svg',
        ];

        return $icons[$normalized] ?? '/projectassets/icons/savings.svg';
    }

    private function resolveSelectedScope(?string $scope): string
    {
        return in_array($scope, ['week', 'month', 'year'], true)
            ? $scope
            : 'month';
    }

    private function resolveAnchorDate(?string $anchor, string $scope): Carbon
    {
        if (is_string($anchor) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $anchor) === 1) {
            try {
                return Carbon::createFromFormat('Y-m-d', $anchor)->startOfDay();
            } catch (\Throwable) {
                // fall through
            }
        }

        return match ($scope) {
            'week' => now()->startOfWeek(Carbon::MONDAY),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };
    }

    private function resolveScopeRange(Carbon $anchorDate, string $scope): array
    {
        return match ($scope) {
            'week' => [
                $anchorDate->copy()->startOfWeek(Carbon::MONDAY),
                $anchorDate->copy()->endOfWeek(Carbon::SUNDAY),
            ],
            'year' => [
                $anchorDate->copy()->startOfYear(),
                $anchorDate->copy()->endOfYear(),
            ],
            default => [
                $anchorDate->copy()->startOfMonth(),
                $anchorDate->copy()->endOfMonth(),
            ],
        };
    }

    private function shiftAnchorDate(Carbon $anchorDate, string $scope, int $shift): Carbon
    {
        return match ($scope) {
            'week' => $anchorDate->copy()->addWeeks($shift),
            'year' => $anchorDate->copy()->addYears($shift),
            default => $anchorDate->copy()->addMonths($shift),
        };
    }

    private function formatPeriodLabel(Carbon $anchorDate, string $scope): string
    {
        return match ($scope) {
            'week' => sprintf(
                '%s-%s',
                $anchorDate->copy()->startOfWeek(Carbon::MONDAY)->format('m.d'),
                $anchorDate->copy()->endOfWeek(Carbon::SUNDAY)->format('m.d')
            ),
            'year' => $anchorDate->format('Y'),
            default => $anchorDate->format('F Y'),
        };
    }
}
