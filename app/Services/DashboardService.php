<?php

namespace App\Services;

use App\Models\Transaction;
use App\Services\Support\ReceiptPhotoService;
use App\Services\Support\TransactionPresentationService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DashboardService
{
    public function __construct(
        private ReceiptPhotoService $receiptPhotoService,
        private TransactionPresentationService $transactionPresentationService
    ) {
    }

    public function buildPayload(int $userId): array
    {
        $transactions = Transaction::query()
            ->with(['category', 'account', 'savingsTransfer.sourceCategory'])
            ->where('user_id', $userId)
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->get();

        return [
            'transactionGroups' => $this->buildTransactionGroups($transactions),
            'initialDisplayDate' => $transactions->isNotEmpty()
                ? $transactions->first()->occurred_at->format('Y-m-d')
                : now()->format('Y-m-d'),
        ];
    }

    private function buildTransactionGroups(Collection $transactions): array
    {
        return $transactions
            ->groupBy(fn (Transaction $transaction) => $transaction->occurred_at->format('Y-m-d'))
            ->map(function (Collection $groupedTransactions, string $dateKey) {
                $groupDate = Carbon::createFromFormat('Y-m-d', $dateKey);

                $incomeTotal = $groupedTransactions
                    ->where('type', 'income')
                    ->sum('amount');

                $expenseTotal = $groupedTransactions
                    ->where('type', 'expense')
                    ->sum('amount');

                $savingsTotal = $groupedTransactions
                    ->where('type', 'savings')
                    ->sum('amount');

                return [
                    'dateKey' => $dateKey,
                    'dateLabel' => $groupDate->format('D, F d'),
                    'income' => (float) $incomeTotal,
                    'expense' => (float) $expenseTotal,
                    'savings' => (float) $savingsTotal,
                    'transactions' => $groupedTransactions
                        ->map(function (Transaction $transaction) {
                            $categoryName = $transaction->category?->name
                                ?? Str::headline($transaction->type);

                            $accountName = $transaction->account?->name ?? '';
                            $isSavingsTransfer = $transaction->type === 'income'
                                && $categoryName === 'Savings Transfer';
                            $sourceSavingsCategoryName = $transaction->savingsTransfer?->sourceCategory?->name ?? '';
                            $transferIndicator = $isSavingsTransfer
                                ? trim(($sourceSavingsCategoryName ?: 'Savings').' savings transfer')
                                : '';

                            return [
                                'id' => $transaction->id,
                                'type' => $transaction->type,
                                'category' => $categoryName,
                                'account' => $accountName,
                                'amount' => (float) $transaction->amount,
                                'time' => $transaction->occurred_at->format('g:ia'),
                                'description' => $transaction->description ?? '',
                                'isSavingsTransfer' => $isSavingsTransfer,
                                'transferIndicator' => $transferIndicator,
                                'iconPath' => $this->transactionPresentationService->resolveTransactionIconPath(
                                    $categoryName,
                                    $accountName
                                ),
                                'iconColor' => $this->transactionPresentationService->resolveTransactionIconColor($transaction->type),
                                'receiptPhotoUrls' => $this->receiptPhotoService->resolveReceiptPhotoUrls($transaction),
                            ];
                        })
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }
}
