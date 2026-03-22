<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $transactions = Transaction::query()
            ->with(['category', 'account'])
            ->where('user_id', $user->id)
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->get();

        $initialDisplayDate = $transactions->isNotEmpty()
            ? $transactions->first()->occurred_at->format('Y-m-d')
            : now()->format('Y-m-d');

        return view('public.transactions', [
            'transactionAnalyticsPayload' => [
                'transactionGroups' => $this->buildTransactionGroups($transactions),
                'initialDisplayDate' => $initialDisplayDate,
                'categoryMeta' => $this->buildCategoryMetaPayload($transactions, $user->id),
                'budgetSnapshots' => $this->buildBudgetSnapshotsPayload($transactions, $user->id),
            ],
        ]);
    }

    public function create(): View
    {
        $user = auth()->user();
        $transactions = $user
            ? Transaction::query()
                ->with(['category', 'account'])
                ->where('user_id', $user->id)
                ->orderByDesc('occurred_at')
                ->orderByDesc('id')
                ->get()
            : collect();

        return view('public.transaction-create', [
            'transaction' => null,
            'budgetGuardPayload' => $user
                ? $this->buildBudgetGuardPayload($transactions, $user->id)
                : ['budgetSnapshots' => [], 'spentByMonthCategory' => []],
        ]);
    }

    public function edit(Transaction $transaction): View
    {
        $this->authorizeTransaction($transaction);

        $transaction->load(['category', 'account']);
        $transactions = Transaction::query()
            ->with(['category', 'account'])
            ->where('user_id', $transaction->user_id)
            ->where('id', '!=', $transaction->id)
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->get();

        return view('public.transaction-create', [
            'transaction' => $transaction,
            'budgetGuardPayload' => $this->buildBudgetGuardPayload($transactions, $transaction->user_id),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()
                ->route('login.view')
                ->withErrors([
                    'email' => 'Please log in again.',
                ]);
        }

        $validatedData = $this->validateTransactionData($request);

        $normalizedAmount = $this->normalizeAmount($validatedData['amount']);

        if ($normalizedAmount === null) {
            return back()
                ->withErrors([
                    'amount' => 'Please enter a valid amount.',
                ])
                ->withInput();
        }

        $transactionType = $validatedData['transaction_type'];
        $occurredAt = $this->buildOccurredAt($validatedData['transaction_date']);

        $categoryId = $this->resolveCategoryId(
            $user->id,
            $transactionType,
            $validatedData['category'] ?? null
        );

        $accountId = $this->resolveAccountId(
            $user->id,
            $validatedData['account'] ?? null
        );

        $receiptPhotoPaths = $this->storeReceiptPhotos($request);

        Transaction::create([
            'user_id' => $user->id,
            'type' => $transactionType,
            'amount' => $normalizedAmount,
            'category_id' => $categoryId,
            'account_id' => $accountId,
            'occurred_at' => $occurredAt,
            'description' => $validatedData['description'] ?? null,
            'receipt_photo_path' => $receiptPhotoPaths[0] ?? null,
            'receipt_photo_paths' => $receiptPhotoPaths,
        ]);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Transaction saved successfully.');
    }

    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        $this->authorizeTransaction($transaction);

        $validatedData = $this->validateTransactionData($request);

        $normalizedAmount = $this->normalizeAmount($validatedData['amount']);

        if ($normalizedAmount === null) {
            return back()
                ->withErrors([
                    'amount' => 'Please enter a valid amount.',
                ])
                ->withInput();
        }

        $transactionType = $validatedData['transaction_type'];
        $occurredAt = $this->buildOccurredAt($validatedData['transaction_date']);

        $categoryId = $this->resolveCategoryId(
            $transaction->user_id,
            $transactionType,
            $validatedData['category'] ?? null
        );

        $accountId = $this->resolveAccountId(
            $transaction->user_id,
            $validatedData['account'] ?? null
        );

        $receiptPhotoPaths = $this->resolveUpdatedReceiptPhotoPaths($request, $transaction);

        $transaction->update([
            'type' => $transactionType,
            'amount' => $normalizedAmount,
            'category_id' => $categoryId,
            'account_id' => $accountId,
            'occurred_at' => $occurredAt,
            'description' => $validatedData['description'] ?? null,
            'receipt_photo_path' => $receiptPhotoPaths[0] ?? null,
            'receipt_photo_paths' => $receiptPhotoPaths,
        ]);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Transaction updated successfully.');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->authorizeTransaction($transaction);

        foreach ($this->getTransactionPhotoPaths($transaction) as $photoPath) {
            Storage::disk('public')->delete($photoPath);
        }

        $transaction->delete();

        return redirect()
            ->route('dashboard')
            ->with('success', 'Transaction deleted successfully.');
    }

    /* Validate transaction data */
    private function validateTransactionData(Request $request): array
    {
        return $request->validate([
            'transaction_type' => ['required', 'in:income,expense,savings'],
            'transaction_date' => ['required', 'date_format:m/d/Y'],
            'amount' => ['required', 'string'],
            'category' => ['required', 'string', 'max:80'],
            'account' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:255'],
            'receipt_photos' => ['nullable', 'array'],
            'receipt_photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'receipt_photo_camera' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'existing_receipt_photo_paths' => ['nullable', 'string'],
        ]);
    }

    /* Normalize amount */
    private function normalizeAmount(string $amount): ?string
    {
        $normalizedAmount = preg_replace('/[^\d.]/', '', $amount);

        if (!$normalizedAmount || !is_numeric($normalizedAmount)) {
            return null;
        }

        return $normalizedAmount;
    }

    /* Build occurred at */
    private function buildOccurredAt(string $transactionDate): Carbon
    {
        $selectedDate = Carbon::createFromFormat('m/d/Y', $transactionDate);
        $currentTime = now();

        return $selectedDate->copy()->setTime(
            $currentTime->hour,
            $currentTime->minute,
            $currentTime->second
        );
    }

    /* Resolve category id */
    private function resolveCategoryId(int $userId, string $transactionType, ?string $categoryName): ?int
    {
        if (!$categoryName || trim($categoryName) === '') {
            return null;
        }

        $category = Category::firstOrCreate([
            'user_id' => $userId,
            'type' => $transactionType,
            'name' => trim($categoryName),
        ]);

        return $category->id;
    }

    /* Resolve account id */
    private function resolveAccountId(int $userId, ?string $accountName): ?int
    {
        if (!$accountName || trim($accountName) === '') {
            return null;
        }

        $account = Account::firstOrCreate([
            'user_id' => $userId,
            'name' => trim($accountName),
        ]);

        return $account->id;
    }

    /* Get transaction photo paths */
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

    /* Decode existing photo paths input */
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

    /* Store receipt photos */
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

    /* Resolve updated receipt photo paths */
    private function resolveUpdatedReceiptPhotoPaths(Request $request, Transaction $transaction): array
    {
        $currentPhotoPaths = $this->getTransactionPhotoPaths($transaction);
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

    /* Authorize transaction */
    private function authorizeTransaction(Transaction $transaction): void
    {
        abort_unless(
            auth()->check() && auth()->id() === $transaction->user_id,
            403
        );
    }

    private function buildTransactionGroups(Collection $transactions): array
    {
        return $transactions
            ->groupBy(fn (Transaction $transaction) => $transaction->occurred_at->format('Y-m-d'))
            ->map(function (Collection $groupedTransactions, string $dateKey) {
                $groupDate = Carbon::createFromFormat('Y-m-d', $dateKey);

                return [
                    'dateKey' => $dateKey,
                    'dateLabel' => $groupDate->format('D, F d'),
                    'income' => (float) $groupedTransactions->where('type', 'income')->sum('amount'),
                    'expense' => (float) $groupedTransactions->where('type', 'expense')->sum('amount'),
                    'savings' => (float) $groupedTransactions->where('type', 'savings')->sum('amount'),
                    'transactions' => $groupedTransactions
                        ->map(function (Transaction $transaction) {
                            $categoryName = $transaction->category?->name
                                ?? Str::headline($transaction->type);

                            $accountName = $transaction->account?->name ?? '';
                            $categoryKey = $this->resolveCategoryKey($categoryName, $transaction->type);

                            return [
                                'id' => $transaction->id,
                                'type' => $transaction->type,
                                'category' => $categoryName,
                                'categoryKey' => $categoryKey,
                                'account' => $accountName,
                                'amount' => (float) $transaction->amount,
                                'time' => $transaction->occurred_at->format('g:ia'),
                                'description' => $transaction->description ?? '',
                                'iconPath' => $this->resolveTransactionIconPath(
                                    $categoryName,
                                    $accountName,
                                    $transaction->type
                                ),
                                'receiptPhotoUrls' => collect($this->getTransactionPhotoPaths($transaction))
                                    ->map(fn (string $photoPath) => Storage::url($photoPath))
                                    ->values()
                                    ->all(),
                            ];
                        })
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }

    private function buildCategoryMetaPayload(Collection $transactions, int $userId): array
    {
        $categoryMeta = collect($this->defaultCategoryCatalog())
            ->keyBy('key');

        $transactionCategories = $transactions
            ->filter(fn (Transaction $transaction) => $transaction->type === 'expense')
            ->map(function (Transaction $transaction) {
                $categoryName = $transaction->category?->name ?? 'Others';
                $categoryKey = $this->resolveCategoryKey($categoryName, 'expense');

                return [
                    'key' => $categoryKey,
                    'name' => $categoryName,
                    'color' => $this->resolveCategoryColor($categoryKey),
                    'iconPath' => $this->resolveTransactionIconPath($categoryName),
                ];
            })
            ->unique('key')
            ->values();

        foreach ($transactionCategories as $category) {
            if ($categoryMeta->has($category['key'])) {
                continue;
            }

            $categoryMeta->put($category['key'], $category);
        }

        $budgetItems = Budget::query()
            ->with('items')
            ->where('user_id', $userId)
            ->get()
            ->flatMap(fn (Budget $budget) => $budget->items ?? collect());

        foreach ($budgetItems as $item) {
            $categoryName = $item->category_name ?: 'Others';
            $categoryKey = $this->resolveCategoryKey($categoryName, 'expense');

            if ($categoryMeta->has($categoryKey)) {
                continue;
            }

            $categoryMeta->put($categoryKey, [
                'key' => $categoryKey,
                'name' => $categoryName,
                'color' => $this->resolveCategoryColor($categoryKey),
                'iconPath' => $this->resolveTransactionIconPath($categoryName),
            ]);
        }

        return $categoryMeta
            ->sortBy('name')
            ->values()
            ->all();
    }

    private function buildBudgetSnapshotsPayload(Collection $transactions, int $userId): array
    {
        $budgetMonthKeys = Budget::query()
            ->where('user_id', $userId)
            ->pluck('period_date')
            ->filter()
            ->map(fn ($periodDate) => Carbon::parse($periodDate)->format('Y-m'));

        $months = $transactions
            ->map(fn (Transaction $transaction) => $transaction->occurred_at->format('Y-m'))
            ->merge($budgetMonthKeys)
            ->push(now()->format('Y-m'))
            ->unique()
            ->values();

        return $months
            ->mapWithKeys(function (string $monthKey) use ($userId) {
                $monthDate = Carbon::createFromFormat('Y-m', $monthKey)->startOfMonth();
                $budget = $this->resolveBudgetForMonth($userId, $monthDate);

                if (!$budget) {
                    return [$monthKey => []];
                }

                $items = $budget->items
                    ? $budget->items->mapWithKeys(function ($item) {
                        $categoryKey = $this->resolveCategoryKey($item->category_name ?: 'Others', 'expense');

                        return [
                            $categoryKey => [
                                'categoryKey' => $categoryKey,
                                'categoryName' => $item->category_name ?: 'Others',
                                'allocatedAmount' => (float) $item->allocated_amount,
                            ],
                        ];
                    })->all()
                    : [];

                return [$monthKey => $items];
            })
            ->all();
    }

    private function buildBudgetGuardPayload(Collection $transactions, int $userId): array
    {
        return [
            'budgetSnapshots' => $this->buildBudgetSnapshotsPayload($transactions, $userId),
            'spentByMonthCategory' => $transactions
                ->filter(fn (Transaction $transaction) => $transaction->type === 'expense')
                ->groupBy(fn (Transaction $transaction) => $transaction->occurred_at->format('Y-m'))
                ->map(function (Collection $monthTransactions) {
                    return $monthTransactions
                        ->groupBy(function (Transaction $transaction) {
                            $categoryName = $transaction->category?->name ?? 'Others';
                            return $this->resolveCategoryKey($categoryName, 'expense');
                        })
                        ->map(fn (Collection $categoryTransactions) => (float) $categoryTransactions->sum('amount'))
                        ->all();
                })
                ->all(),
        ];
    }

    private function resolveBudgetForMonth(int $userId, Carbon $monthDate): ?Budget
    {
        $exactBudget = Budget::query()
            ->with('items')
            ->where('user_id', $userId)
            ->whereDate('period_date', $monthDate->toDateString())
            ->first();

        if ($exactBudget) {
            return $exactBudget;
        }

        return Budget::query()
            ->with('items')
            ->where('user_id', $userId)
            ->where('is_reused', true)
            ->whereDate('period_date', '<=', $monthDate->toDateString())
            ->orderByDesc('period_date')
            ->first();
    }

    private function defaultCategoryCatalog(): array
    {
        return [
            [
                'key' => 'transportation',
                'name' => 'Transportation',
                'color' => '#EB5757',
                'iconPath' => '/projectassets/icons/transport.svg',
            ],
            [
                'key' => 'food',
                'name' => 'Food',
                'color' => '#F2994A',
                'iconPath' => '/projectassets/icons/food&drinks.svg',
            ],
            [
                'key' => 'household',
                'name' => 'Household',
                'color' => '#9B51E0',
                'iconPath' => '/projectassets/icons/homebills.svg',
            ],
            [
                'key' => 'beauty',
                'name' => 'Beauty',
                'color' => '#FF6FAE',
                'iconPath' => '/projectassets/icons/selfcare.svg',
            ],
            [
                'key' => 'health',
                'name' => 'Health',
                'color' => '#E74C3C',
                'iconPath' => '/projectassets/icons/health.svg',
            ],
            [
                'key' => 'others',
                'name' => 'Others',
                'color' => '#F2C94C',
                'iconPath' => '/projectassets/icons/others.svg',
            ],
        ];
    }

    private function resolveCategoryKey(string $categoryName, string $transactionType = 'expense'): string
    {
        if ($transactionType !== 'expense') {
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

        $normalizedName = Str::of($categoryName)
            ->lower()
            ->trim()
            ->replace('&', 'and')
            ->replace('/', ' ')
            ->replace('-', ' ')
            ->squish()
            ->value();

        return match ($normalizedName) {
            'beauty', 'self care', 'selfcare' => 'beauty',
            'transport', 'transportation', 'fare', 'gas', 'car' => 'transportation',
            'food', 'food and drinks', 'food drinks', 'groceries', 'dining' => 'food',
            'household', 'home bills', 'homebills', 'utilities', 'rent' => 'household',
            'health', 'medical', 'medicine' => 'health',
            'shopping', 'apparel', 'gift', 'education', 'school', 'pets', 'pet care', 'others', 'other', '' => 'others',
            default => 'others',
        };
    }

    private function resolveCategoryColor(string $categoryKey): string
    {
        return collect($this->defaultCategoryCatalog())
            ->firstWhere('key', $categoryKey)['color'] ?? '#7d8597';
    }

    private function resolveTransactionIconPath(
        string $categoryName,
        string $accountName = '',
        string $transactionType = 'expense'
    ): string
    {
        $normalizedCategory = Str::of($categoryName)
            ->lower()
            ->trim()
            ->replace('&', 'and')
            ->replace('/', '')
            ->replace('-', '')
            ->replace(' ', '')
            ->value();

        $normalizedAccount = Str::of($accountName)
            ->lower()
            ->trim()
            ->replace('&', 'and')
            ->replace('/', '')
            ->replace('-', '')
            ->replace(' ', '')
            ->value();

        $defaultCategoryIcons = [
            'salary' => '/projectassets/icons/salary.svg',
            'allowance' => '/projectassets/icons/salary.svg',
            'bonus' => '/projectassets/icons/salary.svg',
            'pettycash' => '/projectassets/icons/salary.svg',
            'shopping' => '/projectassets/icons/shopping.svg',
            'apparel' => '/projectassets/icons/shopping.svg',
            'beauty' => '/projectassets/icons/selfcare.svg',
            'gift' => '/projectassets/icons/others.svg',
            'transport' => '/projectassets/icons/transport.svg',
            'transportation' => '/projectassets/icons/transport.svg',
            'food' => '/projectassets/icons/food&drinks.svg',
            'fooddrinks' => '/projectassets/icons/food&drinks.svg',
            'foodanddrinks' => '/projectassets/icons/food&drinks.svg',
            'health' => '/projectassets/icons/health.svg',
            'education' => '/projectassets/icons/others.svg',
            'work' => '/projectassets/icons/work.svg',
            'pets' => '/projectassets/icons/others.svg',
            'household' => '/projectassets/icons/homebills.svg',
            'homebills' => '/projectassets/icons/homebills.svg',
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

        if (array_key_exists($normalizedCategory, $defaultCategoryIcons)) {
            return $defaultCategoryIcons[$normalizedCategory];
        }

        if ($normalizedCategory !== '') {
            return '/projectassets/icons/others.svg';
        }

        $defaultAccountIcons = [
            'cash' => '/projectassets/icons/cash.svg',
            'wallet' => '/projectassets/icons/cash.svg',
            'pettycash' => '/projectassets/icons/cash.svg',
            'bank' => '/projectassets/icons/bank.svg',
            'unionbank' => '/projectassets/icons/bank.svg',
            'bpi' => '/projectassets/icons/bank.svg',
            'bdo' => '/projectassets/icons/bank.svg',
            'metrobank' => '/projectassets/icons/bank.svg',
            'landbank' => '/projectassets/icons/bank.svg',
            'card' => '/projectassets/icons/cards.svg',
            'cards' => '/projectassets/icons/cards.svg',
            'creditcard' => '/projectassets/icons/cards.svg',
            'debitcard' => '/projectassets/icons/cards.svg',
        ];

        if (array_key_exists($normalizedAccount, $defaultAccountIcons)) {
            return $defaultAccountIcons[$normalizedAccount];
        }

        return '/projectassets/icons/others.svg';
    }
}
