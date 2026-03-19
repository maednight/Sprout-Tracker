<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\BudgetItem;
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

        $budget = Budget::query()
            ->with('items')
            ->where('user_id', $user->id)
            ->whereDate('period_date', $selectedMonthDate->toDateString())
            ->first();

        $categories = $this->getBudgetCategories();
        $categoryRows = $this->buildBudgetRows($budget, $categories);
        $totalAllocated = collect($categoryRows)->sum('amount');

        return view('public.budget', [
            'budget' => $budget,
            'categories' => $categories,
            'categoryRows' => $categoryRows,
            'totalAllocated' => $totalAllocated,
            'displayMonthLabel' => $selectedMonthDate->format('F'),
            'selectedMonthValue' => $selectedMonthDate->format('Y-m'),
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

        return view('public.budget-create', [
            'selectedMonthLabel' => $selectedMonthDate->format('F'),
            'selectedMonthValue' => $selectedMonthDate->format('Y-m'),
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

        return redirect()
            ->route('budget.allocate', $budget)
            ->with('success', 'Budget basic details saved successfully.');
    }

    /* Allocation Page */
    public function allocate(Budget $budget): View
    {
        abort_unless($budget->user_id === auth()->id(), 403);

        $budget->load('items');

        $categories = $this->getBudgetCategories();
        $categoryRows = $this->buildBudgetRows($budget, $categories);
        $totalAllocated = collect($categoryRows)->sum('amount');

        return view('public.budget-allocation', [
            'budget' => $budget,
            'categoryRows' => $categoryRows,
            'totalAllocated' => $totalAllocated,
        ]);
    }

    /* Update Allocation */
    public function updateAllocation(Request $request, Budget $budget): RedirectResponse
    {
        abort_unless($budget->user_id === auth()->id(), 403);

        $categories = $this->getBudgetCategories();
        $categoryKeys = collect($categories)->pluck('key')->all();

        $validatedData = $request->validate([
            'amounts' => ['required', 'array'],
            'amounts.*' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
        ]);

        $amounts = $validatedData['amounts'] ?? [];

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
                'month' => optional($budget->period_date)->format('Y-m'),
            ])
            ->with('success', 'Budget allocation saved successfully.');
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
            'key' => 'savings',
            'name' => 'Savings',
            'icon' => 'savings.svg',
            'color' => '#2D9CDB',
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