@php
    $selectedMonthDate = \Carbon\Carbon::createFromFormat('Y-m', $selectedMonthValue)->startOfMonth();
    $activeBudgetView = request()->query('view') === 'remain' ? 'remain' : 'budget';
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
                <button
                    type="button"
                    class="sprout-budget__menu-overlay sprout-budget__menu-overlay--hidden"
                    data-budget-menu-overlay
                    aria-label="Close budget filters"
                ></button>

                <!-- Budget Header -->
                <header class="sprout-budget__header">
                    <a
                        href="{{ route('budget.index', array_filter(['month' => $previousMonthValue, 'view' => $activeBudgetView !== 'budget' ? $activeBudgetView : null])) }}"
                        class="sprout-budget__month-arrow"
                        data-budget-month-link
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
                        href="{{ route('budget.index', array_filter(['month' => $nextMonthValue, 'view' => $activeBudgetView !== 'budget' ? $activeBudgetView : null])) }}"
                        class="sprout-budget__month-arrow"
                        data-budget-month-link
                        aria-label="Next month"
                    >
                        &rsaquo;
                    </a>

                    @if ($budget)
                        @php
                            $remainCategoryFilters = collect($remainRows ?? [])
                                ->map(fn (array $remainRow) => [
                                    'value' => $remainRow['key'],
                                    'label' => $remainRow['name'],
                                ])
                                ->values();
                        @endphp
                        <div class="sprout-budget__header-tools {{ $activeBudgetView === 'remain' ? '' : 'sprout-budget__header-tools--hidden' }}" data-budget-remain-toolbar>
                            <div class="sprout-budget-remain-toolbar sprout-budget-remain-toolbar--header">
                                <div class="sprout-budget-remain-toolbar__group">
                                    <button
                                        type="button"
                                        class="sprout-budget-remain-toolbar__text-trigger"
                                        data-budget-remain-category-trigger
                                        aria-haspopup="listbox"
                                        aria-expanded="false"
                                    >
                                        <span data-budget-remain-category-label>All</span>
                                        <span class="sprout-budget-remain-toolbar__chevron" aria-hidden="true">&#9662;</span>
                                    </button>

                                    <div
                                        class="sprout-budget-remain-toolbar__menu sprout-budget-remain-toolbar__menu--hidden"
                                        data-budget-remain-category-menu
                                        role="listbox"
                                    >
                                        <button
                                            type="button"
                                            class="sprout-budget-remain-toolbar__option sprout-budget-remain-toolbar__option--active"
                                            data-budget-remain-category-option
                                            data-category-value="all"
                                            data-category-label="All"
                                        >
                                            All
                                        </button>
                                        @foreach ($remainCategoryFilters as $remainCategoryFilter)
                                            <button
                                                type="button"
                                                class="sprout-budget-remain-toolbar__option"
                                                data-budget-remain-category-option
                                                data-category-value="{{ $remainCategoryFilter['value'] }}"
                                                data-category-label="{{ $remainCategoryFilter['label'] }}"
                                            >
                                                {{ $remainCategoryFilter['label'] }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="sprout-budget-remain-toolbar__group">
                                    <button
                                        type="button"
                                        class="sprout-budget-remain-toolbar__icon-trigger"
                                        data-budget-remain-sort-trigger
                                        aria-haspopup="listbox"
                                        aria-expanded="false"
                                        aria-label="Sort remain categories"
                                    >
                                        <img src="/projectassets/icons/filtericon.svg" alt="">
                                    </button>

                                    <div
                                        class="sprout-budget-remain-toolbar__menu sprout-budget-remain-toolbar__menu--hidden sprout-budget-remain-toolbar__menu--right"
                                        data-budget-remain-sort-menu
                                        role="listbox"
                                    >
                                        <button
                                            type="button"
                                            class="sprout-budget-remain-toolbar__option sprout-budget-remain-toolbar__option--active"
                                            data-budget-remain-sort-option
                                            data-sort-value="highest"
                                            data-sort-label="Spent: highest to lowest"
                                        >
                                            <img src="/projectassets/icons/filtericon.svg" alt="">
                                            <span>Spent: highest to lowest</span>
                                        </button>
                                        <button
                                            type="button"
                                            class="sprout-budget-remain-toolbar__option"
                                            data-budget-remain-sort-option
                                            data-sort-value="lowest"
                                            data-sort-label="Spent: lowest to highest"
                                        >
                                            <img src="/projectassets/icons/filtericon.svg" alt="">
                                            <span>Spent: lowest to highest</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
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
                                href="{{ route('budget.index', array_filter(['month' => $monthValue, 'view' => $activeBudgetView !== 'budget' ? $activeBudgetView : null])) }}"
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

                    <div class="sprout-budget__views" data-budget-views>
                    <div class="sprout-budget__view" data-budget-view-panel="budget">
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
                                    &#8369;{{ number_format($totalAllocated, 0) }}
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
                                    ~&#8369;{{ number_format($plannedPerDay, 2) }} per day
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
                                            &#8369;{{ number_format($categoryRow['amount'], 0) }}
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
                                        &#8369;{{ number_format($categoryRow['amount'], 0) }}
                                    </span>
                                </article>
                            @endif
                        @endforeach
                    </section>
                    </div>

                    <div class="sprout-budget__view sprout-budget__view--hidden" data-budget-view-panel="remain">
                        <section
                            class="sprout-budget-remain-card"
                            data-budget-remain-card
                            style="--budget-remain-accent: #43da84; --budget-remain-accent-soft: #d8f5e5;"
                        >
                            @php
                                $remainSpentPercentage = min((float) ($remainOverview['spentPercentage'] ?? 0), 100);
                                $remainGaugeAngle = 180 - (($remainSpentPercentage / 100) * 180);
                                $remainGaugeRadians = deg2rad($remainGaugeAngle);
                                $remainGaugeCenterX = 100;
                                $remainGaugeCenterY = 100;
                                $remainGaugeRadius = 76;
                                $remainBadgeX = $remainGaugeCenterX + (cos($remainGaugeRadians) * $remainGaugeRadius);
                                $remainBadgeY = $remainGaugeCenterY - (sin($remainGaugeRadians) * $remainGaugeRadius);
                                $remainBadgeLeft = ($remainBadgeX / 200) * 100;
                                $remainBadgeTop = ($remainBadgeY / 120) * 100;
                                $remainSpentPerDay = $selectedMonthDate->daysInMonth > 0
                                    ? (float) ($remainOverview['spent'] ?? 0) / $selectedMonthDate->daysInMonth
                                    : 0;
                            @endphp
                            <div class="sprout-budget-remain-card__header">
                                <span
                                    class="sprout-budget-remain-card__exceed-pill {{ ($remainOverview['remaining'] ?? 0) < 0 ? '' : 'sprout-budget-remain-card__exceed-pill--hidden' }}"
                                    data-budget-remain-exceed-pill
                                >
                                    Exceed &#8369;{{ number_format(abs(min((float) ($remainOverview['remaining'] ?? 0), 0)), 0) }}
                                </span>
                            </div>

                            <div class="sprout-budget-remain-card__gauge">
                                <div
                                    class="sprout-budget-remain-card__gauge-badge"
                                    data-budget-remain-badge
                                    style="left: {{ $remainBadgeLeft }}%; top: {{ $remainBadgeTop }}%;"
                                >
                                    {{ number_format($remainOverview['spentPercentage'] ?? 0, 0) }}%
                                </div>

                                <svg
                                    viewBox="0 0 200 120"
                                    class="sprout-budget-remain-card__gauge-svg"
                                    aria-label="Budget remain gauge"
                                >
                                    <path
                                        d="M 20 100 A 80 80 0 0 1 180 100"
                                        class="sprout-budget-remain-card__gauge-track"
                                        pathLength="100"
                                    />
                                    <path
                                        d="M 20 100 A 80 80 0 0 1 180 100"
                                        class="sprout-budget-remain-card__gauge-progress {{ ($remainOverview['remaining'] ?? 0) < 0 ? 'sprout-budget-remain-card__gauge-progress--danger' : '' }} {{ $remainSpentPercentage <= 0 ? 'sprout-budget-remain-card__gauge-progress--empty' : '' }}"
                                        data-budget-remain-progress
                                        pathLength="100"
                                        style="stroke-dasharray: {{ $remainSpentPercentage }} 100;"
                                    />
                                </svg>

                                <div class="sprout-budget-remain-card__hero">
                                    <div class="sprout-budget-remain-card__hero-kicker">Remaining Amount</div>
                                    <div
                                        class="sprout-budget-remain-card__hero-value"
                                        data-budget-remain-hero-value
                                    >
                                        &#8369;{{ number_format(max((float) ($remainOverview['remaining'] ?? 0), 0), 0) }}
                                    </div>

                                    <div class="sprout-budget-remain-card__hero-label" data-budget-remain-hero-label>
                                        ~&#8369;{{ number_format($remainSpentPerDay, 2) }} spent per day
                                    </div>
                                </div>
                            </div>

                            <div class="sprout-budget-remain-card__stats">
                                <div class="sprout-budget-remain-card__stat">
                                    <span class="sprout-budget-remain-card__stat-value" data-budget-remain-spent-value>&#8369;{{ number_format((float) ($remainOverview['spent'] ?? 0), 0) }}</span>
                                    <span class="sprout-budget-remain-card__stat-label">Spent</span>
                                </div>

                                <div class="sprout-budget-remain-card__stat sprout-budget-remain-card__stat--right">
                                    <span class="sprout-budget-remain-card__stat-value" data-budget-remain-monthly-value>&#8369;{{ number_format((float) ($remainOverview['monthly'] ?? 0), 0) }}</span>
                                    <span class="sprout-budget-remain-card__stat-label">Monthly</span>
                                </div>
                            </div>
                        </section>

                        <section class="sprout-budget-remain-list">
                            @foreach ($remainRows as $remainRow)
                                <article
                                    class="sprout-budget-remain-item"
                                    data-budget-remain-row
                                    data-category-key="{{ $remainRow['key'] }}"
                                    data-category-name="{{ $remainRow['name'] }}"
                                    data-row-order="{{ $loop->index }}"
                                    data-color="{{ $remainRow['color'] }}"
                                    data-amount="{{ (float) $remainRow['amount'] }}"
                                    data-progress-percentage="{{ (float) $remainRow['progressPercentage'] }}"
                                    data-spent="{{ (float) $remainRow['spent'] }}"
                                    data-remaining="{{ (float) $remainRow['remaining'] }}"
                                >
                                    <div class="sprout-budget-remain-item__head">
                                        <div class="sprout-budget-remain-item__head-left">
                                            <div
                                                class="sprout-budget-remain-item__icon"
                                                style="--budget-remain-color: {{ $remainRow['color'] }};"
                                            >
                                                <img
                                                    src="{{ asset('projectassets/icons/' . $remainRow['icon']) }}"
                                                    alt="{{ $remainRow['name'] }} icon"
                                                >
                                            </div>
                                            <div class="sprout-budget-remain-item__copy">
                                                <div class="sprout-budget-remain-item__name">{{ $remainRow['name'] }}</div>
                                                <div class="sprout-budget-remain-item__monthly">Monthly: &#8369;{{ number_format((float) $remainRow['amount'], 0) }}</div>
                                            </div>
                                        </div>

                                        @if ($remainRow['isOverspent'])
                                            <span class="sprout-budget-remain-item__exceed-pill">
                                                Exceed &#8369;{{ number_format(abs((float) $remainRow['remaining']), 0) }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="sprout-budget-remain-item__stats">
                                        <div class="sprout-budget-remain-item__stat">
                                            <span class="sprout-budget-remain-item__stat-label">Spent:</span>
                                            <span class="sprout-budget-remain-item__stat-value">&#8369;{{ number_format((float) $remainRow['spent'], 0) }}</span>
                                        </div>

                                        <div class="sprout-budget-remain-item__stat sprout-budget-remain-item__stat--right">
                                            <span class="sprout-budget-remain-item__stat-label">Remain:</span>
                                            <span class="sprout-budget-remain-item__stat-value {{ $remainRow['isOverspent'] ? 'sprout-budget-remain-item__stat-value--negative' : '' }}">
                                                &#8369;{{ number_format(max((float) $remainRow['remaining'], 0), 0) }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="sprout-budget-remain-item__progress-track">
                                        <div
                                            class="sprout-budget-remain-item__progress-fill {{ $remainRow['isOverspent'] ? 'sprout-budget-remain-item__progress-fill--danger' : '' }}"
                                            style="--budget-remain-color: {{ $remainRow['color'] }}; width: {{ min((float) $remainRow['progressPercentage'], 100) }}%;"
                                        ></div>
                                    </div>
                                </article>
                            @endforeach
                        </section>
                    </div>
                    </div>
                @endif

            </div>
        </main>

        @if ($budget)
            <div class="sprout-budget__mode-toggle" data-budget-view-toggle-shell>
                <button
                    type="button"
                    class="sprout-budget__mode-toggle-button sprout-budget__mode-toggle-button--active"
                    data-budget-view-toggle="budget"
                >
                    Budget
                </button>

                <button
                    type="button"
                    class="sprout-budget__mode-toggle-button"
                    data-budget-view-toggle="remain"
                >
                    Remain
                </button>
            </div>
        @endif

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
                &times;
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
    const viewToggleButtons = document.querySelectorAll('[data-budget-view-toggle]')
    const viewPanels = document.querySelectorAll('[data-budget-view-panel]')
    const viewToggleShell = document.querySelector('[data-budget-view-toggle-shell]')
    const remainHeaderToolbar = document.querySelector('[data-budget-remain-toolbar]')
    const menuOverlay = document.querySelector('[data-budget-menu-overlay]')
    const monthNavigationLinks = document.querySelectorAll('[data-budget-month-link], [data-budget-picker-month]')
    const currentPageUrl = new URL(window.location.href)
    let currentBudgetView = currentPageUrl.searchParams.get('view') === 'remain' ? 'remain' : 'budget'

    const syncBudgetNavigationLinks = (viewName) => {
        monthNavigationLinks.forEach((link) => {
            const url = new URL(link.href, window.location.origin)

            if (viewName === 'remain') {
                url.searchParams.set('view', 'remain')
            } else {
                url.searchParams.delete('view')
            }

            link.href = url.toString()
        })
    }

    const setBudgetView = (viewName) => {
        currentBudgetView = viewName

        viewPanels.forEach((panel) => {
            panel.classList.toggle(
                'sprout-budget__view--hidden',
                panel.getAttribute('data-budget-view-panel') !== viewName
            )
        })

        viewToggleButtons.forEach((button) => {
            const isActive = button.getAttribute('data-budget-view-toggle') === viewName
            button.classList.toggle('sprout-budget__mode-toggle-button--active', isActive)
            button.setAttribute('aria-pressed', isActive ? 'true' : 'false')
        })

        if (remainHeaderToolbar) {
            remainHeaderToolbar.classList.toggle('sprout-budget__header-tools--hidden', viewName !== 'remain')
        }

        const nextUrl = new URL(window.location.href)

        if (viewName === 'remain') {
            nextUrl.searchParams.set('view', 'remain')
        } else {
            nextUrl.searchParams.delete('view')
        }

        window.history.replaceState({}, '', nextUrl)
        syncBudgetNavigationLinks(viewName)
    }

    viewToggleButtons.forEach((button) => {
        button.addEventListener('click', function () {
            setBudgetView(button.getAttribute('data-budget-view-toggle') || 'budget')
        })
    })

    if (viewToggleButtons.length > 0) {
        setBudgetView(currentBudgetView)
    }

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

            if (currentBudgetView === 'remain') {
                url.searchParams.set('view', 'remain')
            } else {
                url.searchParams.delete('view')
            }

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

    const remainCategoryTrigger = document.querySelector('[data-budget-remain-category-trigger]')
    const remainCategoryLabel = document.querySelector('[data-budget-remain-category-label]')
    const remainCategoryMenu = document.querySelector('[data-budget-remain-category-menu]')
    const remainCategoryOptions = document.querySelectorAll('[data-budget-remain-category-option]')
    const remainSortTrigger = document.querySelector('[data-budget-remain-sort-trigger]')
    const remainSortMenu = document.querySelector('[data-budget-remain-sort-menu]')
    const remainSortOptions = document.querySelectorAll('[data-budget-remain-sort-option]')
    const remainList = document.querySelector('.sprout-budget-remain-list')
    const remainRows = remainList ? Array.from(remainList.querySelectorAll('[data-budget-remain-row]')) : []
    const remainCard = document.querySelector('[data-budget-remain-card]')
    const remainTitle = document.querySelector('[data-budget-remain-title]')
    const remainBadge = document.querySelector('[data-budget-remain-badge]')
    const remainProgress = document.querySelector('[data-budget-remain-progress]')
    const remainExceedPill = document.querySelector('[data-budget-remain-exceed-pill]')
    const remainHeroValue = document.querySelector('[data-budget-remain-hero-value]')
    const remainHeroLabel = document.querySelector('[data-budget-remain-hero-label]')
    const remainSpentValue = document.querySelector('[data-budget-remain-spent-value]')
    const remainMonthlyValue = document.querySelector('[data-budget-remain-monthly-value]')
    let activeRemainCategory = 'all'
    let activeRemainSort = 'highest'
    const remainDaysInMonth = {{ $selectedMonthDate->daysInMonth }}
    const remainDefaultSummary = {
        title: 'Budget Remaining',
        color: '#43da84',
        softColor: '#d8f5e5',
        spent: Number({{ json_encode((float) ($remainOverview['spent'] ?? 0)) }}),
        monthly: Number({{ json_encode((float) ($remainOverview['monthly'] ?? 0)) }}),
        remaining: Number({{ json_encode((float) ($remainOverview['remaining'] ?? 0)) }}),
        percentage: Number({{ json_encode((float) ($remainOverview['spentPercentage'] ?? 0)) }})
    }

    const formatRemainCurrency = (value, absolute = false) => {
        const numericValue = Number(value || 0)
        const displayValue = absolute ? Math.abs(numericValue) : numericValue

        return `\u20B1${new Intl.NumberFormat('en-PH', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(displayValue)}`
    }

    const formatRemainDailyCurrency = (value) => {
        return `~\u20B1${new Intl.NumberFormat('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(Number(value || 0))} spent per day`
    }

    const softenRemainColor = (hexColor) => {
        const normalizedColor = String(hexColor || '').replace('#', '')

        if (normalizedColor.length !== 6) {
            return '#d8f5e5'
        }

        const red = parseInt(normalizedColor.slice(0, 2), 16)
        const green = parseInt(normalizedColor.slice(2, 4), 16)
        const blue = parseInt(normalizedColor.slice(4, 6), 16)

        const mixChannel = (channel) => Math.round((channel * 0.22) + (255 * 0.78))

        return `rgb(${mixChannel(red)}, ${mixChannel(green)}, ${mixChannel(blue)})`
    }

    const updateRemainCardBadgePosition = (percentageValue) => {
        if (!remainBadge) {
            return
        }

        const clampedPercentage = Math.max(0, Math.min(Number(percentageValue || 0), 100))
        const angleInRadians = ((180 - ((clampedPercentage / 100) * 180)) * Math.PI) / 180
        const badgeX = 100 + (Math.cos(angleInRadians) * 76)
        const badgeY = 100 - (Math.sin(angleInRadians) * 76)

        remainBadge.style.left = `${(badgeX / 200) * 100}%`
        remainBadge.style.top = `${(badgeY / 120) * 100}%`
    }

    const updateRemainCardSummary = () => {
        if (!remainCard || !remainProgress) {
            return
        }

        let summary = remainDefaultSummary

        if (activeRemainCategory !== 'all') {
            const activeRow = remainRows.find((row) => row.getAttribute('data-category-key') === activeRemainCategory)

            if (activeRow) {
                const rowColor = activeRow.getAttribute('data-color') || remainDefaultSummary.color
                const spent = Number(activeRow.getAttribute('data-spent') || 0)
                const monthly = Number(activeRow.getAttribute('data-amount') || 0)
                const remaining = Number(activeRow.getAttribute('data-remaining') || 0)
                const percentage = Number(activeRow.getAttribute('data-progress-percentage') || 0)

                summary = {
                    title: `${activeRow.getAttribute('data-category-name') || 'Budget'} Remaining`,
                    color: remaining < 0 ? '#ff8b80' : rowColor,
                    softColor: remaining < 0 ? '#ffe0dc' : softenRemainColor(rowColor),
                    spent,
                    monthly,
                    remaining,
                    percentage
                }
            }
        }

        remainCard.style.setProperty('--budget-remain-accent', summary.color)
        remainCard.style.setProperty('--budget-remain-accent-soft', summary.softColor)

        if (remainTitle) {
            remainTitle.textContent = summary.title
        }

        if (remainBadge) {
            remainBadge.textContent = `${Math.round(summary.percentage)}%`
            remainBadge.classList.toggle('sprout-budget-remain-card__gauge-badge--danger', summary.remaining < 0)
            updateRemainCardBadgePosition(summary.percentage)
        }

        remainProgress.style.strokeDasharray = `${Math.max(0, Math.min(summary.percentage, 100))} 100`
        remainProgress.classList.toggle('sprout-budget-remain-card__gauge-progress--danger', summary.remaining < 0)
        remainProgress.classList.toggle('sprout-budget-remain-card__gauge-progress--empty', Number(summary.percentage) <= 0)

        if (remainHeroValue) {
            remainHeroValue.textContent = formatRemainCurrency(Math.max(summary.remaining, 0))
        }

        if (remainHeroLabel) {
            remainHeroLabel.textContent = formatRemainDailyCurrency(remainDaysInMonth > 0 ? summary.spent / remainDaysInMonth : 0)
        }

        if (remainExceedPill) {
            remainExceedPill.textContent = `Exceed ${formatRemainCurrency(Math.abs(Math.min(summary.remaining, 0)))}`
            remainExceedPill.classList.toggle('sprout-budget-remain-card__exceed-pill--hidden', summary.remaining >= 0)
        }

        if (remainSpentValue) {
            remainSpentValue.textContent = formatRemainCurrency(summary.spent)
        }

        if (remainMonthlyValue) {
            remainMonthlyValue.textContent = formatRemainCurrency(summary.monthly)
        }
    }

    const closeRemainMenus = () => {
        if (remainCategoryMenu && remainCategoryTrigger) {
            remainCategoryMenu.classList.add('sprout-budget-remain-toolbar__menu--hidden')
            remainCategoryTrigger.setAttribute('aria-expanded', 'false')
            remainCategoryTrigger.classList.remove('sprout-budget-remain-toolbar__text-trigger--open')
        }

        if (remainSortMenu && remainSortTrigger) {
            remainSortMenu.classList.add('sprout-budget-remain-toolbar__menu--hidden')
            remainSortTrigger.setAttribute('aria-expanded', 'false')
            remainSortTrigger.classList.remove('sprout-budget-remain-toolbar__icon-trigger--open')
        }

        if (menuOverlay) {
            menuOverlay.classList.add('sprout-budget__menu-overlay--hidden')
        }
    }

    const toggleRemainMenu = (menu, trigger) => {
        if (!menu || !trigger) {
            return
        }

        const shouldOpen = menu.classList.contains('sprout-budget-remain-toolbar__menu--hidden')
        closeRemainMenus()

        if (!shouldOpen) {
            return
        }

        menu.classList.remove('sprout-budget-remain-toolbar__menu--hidden')
        trigger.setAttribute('aria-expanded', 'true')

        if (menuOverlay) {
            menuOverlay.classList.remove('sprout-budget__menu-overlay--hidden')
        }

        if (trigger === remainCategoryTrigger) {
            trigger.classList.add('sprout-budget-remain-toolbar__text-trigger--open')
        }

        if (trigger === remainSortTrigger) {
            trigger.classList.add('sprout-budget-remain-toolbar__icon-trigger--open')
        }
    }

    const applyRemainListState = () => {
        if (!remainList || !remainRows.length) {
            return
        }

        const filteredRows = remainRows.filter((row) => {
            if (activeRemainCategory === 'all') {
                return true
            }

            return row.getAttribute('data-category-key') === activeRemainCategory
        })

        remainRows.forEach((row) => {
            row.classList.add('sprout-budget-remain-item--hidden')
        })

        const sortedRows = filteredRows.sort((leftRow, rightRow) => {
            const leftOrder = Number(leftRow.getAttribute('data-row-order') || 0)
            const rightOrder = Number(rightRow.getAttribute('data-row-order') || 0)
            const leftSpent = Number(leftRow.getAttribute('data-spent') || 0)
            const rightSpent = Number(rightRow.getAttribute('data-spent') || 0)

            switch (activeRemainSort) {
                case 'highest':
                    return rightSpent - leftSpent
                case 'lowest':
                    return leftSpent - rightSpent
                default:
                    return leftOrder - rightOrder
            }
        })

        sortedRows.forEach((row) => {
            row.classList.remove('sprout-budget-remain-item--hidden')
            remainList.appendChild(row)
        })

        updateRemainCardSummary()
    }

    if (remainCategoryTrigger && remainCategoryMenu) {
        remainCategoryTrigger.addEventListener('click', function (event) {
            event.preventDefault()
            event.stopPropagation()
            toggleRemainMenu(remainCategoryMenu, remainCategoryTrigger)
        })

        remainCategoryOptions.forEach((option) => {
            option.addEventListener('click', function (event) {
                event.preventDefault()
                event.stopPropagation()

                activeRemainCategory = option.getAttribute('data-category-value') || 'all'

                if (remainCategoryLabel) {
                    remainCategoryLabel.textContent = option.getAttribute('data-category-label') || 'All'
                }

                remainCategoryOptions.forEach((categoryOption) => {
                    const isActive = categoryOption === option
                    categoryOption.classList.toggle('sprout-budget-remain-toolbar__option--active', isActive)
                    categoryOption.setAttribute('aria-selected', isActive ? 'true' : 'false')
                })

                applyRemainListState()
                closeRemainMenus()
            })
        })
    }

    if (remainSortTrigger && remainSortMenu) {
        remainSortTrigger.addEventListener('click', function (event) {
            event.preventDefault()
            event.stopPropagation()
            toggleRemainMenu(remainSortMenu, remainSortTrigger)
        })

        remainSortOptions.forEach((option) => {
            option.addEventListener('click', function (event) {
                event.preventDefault()
                event.stopPropagation()

                activeRemainSort = option.getAttribute('data-sort-value') || 'highest'

                remainSortOptions.forEach((sortOption) => {
                    const isActive = sortOption === option
                    sortOption.classList.toggle('sprout-budget-remain-toolbar__option--active', isActive)
                    sortOption.setAttribute('aria-selected', isActive ? 'true' : 'false')
                })

                applyRemainListState()
                closeRemainMenus()
            })
        })
    }

    document.addEventListener('click', function (event) {
        const clickedInsideRemainToolbar = event.target.closest('.sprout-budget-remain-toolbar__group')

        if (!clickedInsideRemainToolbar) {
            closeRemainMenus()
        }
    })

    if (menuOverlay) {
        menuOverlay.addEventListener('click', closeRemainMenus)
    }

    applyRemainListState()

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

        return `\u20B1${new Intl.NumberFormat('en-PH', {
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
        if (viewToggleShell) {
            viewToggleShell.classList.add('sprout-budget__mode-toggle--hidden')
        }
        renderScheduleRows(activeFilterValue)
    }

    const closeScheduleModal = () => {
        closeFilterMenu()
        scheduleModal.classList.add('sprout-budget-schedule-modal--hidden')
        if (viewToggleShell) {
            viewToggleShell.classList.remove('sprout-budget__mode-toggle--hidden')
        }
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


