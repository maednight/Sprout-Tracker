<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $transactions = Transaction::query()
            ->with(['category', 'account', 'savingsTransfer.sourceCategory'])
            ->where('user_id', $user->id)
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->get();

        $transactionGroups = $this->buildTransactionGroups($transactions);

        $initialDisplayDate = $transactions->isNotEmpty()
            ? $transactions->first()->occurred_at->format('Y-m-d')
            : now()->format('Y-m-d');

        return view('public.dashboard', [
            'dashboardPayload' => [
                'transactionGroups' => $transactionGroups,
                'initialDisplayDate' => $initialDisplayDate,
            ],
        ]);
    }

    /* Build transaction groups */
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
                                'iconPath' => $this->resolveTransactionIconPath(
                                    $categoryName,
                                    $accountName
                                ),
                                'iconColor' => $this->resolveTransactionIconColor($transaction->type),

                                /* Receipt Photos */
                                'receiptPhotoUrls' => $this->resolveReceiptPhotoUrls($transaction),
                            ];
                        })
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }

    /* Resolve receipt photo urls */
    private function resolveReceiptPhotoUrls(Transaction $transaction): array
    {
        $photoPaths = [];

        if (is_array($transaction->receipt_photo_paths) && ! empty($transaction->receipt_photo_paths)) {
            $photoPaths = $transaction->receipt_photo_paths;
        } elseif (! empty($transaction->receipt_photo_path)) {
            $photoPaths = [$transaction->receipt_photo_path];
        }

        return collect($photoPaths)
            ->filter(fn ($photoPath) => filled($photoPath))
            ->map(function ($photoPath) {
                if (
                    Str::startsWith($photoPath, ['http://', 'https://', '/storage/', 'storage/'])
                ) {
                    return Str::startsWith($photoPath, 'storage/')
                        ? asset($photoPath)
                        : $photoPath;
                }

                return Storage::url($photoPath);
            })
            ->values()
            ->all();
    }

    /* Resolve transaction icon path */
    private function resolveTransactionIconPath(string $categoryName, string $accountName = ''): string
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

            'shopping' => '/projectassets/icons/others.svg',
            'apparel' => '/projectassets/icons/others.svg',
            'beauty' => '/projectassets/icons/selfcare.svg',
            'gift' => '/projectassets/icons/others.svg',

            'transport' => '/projectassets/icons/transport.svg',
            'transportation' => '/projectassets/icons/transport.svg',

            'food' => '/projectassets/icons/food&drinks.svg',
            'fooddrinks' => '/projectassets/icons/food&drinks.svg',
            'foodanddrinks' => '/projectassets/icons/food&drinks.svg',

            'health' => '/projectassets/icons/health.svg',
            'education' => '/projectassets/icons/others.svg',
            'work' => '/projectassets/icons/others.svg',
            'pets' => '/projectassets/icons/others.svg',

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

    /* Resolve transaction icon color */
    private function resolveTransactionIconColor(string $transactionType): string
    {
        return match ($transactionType) {
            'income' => 'green',
            'savings' => 'blue',
            default => 'coral',
        };
    }
}
