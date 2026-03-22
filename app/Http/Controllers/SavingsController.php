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
            ->with(['category', 'account'])
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
            ->with(['sourceCategory', 'account'])
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
        $user = auth()->user();

        $savingsTransactions = Transaction::query()
            ->with(['category', 'account'])
            ->where('user_id', $user->id)
            ->where('type', 'savings')
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->get();

        $savingsTransfers = SavingsTransfer::query()
            ->with(['sourceCategory', 'account'])
            ->where('user_id', $user->id)
            ->orderByDesc('transferred_at')
            ->orderByDesc('id')
            ->get();

        $transferCategories = $this->buildCategoryBalances($savingsTransactions, $savingsTransfers);
        $accountOptions = Account::query()
            ->where('user_id', $user->id)
            ->orderBy('name')
            ->pluck('name')
            ->values()
            ->all();

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
            'transferCategories' => $transferCategories,
            'accountOptions' => $accountOptions,
            'transferDateValue' => old('transfer_date', $prefilledTransferDateValue),
            'transferAmountValue' => old('amount', ''),
            'transferCategoryValue' => old('source_category_id', ''),
            'transferAccountValue' => old('account', ''),
            'transferDescriptionValue' => old('description', ''),
        ]);
    }

    public function transfer(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'source_category_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transfer_date' => ['required', 'date_format:m/d/Y'],
            'account' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $sourceCategory = Category::query()
            ->where('user_id', $user->id)
            ->where('type', 'savings')
            ->whereKey($validated['source_category_id'])
            ->firstOrFail();

        $availableAmount = $this->resolveAvailableCategoryAmount($user->id, $sourceCategory->id);
        $transferAmount = round((float) $validated['amount'], 2);

        if ($transferAmount > $availableAmount) {
            return back()
                ->withErrors([
                    'transfer_amount' => 'The transfer amount is higher than the available savings in this category.',
                ])
                ->withInput();
        }

        $account = Account::firstOrCreate([
            'user_id' => $user->id,
            'name' => trim($validated['account']),
        ]);

        $incomeCategory = Category::firstOrCreate([
            'user_id' => $user->id,
            'type' => 'income',
            'name' => 'Savings Transfer',
        ]);

        $transferredAt = Carbon::createFromFormat('m/d/Y', $validated['transfer_date'])
            ->setTime(now()->hour, now()->minute, now()->second);

        DB::transaction(function () use (
            $user,
            $sourceCategory,
            $account,
            $incomeCategory,
            $transferAmount,
            $transferredAt,
            $validated
        ) {
            $description = trim((string) ($validated['description'] ?? ''));
            $transferDescription = $description !== ''
                ? $description
                : 'Transferred from savings: ' . $sourceCategory->name;

            $incomeTransaction = Transaction::create([
                'user_id' => $user->id,
                'type' => 'income',
                'amount' => $transferAmount,
                'category_id' => $incomeCategory->id,
                'account_id' => $account->id,
                'occurred_at' => $transferredAt,
                'description' => $transferDescription,
                'receipt_photo_path' => null,
                'receipt_photo_paths' => [],
            ]);

            SavingsTransfer::create([
                'user_id' => $user->id,
                'source_category_id' => $sourceCategory->id,
                'account_id' => $account->id,
                'income_transaction_id' => $incomeTransaction->id,
                'amount' => $transferAmount,
                'transferred_at' => $transferredAt,
                'description' => $transferDescription,
            ]);
        });

        return redirect()
            ->route('savings.index')
            ->with('savings_success', 'Savings transferred to income successfully.');
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

    private function buildHistoryItems(Collection $transactions, Collection $transfers, Collection $categoryBalances): Collection
    {
        $categoryColors = $categoryBalances
            ->mapWithKeys(fn (array $category) => [$category['key'] => $category['color']])
            ->all();

        $depositItems = $transactions->map(function (Transaction $transaction) use ($categoryColors) {
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
            ];
        });

        $transferItems = $transfers->map(function (SavingsTransfer $transfer) use ($categoryColors) {
            $categoryName = $transfer->sourceCategory?->name ?: 'Others';
            $categoryKey = $this->normalizeCategoryKey($categoryName);

            return [
                'id' => 'transfer-' . $transfer->id,
                'timestamp' => $transfer->transferred_at->timestamp,
                'dateLabel' => $transfer->transferred_at->format('D, F d'),
                'time' => $transfer->transferred_at->format('g:ia'),
                'direction' => 'out',
                'kind' => 'transfer',
                'typeLabel' => 'Transfer to Income',
                'category' => $categoryName,
                'account' => $transfer->account?->name ?? '',
                'amount' => (float) $transfer->amount,
                'description' => $transfer->description ?? '',
                'iconPath' => $this->resolveSavingsIcon($categoryName),
                'categoryColor' => $categoryColors[$categoryKey] ?? '#2d9af0',
                'receiptPhotoUrls' => [],
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

    private function resolveAvailableCategoryAmount(int $userId, int $categoryId): float
    {
        $savedAmount = (float) Transaction::query()
            ->where('user_id', $userId)
            ->where('type', 'savings')
            ->where('category_id', $categoryId)
            ->sum('amount');

        $transferredAmount = (float) SavingsTransfer::query()
            ->where('user_id', $userId)
            ->where('source_category_id', $categoryId)
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
