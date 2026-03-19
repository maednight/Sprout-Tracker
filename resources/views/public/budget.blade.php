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

                <!-- Budget Header -->
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
                    <section class="sprout-budget-card">
                        <div class="sprout-budget-card__topline">
                            <button
                                type="button"
                                class="sprout-budget-card__schedule-button"
                                id="budget-schedule-open"
                            >
                                Budget Sched
                            </button>

                            <a
                                href="{{ route('budget.allocate', $budget) }}"
                                class="sprout-budget-card__edit-button"
                                aria-label="Edit budget"
                            >
                                <img src="{{ asset('projectassets/icons/edit.svg') }}" alt="Edit budget">
                            </a>
                        </div>

                        <div class="sprout-budget-card__summary">
                            <div class="sprout-budget-card__chart-wrap">
                                <canvas id="budgetSummaryChart" width="132" height="132"></canvas>
                            </div>

                            <div class="sprout-budget-card__amount-wrap">
                                <p class="sprout-budget-card__cycle">
                                    {{ ucfirst($budget->cycle) }}
                                </p>

                                <p class="sprout-budget-card__total">
                                    ₱{{ number_format($totalAllocated, 0) }}
                                </p>

                                <p class="sprout-budget-card__per-day">
                                    ~₱{{ number_format($plannedPerDay, 2) }} per day
                                </p>
                            </div>
                        </div>

                        <div class="sprout-budget-card__list">
                            @foreach ($categoryRows as $categoryRow)
                                @if ($categoryRow['amount'] > 0)
                                    @php
                                        $percentage = $totalAllocated > 0
                                            ? ($categoryRow['amount'] / $totalAllocated) * 100
                                            : 0;
                                    @endphp

                                    <div class="sprout-budget-card__row">
                                        <div class="sprout-budget-card__row-left">
                                            <div
                                                class="sprout-budget-card__row-icon"
                                                style="--budget-row-color: {{ $categoryRow['color'] }};"
                                            >
                                                <img
                                                    src="{{ asset('projectassets/icons/' . $categoryRow['icon']) }}"
                                                    alt="{{ $categoryRow['name'] }} icon"
                                                >
                                            </div>

                                            <div class="sprout-budget-card__row-copy">
                                                <span class="sprout-budget-card__row-name">
                                                    {{ $categoryRow['name'] }}
                                                </span>

                                                <span class="sprout-budget-card__row-percent">
                                                    {{ number_format($percentage, 2) }}%
                                                </span>
                                            </div>
                                        </div>

                                        <span class="sprout-budget-card__row-amount">
                                            ₱{{ number_format($categoryRow['amount'], 0) }}
                                        </span>
                                    </div>
                                @endif
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
<div class="sprout-budget-schedule-modal sprout-budget-schedule-modal--hidden" id="budget-schedule-modal">
    <button
        type="button"
        class="sprout-budget-schedule-modal__backdrop"
        id="budget-schedule-close-backdrop"
    ></button>

    <div class="sprout-budget-schedule-modal__sheet">
        <div class="sprout-budget-schedule-modal__header">
            <button
                type="button"
                class="sprout-budget-schedule-modal__close"
                id="budget-schedule-close"
                aria-label="Close budget schedule"
            >
                ×
            </button>

            <div class="sprout-budget-schedule-modal__filter-wrap">
                <select
                    class="sprout-budget-schedule-modal__filter"
                    id="budget-schedule-filter"
                >
                    @foreach ($scheduleFilters as $filter)
                        <option value="{{ $filter['value'] }}">
                            {{ $filter['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="sprout-budget-schedule-modal__table-head">
            <span>Period</span>
            <span>Plan</span>
            <span>Spent</span>
            <span>Remain</span>
        </div>

        <div class="sprout-budget-schedule-modal__rows" id="budget-schedule-rows"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const summaryCanvas = document.getElementById('budgetSummaryChart')
    const scheduleModal = document.getElementById('budget-schedule-modal')
    const scheduleOpen = document.getElementById('budget-schedule-open')
    const scheduleClose = document.getElementById('budget-schedule-close')
    const scheduleCloseBackdrop = document.getElementById('budget-schedule-close-backdrop')
    const scheduleFilter = document.getElementById('budget-schedule-filter')
    const scheduleRowsContainer = document.getElementById('budget-schedule-rows')

    const rawRows = @json($categoryRows)
    const scheduleRows = @json($scheduleRows)
    let summaryChart = null

    const formatCurrency = (value) => {
        const numericValue = Number(value || 0)

        return `₱${new Intl.NumberFormat('en-PH', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(numericValue)}`
    }

    const rebuildSummaryChart = () => {
        if (!summaryCanvas || typeof Chart === 'undefined') {
            return
        }

        const activeRows = rawRows.filter((row) => Number(row.amount) > 0)
        const hasData = activeRows.length > 0

        const datasetValues = hasData ? activeRows.map((row) => Number(row.amount)) : [1]
        const datasetColors = hasData ? activeRows.map((row) => row.color) : ['#E4E4E4']

        if (summaryChart) {
            summaryChart.destroy()
        }

        summaryChart = new Chart(summaryCanvas, {
            type: 'doughnut',
            data: {
                datasets: [
                    {
                        data: datasetValues,
                        backgroundColor: datasetColors,
                        borderColor: hasData ? '#ffffff' : 'transparent',
                        borderWidth: hasData ? 3 : 0,
                        hoverOffset: 0
                    }
                ]
            },
            options: {
                responsive: false,
                cutout: '82%',
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
    }

    const renderScheduleRows = (filterValue) => {
        const rows = scheduleRows[filterValue] || []

        if (!scheduleRowsContainer) {
            return
        }

        scheduleRowsContainer.innerHTML = rows.map((row) => {
            return `
                <div class="sprout-budget-schedule-modal__table-row ${row.is_current ? 'sprout-budget-schedule-modal__table-row--current' : ''}">
                    <span>${row.period}</span>
                    <span>${formatCurrency(row.plan)}</span>
                    <span>${formatCurrency(row.spent)}</span>
                    <span class="${Number(row.remain) < 0 ? 'sprout-budget-schedule-modal__remain--negative' : 'sprout-budget-schedule-modal__remain--positive'}">
                        ${formatCurrency(row.remain)}
                    </span>
                </div>
            `
        }).join('')
    }

    const openScheduleModal = () => {
        scheduleModal?.classList.remove('sprout-budget-schedule-modal--hidden')
        renderScheduleRows(scheduleFilter?.value || 'all')
    }

    const closeScheduleModal = () => {
        scheduleModal?.classList.add('sprout-budget-schedule-modal--hidden')
    }

    scheduleOpen?.addEventListener('click', openScheduleModal)
    scheduleClose?.addEventListener('click', closeScheduleModal)
    scheduleCloseBackdrop?.addEventListener('click', closeScheduleModal)

    scheduleFilter?.addEventListener('change', (event) => {
        renderScheduleRows(event.target.value)
    })

    rebuildSummaryChart()
})
</script>
@endif
</body>
</html>