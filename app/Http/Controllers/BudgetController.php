<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    /* Budget Page */
    public function index(Request $request): View
    {
        $user = auth()->user();

        $selectedMonthDate = $this->resolveSelectedMonthDate(
            $request->query('month')
        );

        $exactBudget = Budget::query()
            ->with('items')
            ->where('user_id', $user->id)
            ->whereDate('period_date', $selectedMonthDate->toDateString())
            ->first();

        $budget = $this->resolveBudgetForSelectedMonth(
            $user->id,
            $selectedMonthDate
        );
        $isInheritedBudget = !$exactBudget && $budget !== null;
        $reusableBudget = Budget::query()
            ->where('user_id', $user->id)
            ->where('is_reused', true)
            ->whereDate('period_date', '<=', $selectedMonthDate->toDateString())
            ->orderByDesc('period_date')
            ->first();
        $isOverrideBudget = $exactBudget !== null
            && $reusableBudget !== null
            && $exactBudget->id !== $reusableBudget->id;

        $categories = $this->getBudgetCategories();
        $categoryRows = $this->buildBudgetRows($budget, $categories);
        $totalAllocated = collect($categoryRows)->sum('amount');
        $plannedPerDay = $this->calculatePlannedPerDay(
            $totalAllocated,
            $budget?->cycle ?? 'monthly',
            $selectedMonthDate
        );

        $schedulePayload = $budget
            ? $this->buildSchedulePayload($budget, $categories, $selectedMonthDate)
            : [
                'filters' => [],
                'rows' => [],
            ];

        return view('public.budget', [
            'budget' => $budget,
            'categoryRows' => $categoryRows,
            'totalAllocated' => $totalAllocated,
            'plannedPerDay' => $plannedPerDay,
            'scheduleFilters' => $schedulePayload['filters'],
            'scheduleRows' => $schedulePayload['rows'],
            'displayMonthLabel' => $selectedMonthDate->format('F'),
            'selectedMonthValue' => $selectedMonthDate->format('Y-m'),
            'isInheritedBudget' => $isInheritedBudget,
            'isOverrideBudget' => $isOverrideBudget,
            'reusableBudget' => $reusableBudget,
            'previousMonthValue' => $selectedMonthDate->copy()->subMonth()->format('Y-m'),
            'nextMonthValue' => $selectedMonthDate->copy()->addMonth()->format('Y-m'),
        ]);
    }

    /* Set Budget Page */
    public function create(Request $request): View
    {
        $selectedMonthDate = $this->resolveSelectedMonthDate(
            $request->query('month')
        );

        $sourceBudget = null;
        $sourceBudgetId = $request->query('source_budget_id');

        if ($sourceBudgetId) {
            $sourceBudget = Budget::query()
                ->with('items')
                ->where('user_id', auth()->id())
                ->find($sourceBudgetId);
        }

        return view('public.budget-create', [
            'selectedMonthLabel' => $selectedMonthDate->format('F'),
            'selectedMonthValue' => $selectedMonthDate->format('Y-m'),
            'sourceBudget' => $sourceBudget,
            'isOverrideMode' => (bool) $sourceBudget,
            'cycleOptions' => [
                'daily' => 'Daily',
                'weekly' => 'Weekly',
                'monthly' => 'Monthly',
                'quarterly' => 'Quarterly',
                'yearly' => 'Yearly',
            ],
        ]);
    }

    /* Store Budget */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $selectedMonthDate = $this->resolveSelectedMonthDate(
            $request->input('month')
        );

        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'cycle' => ['required', 'in:daily,weekly,monthly,quarterly,yearly'],
            'is_reused' => ['nullable', 'in:0,1'],
            'month' => ['required', 'date_format:Y-m'],
            'source_budget_id' => ['nullable', 'integer'],
        ]);

        $budget = Budget::updateOrCreate(
            [
                'user_id' => $user->id,
                'period_date' => $selectedMonthDate->toDateString(),
            ],
            [
                'name' => trim($validatedData['name']),
                'cycle' => $validatedData['cycle'],
                'is_reused' => ($validatedData['is_reused'] ?? '0') === '1',
            ]
        );

        $sourceBudgetId = $validatedData['source_budget_id'] ?? null;

        if ($sourceBudgetId && !$budget->items()->exists()) {
            $sourceBudget = Budget::query()
                ->with('items')
                ->where('user_id', $user->id)
                ->find($sourceBudgetId);

            if ($sourceBudget) {
                foreach ($sourceBudget->items as $item) {
                    BudgetItem::create([
                        'budget_id' => $budget->id,
                        'category_id' => $item->category_id,
                        'category_name' => $item->category_name,
                        'allocated_amount' => $item->allocated_amount,
                    ]);
                }
            }
        }

        return redirect()
            ->route('budget.allocate', [
                'budget' => $budget,
                'month' => $selectedMonthDate->format('Y-m'),
            ])
            ->with('success', 'Budget basic details saved successfully.');
    }

    /* Allocation Page */
    public function allocate(Budget $budget): View
    {
        abort_unless($budget->user_id === auth()->id(), 403);

        $budget->load('items');
        $selectedMonthDate = $this->resolveSelectedMonthDate(request()->query('month'));
        $isInheritedView = $budget->is_reused
            && optional($budget->period_date)->format('Y-m') !== $selectedMonthDate->format('Y-m');

        $categories = $this->getBudgetCategories();
        $categoryRows = $this->buildBudgetRows($budget, $categories);
        $totalAllocated = collect($categoryRows)->sum('amount');

        return view('public.budget-allocation', [
            'budget' => $budget,
            'categoryRows' => $categoryRows,
            'totalAllocated' => $totalAllocated,
            'selectedMonthValue' => $selectedMonthDate->format('Y-m'),
            'isInheritedView' => $isInheritedView,
            'cycleOptions' => [
                'daily' => 'Daily',
                'weekly' => 'Weekly',
                'monthly' => 'Monthly',
                'quarterly' => 'Quarterly',
                'yearly' => 'Yearly',
            ],
        ]);
    }

    /* Update Allocation */
    public function updateAllocation(Request $request, Budget $budget): RedirectResponse
    {
        abort_unless($budget->user_id === auth()->id(), 403);

        $categories = $this->getBudgetCategories();

        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'cycle' => ['required', 'in:daily,weekly,monthly,quarterly,yearly'],
            'month' => ['nullable', 'date_format:Y-m'],
            'amounts' => ['required', 'array'],
            'amounts.*' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
        ]);

        $amounts = $validatedData['amounts'] ?? [];
        $budget->update([
            'name' => trim($validatedData['name']),
            'cycle' => $validatedData['cycle'],
        ]);

        foreach ($categories as $category) {
            $rawAmount = $amounts[$category['key']] ?? 0;
            $amount = is_numeric($rawAmount) ? (float) $rawAmount : 0;

            BudgetItem::updateOrCreate(
                [
                    'budget_id' => $budget->id,
                    'category_name' => $category['name'],
                ],
                [
                    'category_id' => null,
                    'allocated_amount' => $amount,
                ]
            );
        }

        $budget->items()
            ->whereNotIn('category_name', collect($categories)->pluck('name')->all())
            ->delete();

        return redirect()
            ->route('budget.index', [
                'month' => $validatedData['month'] ?? optional($budget->period_date)->format('Y-m'),
            ])
            ->with('success', 'Budget allocation saved successfully.');
    }

    /* Reset Budget */
    public function destroy(Budget $budget): RedirectResponse
    {
        abort_unless($budget->user_id === auth()->id(), 403);

        $periodMonth = request()->input('month', optional($budget->period_date)->format('Y-m'));

        $budget->delete();

        return redirect()
            ->route('budget.index', [
                'month' => $periodMonth,
            ])
            ->with('success', 'Budget was reset successfully.');
    }

    /* Revert Month Override */
    public function revertOverride(Request $request, Budget $budget): RedirectResponse
    {
        abort_unless($budget->user_id === auth()->id(), 403);

        $validatedData = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
        ]);

        $selectedMonthDate = $this->resolveSelectedMonthDate($validatedData['month']);

        $reusableBudget = Budget::query()
            ->where('user_id', $budget->user_id)
            ->where('is_reused', true)
            ->whereDate('period_date', '<=', $selectedMonthDate->toDateString())
            ->orderByDesc('period_date')
            ->first();

        if (!$reusableBudget || $reusableBudget->id === $budget->id) {
            return redirect()
                ->route('budget.index', [
                    'month' => $validatedData['month'],
                ])
                ->with('success', 'Reusable budget is already active for this month.');
        }

        $budget->delete();

        return redirect()
            ->route('budget.index', [
                'month' => $validatedData['month'],
            ])
            ->with('success', 'Reverted to reusable budget for this month.');
    }

