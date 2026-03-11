<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\BudgetItem;

class BudgetController extends Controller
{
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

        return view('public.budget', [
            'budget' => $budget,
            'displayMonthLabel' => $selectedMonthDate->format('F'),
            'selectedMonthValue' => $selectedMonthDate->format('Y-m'),
            'previousMonthValue' => $selectedMonthDate->copy()->subMonth()->format('Y-m'),
            'nextMonthValue' => $selectedMonthDate->copy()->addMonth()->format('Y-m'),
        ]);
    }

    public function create(Request $request): View
    {
        $selectedMonthDate = $this->resolveSelectedMonthDate(
            $request->query('month')
        );

        return view('public.budget-create', [
            'selectedMonthLabel' => $selectedMonthDate->format('F'),
            'selectedMonthValue' => $selectedMonthDate->format('Y-m'),
        ]);
    }

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

    /* Resolve selected month date */
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

/* Budget Allocation Page */
public function allocate(Budget $budget): View
{
    $user = auth()->user();

    /* Important Default Categories */
    $defaultCategories = [
        ['name' => 'Transport', 'color' => '#2D9CDB'],
        ['name' => 'Shopping', 'color' => '#F56C5B'],
        ['name' => 'Home Bills', 'color' => '#BDBDBD'],
        ['name' => 'Food', 'color' => '#F2994A'],
        ['name' => 'Others', 'color' => '#D9D9D9'],
    ];

    $items = collect($defaultCategories)->map(function ($category) {
        return [
            'name' => $category['name'],
            'color' => $category['color'],
            'amount' => 0,
        ];
    });

    return view('public.budget-allocation', [
        'budget' => $budget,
        'items' => $items,
    ]);
}