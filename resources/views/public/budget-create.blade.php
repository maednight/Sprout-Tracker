@php
$budgetNameValue = old('name', '');
$budgetCycleValue = old('cycle', 'monthly');
$isReusedValue = old('is_reused', '1') === '1';

$budgetCycleLabelMap = [
    'daily' => 'Daily',
    'weekly' => 'Weekly',
    'monthly' => 'Monthly',
    'quarterly' => 'Quarterly',
    'yearly' => 'Yearly',
];

$budgetCycleLabel = $budgetCycleLabelMap[$budgetCycleValue] ?? 'Monthly';
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Budget Create Head -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Budget - Sprout</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inknut+Antiqua:wght@400;600;700&family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="sprout-font">
    <!-- Budget App Shell -->
    <div class="sprout-shell">
        <div class="sprout-phone sprout-budget sprout-budget--form">

            <!-- Budget Main -->
            <main class="sprout-budget__page">
                <div class="sprout-budget__content">

                    @if ($errors->any())
                        <div class="sprout-budget-form__alert sprout-budget-form__alert--error">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Budget Form Header -->
                    <header class="sprout-budget-form__header">
                        <a
                            href="{{ route('budget.index', ['month' => $selectedMonthValue]) }}"
                            class="sprout-budget-form__close"
                            aria-label="Back to budget"
                        >
                            ×
                        </a>

                        <h1 class="sprout-budget-form__title">
                            Set Budget
                        </h1>

                        <div class="sprout-budget-form__spacer" aria-hidden="true"></div>
                    </header>

                    <form method="POST" action="{{ route('budget.store') }}" class="sprout-budget-form__form">
                        @csrf

                        <input type="hidden" name="month" value="{{ $selectedMonthValue }}">
                        <input
                            type="hidden"
                            name="is_reused"
                            value="{{ $isReusedValue ? '1' : '0' }}"
                            data-budget-reused-input
                        >
                        <input
                            type="hidden"
                            name="cycle"
                            value="{{ $budgetCycleValue }}"
                            data-budget-cycle-input
                        >

                        <!-- Budget Form Section -->
                        <section class="sprout-budget-form__section">
                            <div class="sprout-budget-form__field-card">
                                <label class="sprout-budget-form__label" for="budget_name">
                                    Name
                                </label>

                                <input
                                    id="budget_name"
                                    name="name"
                                    type="text"
                                    class="sprout-budget-form__input"
                                    value="{{ $budgetNameValue }}"
                                    placeholder="Enter budget name"
                                    autocomplete="off"
                                >
                            </div>

                            <button
                                type="button"
                                class="sprout-budget-form__field-card sprout-budget-form__field-card--row sprout-budget-form__field-card--button"
                                data-budget-cycle-trigger
                                aria-label="Open budget cycle options"
                            >
                                <div class="sprout-budget-form__field-grow sprout-budget-form__field-grow--left">
                                    <div class="sprout-budget-form__label">
                                        Budget Cycle
                                    </div>

                                    <div
                                        class="sprout-budget-form__input-display"
                                        data-budget-cycle-display
                                    >
                                        {{ $budgetCycleLabel }}
                                    </div>
                                </div>

                                <span class="sprout-budget-form__chevron">›</span>
                            </button>

                            <div class="sprout-budget-form__field-card sprout-budget-form__field-card--toggle">
                                <div class="sprout-budget-form__toggle-copy">
                                    <button
                                        type="button"
                                        class="sprout-budget-form__toggle-label-button"
                                        data-budget-info-trigger
                                    >
                                        <span class="sprout-budget-form__toggle-label">
                                            Reused Budget
                                            <span class="sprout-budget-form__info">?</span>
                                        </span>
                                    </button>
                                </div>

                                <button
                                    type="button"
                                    class="sprout-budget-form__toggle-switch {{ $isReusedValue ? 'sprout-budget-form__toggle-switch--active' : '' }}"
                                    data-budget-reused-toggle
                                    aria-pressed="{{ $isReusedValue ? 'true' : 'false' }}"
                                    aria-label="Toggle reused budget"
                                >
                                    <span class="sprout-budget-form__toggle-thumb"></span>
                                </button>
                            </div>
                        </section>

                        <!-- Budget Form Action -->
                        <div class="sprout-budget-form__actions">
                            <button
                                type="submit"
                                class="sprout-budget-form__next-button"
                            >
                                Next
                            </button>
                        </div>
                    </form>

                </div>
            </main>

        </div>
    </div>

    <!-- Budget Info Modal -->
    <div class="sprout-budget-info-modal sprout-budget-info-modal--hidden" data-budget-info-modal>
        <button
            type="button"
            class="sprout-budget-info-modal__backdrop"
            data-budget-info-close
            aria-label="Close reused budget info"
        ></button>

        <div class="sprout-budget-info-modal__sheet" role="dialog" aria-modal="true" aria-labelledby="budgetInfoTitle">
            <div class="sprout-budget-info-modal__header">
                <h2 id="budgetInfoTitle" class="sprout-budget-info-modal__title">
                    Reused Budget
                </h2>

                <button
                    type="button"
                    class="sprout-budget-info-modal__close"
                    data-budget-info-close
                    aria-label="Close reused budget info"
                >
                    ×
                </button>
            </div>

            <p class="sprout-budget-info-modal__text">
                If enabled, the budget will be reused for each period. If disabled you must manually set the budget for each period.
            </p>
        </div>
    </div>

    <!-- Budget Cycle Modal -->
    <div class="sprout-budget-cycle-modal sprout-budget-cycle-modal--hidden" data-budget-cycle-modal>
        <button
            type="button"
            class="sprout-budget-cycle-modal__backdrop"
            data-budget-cycle-close
            aria-label="Close budget cycle options"
        ></button>

        <div class="sprout-budget-cycle-modal__sheet" role="dialog" aria-modal="true" aria-labelledby="budgetCycleTitle">
            <div class="sprout-budget-cycle-modal__header">
                <button
                    type="button"
                    class="sprout-budget-cycle-modal__header-close"
                    data-budget-cycle-close
                    aria-label="Close budget cycle options"
                >
                    ×
                </button>

                <h2 id="budgetCycleTitle" class="sprout-budget-cycle-modal__title">
                    Budget Cycle
                </h2>

                <div class="sprout-budget-cycle-modal__header-space" aria-hidden="true"></div>
            </div>

            <div class="sprout-budget-cycle-modal__list">
                <button type="button" class="sprout-budget-cycle-modal__list-item {{ $budgetCycleValue === 'daily' ? 'sprout-budget-cycle-modal__list-item--active' : '' }}" data-budget-cycle-option data-value="daily" data-label="Daily">
                    <span>Daily</span>
                    <span class="sprout-budget-cycle-modal__check"></span>
                </button>

                <button type="button" class="sprout-budget-cycle-modal__list-item {{ $budgetCycleValue === 'weekly' ? 'sprout-budget-cycle-modal__list-item--active' : '' }}" data-budget-cycle-option data-value="weekly" data-label="Weekly">
                    <span>Weekly</span>
                    <span class="sprout-budget-cycle-modal__check"></span>
                </button>

                <button type="button" class="sprout-budget-cycle-modal__list-item {{ $budgetCycleValue === 'monthly' ? 'sprout-budget-cycle-modal__list-item--active' : '' }}" data-budget-cycle-option data-value="monthly" data-label="Monthly">
                    <span>Monthly</span>
                    <span class="sprout-budget-cycle-modal__check"></span>
                </button>

                <button type="button" class="sprout-budget-cycle-modal__list-item {{ $budgetCycleValue === 'quarterly' ? 'sprout-budget-cycle-modal__list-item--active' : '' }}" data-budget-cycle-option data-value="quarterly" data-label="Quarterly">
                    <span>Quarterly</span>
                    <span class="sprout-budget-cycle-modal__check"></span>
                </button>

                <button type="button" class="sprout-budget-cycle-modal__list-item {{ $budgetCycleValue === 'yearly' ? 'sprout-budget-cycle-modal__list-item--active' : '' }}" data-budget-cycle-option data-value="yearly" data-label="Yearly">
                    <span>Yearly</span>
                    <span class="sprout-budget-cycle-modal__check"></span>
                </button>
            </div>
        </div>
    </div>

    <script>
        /* Budget Create Interactions */
        document.addEventListener('DOMContentLoaded', function () {
            const reusedToggleButton = document.querySelector('[data-budget-reused-toggle]')
            const reusedInput = document.querySelector('[data-budget-reused-input]')

            const infoTriggerButton = document.querySelector('[data-budget-info-trigger]')
            const infoModal = document.querySelector('[data-budget-info-modal]')
            const infoCloseButtons = document.querySelectorAll('[data-budget-info-close]')

            const cycleTriggerButton = document.querySelector('[data-budget-cycle-trigger]')
            const cycleModal = document.querySelector('[data-budget-cycle-modal]')
            const cycleCloseButtons = document.querySelectorAll('[data-budget-cycle-close]')
            const cycleOptions = document.querySelectorAll('[data-budget-cycle-option]')
            const cycleInput = document.querySelector('[data-budget-cycle-input]')
            const cycleDisplay = document.querySelector('[data-budget-cycle-display]')

            if (reusedToggleButton && reusedInput) {
                reusedToggleButton.addEventListener('click', function () {
                    const isActive = reusedInput.value === '1'
                    const nextValue = isActive ? '0' : '1'

                    reusedInput.value = nextValue
                    reusedToggleButton.setAttribute('aria-pressed', nextValue === '1' ? 'true' : 'false')
                    reusedToggleButton.classList.toggle('sprout-budget-form__toggle-switch--active', nextValue === '1')
                })
            }

            if (infoTriggerButton && infoModal) {
                infoTriggerButton.addEventListener('click', function () {
                    infoModal.classList.remove('sprout-budget-info-modal--hidden')
                })
            }

            infoCloseButtons.forEach(function (closeButton) {
                closeButton.addEventListener('click', function () {
                    infoModal.classList.add('sprout-budget-info-modal--hidden')
                })
            })

            if (cycleTriggerButton && cycleModal) {
                cycleTriggerButton.addEventListener('click', function () {
                    cycleModal.classList.remove('sprout-budget-cycle-modal--hidden')
                })
            }

            cycleCloseButtons.forEach(function (closeButton) {
                closeButton.addEventListener('click', function () {
                    cycleModal.classList.add('sprout-budget-cycle-modal--hidden')
                })
            })

            cycleOptions.forEach(function (cycleOption) {
                cycleOption.addEventListener('click', function () {
                    const nextValue = cycleOption.getAttribute('data-value') || 'monthly'
                    const nextLabel = cycleOption.getAttribute('data-label') || 'Monthly'

                    if (cycleInput) {
                        cycleInput.value = nextValue
                    }

                    if (cycleDisplay) {
                        cycleDisplay.textContent = nextLabel
                    }

                    cycleOptions.forEach(function (item) {
                        item.classList.remove('sprout-budget-cycle-modal__list-item--active')
                    })

                    cycleOption.classList.add('sprout-budget-cycle-modal__list-item--active')
                    cycleModal.classList.add('sprout-budget-cycle-modal--hidden')
                })
            })
        })
    </script>
</body>
</html>