/* Budget Categories */
private function getBudgetCategories(): array
{
    return [
        [
            'key' => 'food',
            'name' => 'Food',
            'icon' => 'food&drinks.svg',
            'color' => '#F2994A',
        ],
        [
            'key' => 'transportation',
            'name' => 'Transportation',
            'icon' => 'transport.svg',
            'color' => '#EB5757',
        ],
        [
            'key' => 'household',
            'name' => 'Household',
            'icon' => 'homebills.svg',
            'color' => '#9B51E0',
        ],
        [
            'key' => 'beauty',
            'name' => 'Beauty',
            'icon' => 'selfcare.svg',
            'color' => '#FF6FAE',
        ],
        [
            'key' => 'health',
            'name' => 'Health',
            'icon' => 'health.svg',
            'color' => '#E74C3C',
        ],
        [
            'key' => 'others',
            'name' => 'Others',
            'icon' => 'others.svg',
            'color' => '#F2C94C',
        ],
    ];
}

    /* Budget Rows */
    private function buildBudgetRows(?Budget $budget, array $categories): array
    {
        $budgetItems = $budget?->items
            ? $budget->items->keyBy(fn ($item) => mb_strtolower(trim($item->category_name)))
            : collect();

        return collect($categories)->map(function ($category) use ($budgetItems) {
            $matchedItem = $budgetItems->get(mb_strtolower($category['name']));
            $amount = $matchedItem ? (float) $matchedItem->allocated_amount : 0;

            return [
                'key' => $category['key'],
                'name' => $category['name'],
                'icon' => $category['icon'],
                'color' => $category['color'],
                'amount' => $amount,
                'is_active' => $amount > 0,
            ];
        })->all();
    }

    /* Planned Per Day */
    private function calculatePlannedPerDay(float $totalAllocated, string $cycle, Carbon $selectedMonthDate): float
    {
        $daysCount = match ($cycle) {
            'daily' => 1,
            'weekly' => 7,
            'monthly' => $selectedMonthDate->daysInMonth,
            'quarterly' => $selectedMonthDate->copy()->startOfQuarter()->diffInDays(
                $selectedMonthDate->copy()->endOfQuarter()
            ) + 1,
            'yearly' => $selectedMonthDate->isLeapYear() ? 366 : 365,
            default => $selectedMonthDate->daysInMonth,
        };

        if ($daysCount <= 0) {
            return 0;
        }

        return round($totalAllocated / $daysCount, 2);
    }

    /* Schedule Payload */
    private function buildSchedulePayload(Budget $budget, array $categories, Carbon $selectedMonthDate): array
    {
        $filters = [
            [
                'value' => 'all',
                'label' => 'All',
            ],
        ];

        foreach ($categories as $category) {
            $filters[] = [
                'value' => $category['key'],
                'label' => $category['name'],
            ];
        }

        $rows = [
            'all' => $this->buildScheduleRowsForFilter(
                $budget,
                $categories,
                $selectedMonthDate,
                null
            ),
        ];

        foreach ($categories as $category) {
            $rows[$category['key']] = $this->buildScheduleRowsForFilter(
                $budget,
                $categories,
                $selectedMonthDate,
                $category
            );
        }

        return [
            'filters' => $filters,
            'rows' => $rows,
        ];
    }

    /* Schedule Rows */
    private function buildScheduleRowsForFilter(
        Budget $budget,
        array $categories,
        Carbon $selectedMonthDate,
        ?array $selectedCategory
    ): array {
        $periods = $this->generateSchedulePeriods(
            $budget,
            $selectedMonthDate
        );

        return collect($periods)->map(function ($period) use ($budget, $categories, $selectedCategory) {
            $rowBudget = $budget->is_reused
                ? $this->resolveBudgetForSelectedMonth($budget->user_id, $period['anchor'])
                : $budget;

            $planAmount = $this->calculatePlanAmountForPeriod(
                $rowBudget,
                $categories,
                $selectedCategory
            );

            $spentAmount = $this->calculateSpentAmountForPeriod(
                $rowBudget ?? $budget,
                $period['start'],
                $period['end'],
                $selectedCategory
            );

            return [
                'period' => $period['label'],
                'budget_name' => $rowBudget?->name ?? $budget->name,
                'plan' => round($planAmount, 2),
                'spent' => round($spentAmount, 2),
                'remain' => round($planAmount - $spentAmount, 2),
                'is_current' => $period['is_current'],
            ];
        })->all();
    }

    /* Generate Schedule Periods */
    private function generateSchedulePeriods(Budget $budget, Carbon $selectedMonthDate): array
    {
        $baseDate = $budget->is_reused
            ? $selectedMonthDate->copy()
            : ($budget->period_date
                ? Carbon::parse($budget->period_date)
                : $selectedMonthDate->copy());

        if (!$budget->is_reused) {
            return [[
                'label' => $this->formatPeriodLabel(
                    $baseDate,
                    $budget->cycle
                ),
                'anchor' => $baseDate->copy()->startOfMonth(),
                'start' => $this->resolvePeriodStart($baseDate, $budget->cycle),
                'end' => $this->resolvePeriodEnd($baseDate, $budget->cycle),
                'is_current' => true,
            ]];
        }

        $rows = [];

        for ($offset = -6; $offset <= 5; $offset++) {
            $periodDate = $this->shiftPeriodDate(
                $baseDate->copy(),
                $budget->cycle,
                $offset
            );

            $rows[] = [
                'label' => $this->formatPeriodLabel($periodDate, $budget->cycle),
                'anchor' => $periodDate->copy()->startOfMonth(),
                'start' => $this->resolvePeriodStart($periodDate, $budget->cycle),
                'end' => $this->resolvePeriodEnd($periodDate, $budget->cycle),
                'is_current' => $offset === 0,
            ];
        }

        return $rows;
    }

    /* Resolve Budget For Month */
    private function resolveBudgetForSelectedMonth(int $userId, Carbon $selectedMonthDate): ?Budget
    {
        $exactBudget = Budget::query()
            ->with('items')
            ->where('user_id', $userId)
            ->whereDate('period_date', $selectedMonthDate->toDateString())
            ->first();

        if ($exactBudget) {
            return $exactBudget;
        }

        return Budget::query()
            ->with('items')
            ->where('user_id', $userId)
            ->where('is_reused', true)
            ->whereDate('period_date', '<=', $selectedMonthDate->toDateString())
            ->orderByDesc('period_date')
            ->first();
    }

    /* Calculate Plan Amount */
    private function calculatePlanAmountForPeriod(
        ?Budget $budget,
        array $categories,
        ?array $selectedCategory
    ): float {
        if (!$budget) {
            return 0;
        }

        $budgetItems = $budget->items
            ? $budget->items->keyBy(fn ($item) => mb_strtolower(trim($item->category_name)))
            : collect();

        if ($selectedCategory) {
            $matchedItem = $budgetItems->get(mb_strtolower($selectedCategory['name']));

            return $matchedItem ? (float) $matchedItem->allocated_amount : 0;
        }

        return collect($categories)->sum(function ($category) use ($budgetItems) {
            $matchedItem = $budgetItems->get(mb_strtolower($category['name']));

            return $matchedItem ? (float) $matchedItem->allocated_amount : 0;
        });
    }

    /* Calculate Spent Amount */
    private function calculateSpentAmountForPeriod(
        Budget $budget,
        Carbon $startDate,
        Carbon $endDate,
        ?array $selectedCategory
    ): float {
        $query = Transaction::query()
            ->where('user_id', $budget->user_id)
            ->where('type', 'expense')
            ->whereBetween('occurred_at', [
                $startDate->copy()->startOfDay(),
                $endDate->copy()->endOfDay(),
            ])
            ->with('category');

        $transactions = $query->get();

        if ($selectedCategory) {
            $selectedKey = $selectedCategory['key'] ?? mb_strtolower($selectedCategory['name']);

            $transactions = $transactions->filter(function ($transaction) use ($selectedKey) {
                $budgetCategoryKey = $this->resolveBudgetCategoryKey(
                    $transaction->category?->name
                );

                return $budgetCategoryKey === $selectedKey;
            });
        } else {
            $transactions = $transactions->filter(function ($transaction) {
                return $this->resolveBudgetCategoryKey(
                    $transaction->category?->name
                ) !== null;
            });
        }

        return (float) $transactions->sum('amount');
    }

    /* Resolve Budget Category Key */
    private function resolveBudgetCategoryKey(?string $categoryName): ?string
    {
        $normalizedName = mb_strtolower(trim((string) $categoryName));

        if ($normalizedName === '') {
            return null;
        }

        return match ($normalizedName) {
            'food' => 'food',
            'transportation' => 'transportation',
            'household' => 'household',
            'beauty' => 'beauty',
            'health' => 'health',
            'others',
            'pets',
            'culture',
            'apparel',
            'education',
            'work',
            'gift' => 'others',
            default => 'others',
        };
    }

    /* Shift Period Date */
    private function shiftPeriodDate(Carbon $date, string $cycle, int $offset): Carbon
    {
        return match ($cycle) {
            'daily' => $date->addDays($offset),
            'weekly' => $date->addWeeks($offset),
            'monthly' => $date->addMonths($offset),
            'quarterly' => $date->addQuarters($offset),
            'yearly' => $date->addYears($offset),
            default => $date->addMonths($offset),
        };
    }

    /* Period Start */
    private function resolvePeriodStart(Carbon $date, string $cycle): Carbon
    {
        return match ($cycle) {
            'daily' => $date->copy()->startOfDay(),
            'weekly' => $date->copy()->startOfWeek(),
            'monthly' => $date->copy()->startOfMonth(),
            'quarterly' => $date->copy()->startOfQuarter(),
            'yearly' => $date->copy()->startOfYear(),
            default => $date->copy()->startOfMonth(),
        };
    }

    /* Period End */
    private function resolvePeriodEnd(Carbon $date, string $cycle): Carbon
    {
        return match ($cycle) {
            'daily' => $date->copy()->endOfDay(),
            'weekly' => $date->copy()->endOfWeek(),
            'monthly' => $date->copy()->endOfMonth(),
            'quarterly' => $date->copy()->endOfQuarter(),
            'yearly' => $date->copy()->endOfYear(),
            default => $date->copy()->endOfMonth(),
        };
    }

    /* Period Label */
    private function formatPeriodLabel(Carbon $date, string $cycle): string
    {
        return match ($cycle) {
            'daily' => $date->format('Y M d'),
            'weekly' => $date->copy()->startOfWeek()->format('Y M d') . ' - ' . $date->copy()->endOfWeek()->format('d'),
            'monthly' => $date->format('Y M'),
            'quarterly' => $date->format('Y') . ' Q' . $date->quarter,
            'yearly' => $date->format('Y'),
            default => $date->format('Y M'),
        };
    }

    /* Resolve Month */
    private function resolveSelectedMonthDate(?string $monthValue): Carbon
    {
        if (!$monthValue) {
            return now()->startOfMonth();
        }

        try {
            return Carbon::createFromFormat('Y-m', $monthValue)->startOfMonth();
        } catch (\Throwable $throwable) {
            return now()->startOfMonth();
        }
    }
}
