<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\SavingsTransfer;
use App\Models\Transaction;
use App\Services\Support\ReceiptPhotoService;
use App\Services\Support\TransactionPresentationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TransactionService
{
    public function __construct(
        private ReceiptPhotoService $receiptPhotoService,
        private TransactionPresentationService $transactionPresentationService
    ) {}

    public function buildIndexPayload(int $userId): array
    {
        $transactions = Transaction::query()
            ->with(['category', 'account'])
            ->where('user_id', $userId)
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->get();

        return [
            'transactionGroups' => $this->buildTransactionGroups($transactions),
            'initialDisplayDate' => $transactions->isNotEmpty()
                ? $transactions->first()->occurred_at->format('Y-m-d')
                : now()->format('Y-m-d'),
            'categoryMeta' => $this->buildCategoryMetaPayload($transactions, $userId),
            'budgetSnapshots' => $this->buildBudgetSnapshotsPayload($transactions, $userId),
        ];
    }

    public function buildCreatePayload(int $userId): array
    {
        $transactions = Transaction::query()
            ->with(['category', 'account'])
            ->where('user_id', $userId)
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->get();

        return [
            'transaction' => null,
            'budgetGuardPayload' => $this->buildBudgetGuardPayload($transactions, $userId),
        ];
    }

    public function buildEditPayload(Transaction $transaction): array
    {
        $transaction->load(['category', 'account']);

        $transactions = Transaction::query()
            ->with(['category', 'account'])
            ->where('user_id', $transaction->user_id)
            ->where('id', '!=', $transaction->id)
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->get();

        return [
            'transaction' => $transaction,
            'budgetGuardPayload' => $this->buildBudgetGuardPayload($transactions, $transaction->user_id),
        ];
    }

    public function createTransaction(int $userId, array $validatedData, Request $request): Transaction
    {
        $normalizedAmount = $this->normalizeAmount($validatedData['amount']);

        if ($normalizedAmount === null) {
            throw ValidationException::withMessages([
                'amount' => 'Please enter a valid amount.',
            ]);
        }

        $transactionType = $validatedData['transaction_type'];
        $occurredAt = $this->buildOccurredAt($validatedData['transaction_date']);

        $categoryId = $this->resolveCategoryId(
            $userId,
            $transactionType,
            $validatedData['category'] ?? null
        );

        $accountId = $this->resolveAccountId(
            $userId,
            $validatedData['account'] ?? null
        );

        $receiptPhotoPaths = $this->receiptPhotoService->storeReceiptPhotos($request);

        return Transaction::create([
            'user_id' => $userId,
            'type' => $transactionType,
            'amount' => $normalizedAmount,
            'category_id' => $categoryId,
            'account_id' => $accountId,
            'occurred_at' => $occurredAt,
            'description' => $validatedData['description'] ?? null,
            'receipt_photo_path' => $receiptPhotoPaths[0] ?? null,
            'receipt_photo_paths' => $receiptPhotoPaths,
        ]);
    }

    public function updateTransaction(Transaction $transaction, array $validatedData, Request $request): Transaction
    {
        $normalizedAmount = $this->normalizeAmount($validatedData['amount']);

        if ($normalizedAmount === null) {
            throw ValidationException::withMessages([
                'amount' => 'Please enter a valid amount.',
            ]);
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

        $receiptPhotoPaths = $this->receiptPhotoService->resolveUpdatedReceiptPhotoPaths($request, $transaction);

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

        return $transaction;
    }

    public function deleteTransaction(Transaction $transaction): void
    {
        $linkedSavingsTransfer = SavingsTransfer::query()
            ->where('user_id', $transaction->user_id)
            ->where(function ($query) use ($transaction) {
                $query->where('income_transaction_id', $transaction->id)
                    ->orWhere('savings_transaction_id', $transaction->id);
            })
            ->first();

        $this->receiptPhotoService->deleteTransactionPhotos($transaction);
        $transaction->delete();

        if ($linkedSavingsTransfer) {
            $linkedSavingsTransfer->delete();
        }
    }

    private function normalizeAmount(string $amount): ?string
    {
        $normalizedAmount = preg_replace('/[^\d.]/', '', $amount);

        if (! $normalizedAmount || ! is_numeric($normalizedAmount)) {
            return null;
        }

        return $normalizedAmount;
    }

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

    private function resolveCategoryId(int $userId, string $transactionType, ?string $categoryName): ?int
    {
        if (! $categoryName || trim($categoryName) === '') {
            return null;
        }

        $category = Category::firstOrCreate([
            'user_id' => $userId,
            'type' => $transactionType,
            'name' => trim($categoryName),
        ]);

        return $category->id;
    }

    private function resolveAccountId(int $userId, ?string $accountName): ?int
    {
        if (! $accountName || trim($accountName) === '') {
            return null;
        }

        $account = Account::firstOrCreate([
            'user_id' => $userId,
            'name' => trim($accountName),
        ]);

        return $account->id;
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
                            $categoryKey = $this->transactionPresentationService->resolveCategoryKey($categoryName, $transaction->type);

                            return [
                                'id' => $transaction->id,
                                'type' => $transaction->type,
                                'category' => $categoryName,
                                'categoryKey' => $categoryKey,
                                'account' => $accountName,
                                'amount' => (float) $transaction->amount,
                                'time' => $transaction->occurred_at->format('g:ia'),
                                'description' => $transaction->description ?? '',
                                'iconPath' => $this->transactionPresentationService->resolveTransactionIconPath(
                                    $categoryName,
                                    $accountName,
                                    $transaction->type
                                ),
                                'receiptPhotoUrls' => collect($this->receiptPhotoService->getTransactionPhotoPaths($transaction))
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
        $categoryMeta = collect($this->transactionPresentationService->defaultCategoryCatalog())
            ->keyBy('key');

        $transactionCategories = $transactions
            ->filter(fn (Transaction $transaction) => $transaction->type === 'expense')
            ->toBase()
            ->map(function (Transaction $transaction) {
                $categoryName = $transaction->category?->name ?? 'Others';
                $categoryKey = $this->transactionPresentationService->resolveCategoryKey($categoryName, 'expense');

                return [
                    'key' => $categoryKey,
                    'name' => $categoryName,
                    'color' => $this->transactionPresentationService->resolveCategoryColor($categoryKey),
                    'iconPath' => $this->transactionPresentationService->resolveTransactionIconPath($categoryName),
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
            $categoryKey = $this->transactionPresentationService->resolveCategoryKey($categoryName, 'expense');

            if ($categoryMeta->has($categoryKey)) {
                continue;
            }

            $categoryMeta->put($categoryKey, [
                'key' => $categoryKey,
                'name' => $categoryName,
                'color' => $this->transactionPresentationService->resolveCategoryColor($categoryKey),
                'iconPath' => $this->transactionPresentationService->resolveTransactionIconPath($categoryName),
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
            ->toBase()
            ->map(fn (Transaction $transaction) => $transaction->occurred_at->format('Y-m'))
            ->merge($budgetMonthKeys)
            ->push(now()->format('Y-m'))
            ->unique()
            ->values();

        return $months
            ->mapWithKeys(function (string $monthKey) use ($userId) {
                $monthDate = Carbon::createFromFormat('Y-m', $monthKey)->startOfMonth();
                $budget = $this->resolveBudgetForMonth($userId, $monthDate);

                if (! $budget) {
                    return [$monthKey => []];
                }

                $items = $budget->items
                    ? $budget->items->mapWithKeys(function ($item) {
                        $categoryKey = $this->transactionPresentationService->resolveCategoryKey($item->category_name ?: 'Others', 'expense');

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

                            return $this->transactionPresentationService->resolveCategoryKey($categoryName, 'expense');
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
}
