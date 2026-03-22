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

@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="sprout-font">
@php
    $selectedMonthDate = \Carbon\Carbon::createFromFormat('Y-m', $selectedMonthValue)->startOfMonth();
    $pickerYear = (int) $selectedMonthDate->format('Y');
    $pickerMonth = (int) $selectedMonthDate->format('n');
    $pickerMonths = [
        1 => 'Jan',
        2 => 'Feb',
        3 => 'Mar',
        4 => 'Apr',
        5 => 'May',
        6 => 'Jun',
        7 => 'Jul',
        8 => 'Aug',
        9 => 'Sep',
        10 => 'Oct',
        11 => 'Nov',
        12 => 'Dec',
    ];
@endphp
<div class="sprout-shell">
    <div class="sprout-phone sprout-budget">
        <main class="sprout-budget__page">
            <div class="sprout-budget__content">
                <button
                    type="button"
                    class="sprout-budget__picker-overlay sprout-budget__picker-overlay--hidden"
                    data-budget-picker-overlay
                    aria-label="Close month picker"
                ></button>

                <!-- Budget Header -->
                <header class="sprout-budget__header">
                    <a
                        href="{{ route('budget.index', ['month' => $previousMonthValue]) }}"
                        class="sprout-budget__month-arrow"
                        aria-label="Previous month"
                    >
                        &lsaquo;
                    </a>

                    <button
                        type="button"
                        class="sprout-budget__month-title sprout-budget__month-title--trigger"
                        data-budget-picker-trigger
                        aria-haspopup="dialog"
                        aria-expanded="false"
                    >
                        {{ $selectedMonthDate->format('F Y') }}
                    </button>

                    <a
                        href="{{ route('budget.index', ['month' => $nextMonthValue]) }}"
                        class="sprout-budget__month-arrow"
                        aria-label="Next month"
                    >
                        &rsaquo;
                    </a>
                </header>

                <section
                    class="sprout-budget__picker sprout-budget__picker--hidden"
                    data-budget-picker
                    aria-label="Budget month picker"
                    data-year="{{ $pickerYear }}"
                    data-selected-month="{{ $pickerMonth }}"
                >
                    <div class="sprout-budget__picker-year-row">
                        <button
                            type="button"
                            class="sprout-budget__picker-year-arrow"
                            data-budget-picker-year-shift="-1"
                            aria-label="Previous year"
                        >
                            &lsaquo;
                        </button>

                        <strong class="sprout-budget__picker-year-label" data-budget-picker-year-label>
                            {{ $pickerYear }}
                        </strong>

                        <button
                            type="button"
                            class="sprout-budget__picker-year-arrow"
                            data-budget-picker-year-shift="1"
                            aria-label="Next year"
                        >
                            &rsaquo;
                        </button>
                    </div>

                    <div class="sprout-budget__picker-month-grid">
                        @foreach ($pickerMonths as $monthNumber => $monthLabel)
                            @php
                                $monthValue = sprintf('%04d-%02d', $pickerYear, $monthNumber);
                            @endphp
                            <a
                                href="{{ route('budget.index', ['month' => $monthValue]) }}"
                                class="sprout-budget__picker-month {{ $monthNumber === $pickerMonth ? 'sprout-budget__picker-month--active' : '' }}"
                                data-budget-picker-month
                                data-month="{{ $monthNumber }}"
                            >
                                {{ $monthLabel }}
                            </a>
                        @endforeach
                    </div>
                </section>

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
                    @php
                        $activeRows = collect($categoryRows)->filter(fn ($row) => (float) $row['amount'] > 0)->values();
                        $gradientParts = [];
                        $currentStop = 0;

                        if ($activeRows->count() > 0 && $totalAllocated > 0) {
                            foreach ($activeRows as $index => $row) {
                                $slice = ((float) $row['amount'] / (float) $totalAllocated) * 100;
                                $start = $currentStop;
                                $end = $currentStop + $slice;

                                $gradientParts[] = "{$row['color']} {$start}% {$end}%";

                                if ($index < $activeRows->count() - 1) {
                                    $separatorStart = max($end - 0.45, $start);
                                    $separatorEnd = min($end + 0.45, 100);

                                    $gradientParts[] = "#ffffff {$separatorStart}% {$separatorEnd}%";
                                }

                                $currentStop = $end;
                            }

                            $donutGradient = 'conic-gradient(' . implode(', ', $gradientParts) . ')';
                        } else {
                            $donutGradient = 'conic-gradient(#E4E4E4 0% 100%)';
                        }
                    @endphp

                    <section class="sprout-budget-card">
                        <div class="sprout-budget-card__topline">
                            <div class="sprout-budget-card__topline-actions">
                                <button
                                    type="button"
                                    class="sprout-budget-card__schedule-button"
                                    id="budget-schedule-open"
                                    onclick="document.getElementById('budget-schedule-modal')?.classList.remove('sprout-budget-schedule-modal--hidden'); window.openBudgetScheduleModal && window.openBudgetScheduleModal();"
                                >
                                    Budget Sched
                                </button>
                            </div>

                            <div class="sprout-budget-card__topline-right">
                                @if ($isInheritedBudget)
                                    <a
                                        href="{{ route('budget.create', ['month' => $selectedMonthValue, 'source_budget_id' => $budget->id]) }}"
                                        class="sprout-budget-card__override-link sprout-budget-card__override-link--danger"
                                    >
                                        Customize this month
                                    </a>
                                @elseif ($isOverrideBudget)
                                    <form
                                        action="{{ route('budget.override.revert', $budget) }}"
                                        method="POST"
                                        class="sprout-budget-card__revert-form"
                                    >
                                        @csrf
                                        <input type="hidden" name="month" value="{{ $selectedMonthValue }}">

                                        <button type="submit" class="sprout-budget-card__override-link sprout-budget-card__override-link--danger">
                                            Use reused budget
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        @if ($isOverrideBudget || $budget->is_reused || !$budget->is_reused)
                            <div class="sprout-budget-card__status-center">
                                <span class="sprout-budget-card__state-badge sprout-budget-card__state-badge--neutral">
                                    {{ $isOverrideBudget ? 'Override budget' : ($budget->is_reused ? 'Reused budget' : 'One-time budget') }}
                                </span>
                            </div>
                        @endif

                        <div class="sprout-budget-card__summary">
                            <div class="sprout-budget-card__chart-wrap">
                                <div
                                    class="sprout-budget-card__donut"
                                    style="--budget-donut-gradient: {{ $donutGradient }};"
                                    aria-label="Budget chart"
                                ></div>
                            </div>

                            <div class="sprout-budget-card__amount-wrap">
                                <p class="sprout-budget-card__name">
                                    {{ $budget->name }}
                                </p>

                                <p class="sprout-budget-card__cycle">
                                    {{ ucfirst($budget->cycle) }}
                                </p>

                                <div class="sprout-budget-card__amount-row">
                                <p class="sprout-budget-card__total">
                                    ₱{{ number_format($totalAllocated, 0) }}
                                </p>
                                <a
                                    href="{{ route('budget.allocate', ['budget' => $budget, 'month' => $selectedMonthValue]) }}"
                                    class="sprout-budget-card__edit-button sprout-budget-card__edit-button--inline"
                                    aria-label="{{ $isInheritedBudget ? 'Edit reusable budget' : 'Edit budget' }}"
                                >
                                    <img src="{{ asset('projectassets/icons/edit.svg') }}" alt="Edit budget">
                                </a>
                                </div>

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

                    <section class="sprout-budget-category-cards">
                        @foreach ($categoryRows as $categoryRow)
                            @if ($categoryRow['amount'] > 0)
                                <article class="sprout-budget-category-card">
                                    <div class="sprout-budget-category-card__left">
                                        <div
                                            class="sprout-budget-category-card__icon"
                                            style="--budget-category-card-color: {{ $categoryRow['color'] }};"
                                        >
                                            <img
                                                src="{{ asset('projectassets/icons/' . $categoryRow['icon']) }}"
                                                alt="{{ $categoryRow['name'] }} icon"
                                            >
                                        </div>

                                        <span class="sprout-budget-category-card__name">
                                            {{ $categoryRow['name'] }}
                                        </span>
                                    </div>

                                    <span class="sprout-budget-category-card__amount">
                                        ₱{{ number_format($categoryRow['amount'], 0) }}
                                    </span>
                                </article>
                            @endif
                        @endforeach
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
                <div class="sprout-budget-schedule-modal__filter-shell">
                    <button
                        type="button"
                        class="sprout-budget-schedule-modal__filter"
                        id="budget-schedule-filter"
                        aria-haspopup="listbox"
                        aria-expanded="false"
                    >
                        <span class="sprout-budget-schedule-modal__filter-label" id="budget-schedule-filter-label">
                            {{ $scheduleFilters[0]['label'] ?? 'All' }}
                        </span>
                    </button>

                    <span class="sprout-budget-schedule-modal__filter-chevron" aria-hidden="true">
                        &#9662;
                    </span>

                    <div
                        class="sprout-budget-schedule-modal__filter-menu sprout-budget-schedule-modal__filter-menu--hidden"
                        id="budget-schedule-filter-menu"
                        role="listbox"
                    >
                        @foreach ($scheduleFilters as $filter)
                            <button
                                type="button"
                                class="sprout-budget-schedule-modal__filter-option {{ $loop->first ? 'sprout-budget-schedule-modal__filter-option--active' : '' }}"
                                data-budget-filter-option
                                data-filter-value="{{ $filter['value'] }}"
                                data-filter-label="{{ $filter['label'] }}"
                                role="option"
                                aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                            >
                                {{ $filter['label'] }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <span class="sprout-budget-schedule-modal__header-space" aria-hidden="true"></span>
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

<script type="application/json" id="budget-schedule-rows-data">@json($scheduleRows)</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const pickerTrigger = document.querySelector('[data-budget-picker-trigger]')
    const pickerPanel = document.querySelector('[data-budget-picker]')
    const pickerOverlay = document.querySelector('[data-budget-picker-overlay]')
    const pickerYearLabel = document.querySelector('[data-budget-picker-year-label]')
    const pickerYearButtons = document.querySelectorAll('[data-budget-picker-year-shift]')
    const pickerMonthLinks = document.querySelectorAll('[data-budget-picker-month]')
    const selectedYear = {{ $pickerYear }}
    const selectedMonth = {{ $pickerMonth }}
    let pickerOpen = false

    const formatMonthValue = (year, month) => `${year}-${String(month).padStart(2, '0')}`

    const syncPickerMonthLinks = () => {
        if (!pickerPanel || !pickerYearLabel) {
            return
        }

        const activeYear = Number(pickerPanel.getAttribute('data-year') || selectedYear)

        pickerYearLabel.textContent = String(activeYear)

        pickerMonthLinks.forEach((link) => {
            const month = Number(link.getAttribute('data-month') || 1)
            const monthValue = formatMonthValue(activeYear, month)
            const url = new URL(link.href, window.location.origin)

            url.searchParams.set('month', monthValue)
            link.href = url.toString()
            link.classList.toggle(
                'sprout-budget__picker-month--active',
                activeYear === selectedYear && month === selectedMonth
            )
        })
    }

    const closePicker = () => {
        if (!pickerPanel || !pickerOverlay || !pickerTrigger) {
            return
        }

        pickerOpen = false
        pickerPanel.classList.add('sprout-budget__picker--hidden')
        pickerOverlay.classList.add('sprout-budget__picker-overlay--hidden')
        pickerTrigger.setAttribute('aria-expanded', 'false')
    }

    const openPicker = () => {
        if (!pickerPanel || !pickerOverlay || !pickerTrigger) {
            return
        }

        pickerOpen = true
        pickerPanel.classList.remove('sprout-budget__picker--hidden')
        pickerOverlay.classList.remove('sprout-budget__picker-overlay--hidden')
        pickerTrigger.setAttribute('aria-expanded', 'true')
    }

    if (pickerTrigger && pickerPanel && pickerOverlay) {
        syncPickerMonthLinks()

        pickerTrigger.addEventListener('click', function () {
            if (pickerOpen) {
                closePicker()
                return
            }

            openPicker()
        })

        pickerOverlay.addEventListener('click', closePicker)

        pickerYearButtons.forEach((button) => {
            button.addEventListener('click', function () {
                const shift = Number(button.getAttribute('data-budget-picker-year-shift') || 0)
                const currentYear = Number(pickerPanel.getAttribute('data-year') || selectedYear)
                pickerPanel.setAttribute('data-year', String(currentYear + shift))
                syncPickerMonthLinks()
            })
        })

        pickerMonthLinks.forEach((link) => {
            link.addEventListener('click', closePicker)
        })

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closePicker()
            }
        })
    }

    const scheduleModal = document.getElementById('budget-schedule-modal')
    const scheduleOpen = document.getElementById('budget-schedule-open')
    const scheduleClose = document.getElementById('budget-schedule-close')
    const scheduleCloseBackdrop = document.getElementById('budget-schedule-close-backdrop')
    const scheduleFilter = document.getElementById('budget-schedule-filter')
    const scheduleFilterLabel = document.getElementById('budget-schedule-filter-label')
    const scheduleFilterMenu = document.getElementById('budget-schedule-filter-menu')
    const scheduleFilterOptions = document.querySelectorAll('[data-budget-filter-option]')
    const scheduleRowsContainer = document.getElementById('budget-schedule-rows')
    const scheduleRowsData = document.getElementById('budget-schedule-rows-data')
    let activeFilterValue = 'all'

    if (!scheduleModal || !scheduleOpen || !scheduleRowsContainer || !scheduleRowsData) {
        return
    }

    let scheduleRows = {}

    try {
        scheduleRows = JSON.parse(scheduleRowsData.textContent || '{}')
    } catch (error) {
        scheduleRows = {}
    }

    const formatCurrency = (value) => {
        const numericValue = Number(value || 0)

        return `P${new Intl.NumberFormat('en-PH', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(numericValue)}`
    }

    const renderScheduleRows = (filterValue) => {
        const rows = scheduleRows[filterValue] || []

        if (!rows.length) {
            scheduleRowsContainer.innerHTML = `
                <div class="sprout-budget-schedule-modal__table-row">
                    <span class="sprout-budget-schedule-modal__cell sprout-budget-schedule-modal__cell--period">No data</span>
                    <span class="sprout-budget-schedule-modal__cell sprout-budget-schedule-modal__cell--amount">-</span>
                    <span class="sprout-budget-schedule-modal__cell sprout-budget-schedule-modal__cell--amount">-</span>
                    <span class="sprout-budget-schedule-modal__cell sprout-budget-schedule-modal__cell--amount">-</span>
                </div>
            `

            return
        }

        scheduleRowsContainer.innerHTML = rows.map((row) => {
            const remainValue = Number(row.remain)
            const remainClass = remainValue < 0
                ? 'sprout-budget-schedule-modal__remain--negative'
                : (remainValue > 0
                    ? 'sprout-budget-schedule-modal__remain--positive'
                    : 'sprout-budget-schedule-modal__remain--neutral')

            const currentClass = row.is_current
                ? 'sprout-budget-schedule-modal__table-row sprout-budget-schedule-modal__table-row--current'
                : 'sprout-budget-schedule-modal__table-row'

            return `
                <div class="${currentClass}">
                    <span class="sprout-budget-schedule-modal__cell sprout-budget-schedule-modal__cell--period">
                        <span class="sprout-budget-schedule-modal__period-main">${row.period}</span>
                        <span class="sprout-budget-schedule-modal__budget-name">${row.budget_name || 'Budget'}</span>
                    </span>
                    <span class="sprout-budget-schedule-modal__cell sprout-budget-schedule-modal__cell--amount">${formatCurrency(row.plan)}</span>
                    <span class="sprout-budget-schedule-modal__cell sprout-budget-schedule-modal__cell--amount">${formatCurrency(row.spent)}</span>
                    <span class="sprout-budget-schedule-modal__cell sprout-budget-schedule-modal__cell--amount ${remainClass}">${formatCurrency(row.remain)}</span>
                </div>
            `
        }).join('')
    }

    const openScheduleModal = () => {
        scheduleModal.classList.remove('sprout-budget-schedule-modal--hidden')
        renderScheduleRows(activeFilterValue)
    }

    const closeScheduleModal = () => {
        closeFilterMenu()
        scheduleModal.classList.add('sprout-budget-schedule-modal--hidden')
    }

    const openFilterMenu = () => {
        if (!scheduleFilterMenu || !scheduleFilter) {
            return
        }

        scheduleFilterMenu.classList.remove('sprout-budget-schedule-modal__filter-menu--hidden')
        scheduleFilter.setAttribute('aria-expanded', 'true')
        scheduleFilter.classList.add('sprout-budget-schedule-modal__filter--open')
    }

    const closeFilterMenu = () => {
        if (!scheduleFilterMenu || !scheduleFilter) {
            return
        }

        scheduleFilterMenu.classList.add('sprout-budget-schedule-modal__filter-menu--hidden')
        scheduleFilter.setAttribute('aria-expanded', 'false')
        scheduleFilter.classList.remove('sprout-budget-schedule-modal__filter--open')
    }

    const toggleFilterMenu = () => {
        if (!scheduleFilterMenu) {
            return
        }

        if (scheduleFilterMenu.classList.contains('sprout-budget-schedule-modal__filter-menu--hidden')) {
            openFilterMenu()
            return
        }

        closeFilterMenu()
    }

    const setActiveFilter = (filterValue, filterLabel) => {
        activeFilterValue = filterValue

        if (scheduleFilterLabel) {
            scheduleFilterLabel.textContent = filterLabel
        }

        scheduleFilterOptions.forEach((option) => {
            const isActive = option.getAttribute('data-filter-value') === filterValue

            option.classList.toggle('sprout-budget-schedule-modal__filter-option--active', isActive)
            option.setAttribute('aria-selected', isActive ? 'true' : 'false')
        })

        renderScheduleRows(filterValue)
    }

    scheduleOpen.addEventListener('click', openScheduleModal)

    if (scheduleClose) {
        scheduleClose.addEventListener('click', closeScheduleModal)
    }

    if (scheduleCloseBackdrop) {
        scheduleCloseBackdrop.addEventListener('click', closeScheduleModal)
    }

    if (scheduleFilter && scheduleFilterMenu) {
        scheduleFilter.addEventListener('click', function (event) {
            event.preventDefault()
            event.stopPropagation()
            toggleFilterMenu()
        })

        scheduleFilterOptions.forEach((option) => {
            option.addEventListener('click', function (event) {
                event.preventDefault()
                event.stopPropagation()

                const filterValue = option.getAttribute('data-filter-value') || 'all'
                const filterLabel = option.getAttribute('data-filter-label') || 'All'

                setActiveFilter(filterValue, filterLabel)
                closeFilterMenu()
            })
        })

        document.addEventListener('click', function (event) {
            if (!scheduleFilterMenu.contains(event.target) && !scheduleFilter.contains(event.target)) {
                closeFilterMenu()
            }
        })
    }

    setActiveFilter(activeFilterValue, scheduleFilterLabel ? scheduleFilterLabel.textContent.trim() : 'All')
    window.openBudgetScheduleModal = openScheduleModal
})
</script>
@endif
</body>
</html>
