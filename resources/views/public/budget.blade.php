<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Budget - Sprout</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link
    href="https://fonts.googleapis.com/css2?family=Inknut+Antiqua:wght@400;600;700&family=Inter:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet"
>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="sprout-font">
<div class="sprout-shell">
    <div class="sprout-phone sprout-budget">
        <main class="sprout-budget__page">
            <div class="sprout-budget__content">

                <header class="sprout-budget__header">
                    <a
                        href="{{ route('budget.index', ['month' => $previousMonthValue]) }}"
                        class="sprout-budget__month-arrow"
                        aria-label="Previous month"
                    >
                        ‹
                    </a>

                    <h1 class="sprout-budget__month-title">
                        {{ $displayMonthLabel }}
                    </h1>

                    <a
                        href="{{ route('budget.index', ['month' => $nextMonthValue]) }}"
                        class="sprout-budget__month-arrow"
                        aria-label="Next month"
                    >
                        ›
                    </a>
                </header>

                @if (session('success'))
                    <div class="sprout-budget-form__alert sprout-budget-form__alert--success">
                        {{ session('success') }}
                    </div>
                @endif

                @if (!$budget)
                    <section class="sprout-budget__empty-state">
                        <p class="sprout-budget__empty-text">
                            No Budget was set.
                        </p>

                        <a
                            href="{{ route('budget.create', ['month' => $selectedMonthValue]) }}"
                            class="sprout-budget__empty-link"
                        >
                            Set Now
                        </a>
                    </section>
                @else
                    <section class="sprout-budget-summary">
                        <header class="sprout-budget-summary__topbar">
                            <a
                                href="{{ route('budget.allocate', $budget) }}"
                                class="sprout-budget-summary__back"
                                aria-label="Back to budget allocation"
                            >
                                ‹
                            </a>

                            <h2 class="sprout-budget-summary__title">
                                Budget
                            </h2>

                            <span class="sprout-budget-summary__topbar-space"></span>
                        </header>

                        <div class="sprout-budget-summary__hero">
                            <div class="sprout-budget-summary__chart-wrap">
                                <canvas id="budgetSummaryChart" width="120" height="120"></canvas>
                            </div>

                            <div class="sprout-budget-summary__amount-block">
                                <p class="sprout-budget-summary__cycle">
                                    {{ ucfirst($budget->cycle) }}
                                </p>

                                <p class="sprout-budget-summary__amount">
                                    ₱{{ number_format($totalAllocated, 0) }}
                                </p>

                                <a
                                    href="{{ route('budget.allocate', $budget) }}"
                                    class="sprout-budget-summary__edit"
                                    aria-label="Edit budget allocation"
                                >
                                    <img src="{{ asset('projectassets/icons/edit.svg') }}" alt="Edit budget">
                                </a>
                            </div>
                        </div>

                        <div class="sprout-budget-summary__list">
                            @foreach ($categoryRows as $categoryRow)
                                <div class="sprout-budget-summary__item {{ !$categoryRow['is_active'] ? 'sprout-budget-summary__item--inactive' : '' }}">
                                    <div class="sprout-budget-summary__item-main">
                                        <div
                                            class="sprout-budget-summary__item-icon"
                                            style="--budget-icon-bg: {{ $categoryRow['is_active'] ? $categoryRow['color'] : '#E7E7E7' }};"
                                        >
                                            <img
                                                src="{{ asset('projectassets/icons/' . $categoryRow['icon']) }}"
                                                alt="{{ $categoryRow['name'] }} icon"
                                            >
                                        </div>

                                        <span class="sprout-budget-summary__item-name">
                                            {{ $categoryRow['name'] }}
                                        </span>
                                    </div>

                                    <span class="sprout-budget-summary__item-amount">
                                        ₱{{ number_format($categoryRow['amount'], 0) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

            </div>
        </main>

        @include('public.partials.nav-mobile')
    </div>
</div>

@if ($budget)
<script>
document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('budgetSummaryChart')

    if (!canvas) {
        return
    }

    const rawRows = @json($categoryRows)
    const activeRows = rawRows.filter((row) => Number(row.amount) > 0)

    const chartData = activeRows.length > 0
        ? {
            values: activeRows.map((row) => Number(row.amount)),
            colors: activeRows.map((row) => row.color)
        }
        : {
            values: [1],
            colors: ['#E4E4E4']
        }

    new Chart(canvas, {
        type: 'doughnut',
        data: {
            datasets: [
                {
                    data: chartData.values,
                    backgroundColor: chartData.colors,
                    borderWidth: 0,
                    hoverOffset: 0
                }
            ]
        },
        options: {
            responsive: false,
            cutout: '72%',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    enabled: false
                }
            }
        }
    })
})
</script>
@endif
</body>
</html>