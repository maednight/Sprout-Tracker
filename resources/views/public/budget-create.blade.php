<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Set Budget - Sprout</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link
    href="https://fonts.googleapis.com/css2?family=Inknut+Antiqua:wght@400;600;700&family=Inter:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet"
>

@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="sprout-font">
<div class="sprout-shell">
    <div class="sprout-phone sprout-budget sprout-budget--form">
        <main class="sprout-budget__page">
            <div class="sprout-budget__content">

                <header class="sprout-budget-form__header">
                    <a
                        href="{{ route('budget_index', ['month' => $selectedMonthValue]) }}"
                        class="sprout-budget-form__close"
                        aria-label="Close set budget"
                    >
                        ×
                    </a>

                    <h1 class="sprout-budget-form__title">Set Budget</h1>

                    <span class="sprout-budget-form__spacer"></span>
                </header>

                @if ($errors->any())
                    <div class="sprout-budget-form__alert sprout-budget-form__alert--error">
                        Please check the form fields and try again.
                    </div>
                @endif

                <form
                    action="{{ route('budget_store') }}"
                    method="POST"
                    class="sprout-budget-form__form"
                >
                    @csrf

                    <input type="hidden" name="month" value="{{ $selectedMonthValue }}">
                    <input type="hidden" name="source_budget_id" value="{{ $sourceBudget?->id }}">
                    <input type="hidden" name="cycle" id="budget-cycle-input" value="{{ old('cycle', $sourceBudget?->cycle ?? 'monthly') }}">
                    <input type="hidden" name="is_reused" id="budget-reused-input" value="{{ old('is_reused', $sourceBudget ? ($sourceBudget->is_reused ? '1' : '0') : '1') }}">

                    @if ($isOverrideMode)
                        <div class="sprout-budget-form__alert sprout-budget-form__alert--success">
                            Creating a custom budget for {{ $selectedMonthLabel }} using your reusable budget as a starting point.
                        </div>
                    @endif

                    <section class="sprout-budget-form__section">
                        <div class="sprout-budget-form__field-card">
                            <label for="budget-name" class="sprout-budget-form__label">Name</label>

                            <input
                                id="budget-name"
                                type="text"
                                name="name"
                                class="sprout-budget-form__input"
                                value="{{ old('name', $sourceBudget?->name) }}"
                                placeholder="Enter budget name"
                                maxlength="80"
                                autocomplete="off"
                            >
                        </div>

                        <button
                            type="button"
                            class="sprout-budget-form__field-card sprout-budget-form__field-card--button sprout-budget-form__field-card--row"
                            id="budget-cycle-trigger"
                            aria-label="Open budget cycle options"
                        >
                            <div class="sprout-budget-form__field-grow sprout-budget-form__field-grow--left">
                                <span class="sprout-budget-form__label">Budget Cycle</span>
                                <span class="sprout-budget-form__input-display" id="budget-cycle-display">
                                    {{ $cycleOptions[old('cycle', $sourceBudget?->cycle ?? 'monthly')] }}
                                </span>
                            </div>

                            <span class="sprout-budget-form__chevron">›</span>
                        </button>

                        <div class="sprout-budget-form__field-card sprout-budget-form__field-card--toggle">
                            <div class="sprout-budget-form__toggle-copy">
                                <button
                                    type="button"
                                    class="sprout-budget-form__toggle-label-button"
                                    id="budget-info-open"
                                >
                                    <span class="sprout-budget-form__toggle-label">
                                        Reused Budget
                                        <span class="sprout-budget-form__info">?</span>
                                    </span>
                                </button>
                            </div>

                            <button
                                type="button"
                                class="sprout-budget-form__toggle-switch {{ old('is_reused', $sourceBudget ? ($sourceBudget->is_reused ? '1' : '0') : '1') === '1' ? 'sprout-budget-form__toggle-switch--active' : '' }}"
                                id="budget-reused-toggle"
                                aria-label="Toggle reused budget"
                                aria-pressed="{{ old('is_reused', $sourceBudget ? ($sourceBudget->is_reused ? '1' : '0') : '1') === '1' ? 'true' : 'false' }}"
                            >
                                <span class="sprout-budget-form__toggle-thumb"></span>
                            </button>
                        </div>
                    </section>

                    <div class="sprout-budget-form__actions">
                        <button type="submit" class="sprout-budget-form__next-button">
                            Next
                        </button>
                    </div>
                </form>

            </div>
        </main>
    </div>
</div>

<div class="sprout-budget-info-modal sprout-budget-info-modal--hidden" id="budget-info-modal">
    <button type="button" class="sprout-budget-info-modal__backdrop" id="budget-info-close-backdrop"></button>

    <div class="sprout-budget-info-modal__sheet">
        <div class="sprout-budget-info-modal__header">
            <h2 class="sprout-budget-info-modal__title">Reused Budget</h2>

            <button type="button" class="sprout-budget-info-modal__close" id="budget-info-close-button">
                ×
            </button>
        </div>

        <p class="sprout-budget-info-modal__text">
            When enabled, your budget setup can be reused again for the next cycle. If disabled, you will need to set a new budget manually for the next period.
        </p>
    </div>
</div>

<div class="sprout-budget-cycle-modal sprout-budget-cycle-modal--hidden" id="budget-cycle-modal">
    <button type="button" class="sprout-budget-cycle-modal__backdrop" id="budget-cycle-close-backdrop"></button>

    <div class="sprout-budget-cycle-modal__sheet">
        <div class="sprout-budget-cycle-modal__header">
            <button
                type="button"
                class="sprout-budget-cycle-modal__header-close"
                id="budget-cycle-close-button"
            >
                ×
            </button>

            <h2 class="sprout-budget-cycle-modal__title">Budget Cycle</h2>

            <span class="sprout-budget-cycle-modal__header-space"></span>
        </div>

        <div class="sprout-budget-cycle-modal__list">
            @foreach ($cycleOptions as $cycleValue => $cycleLabel)
                <button
                    type="button"
                    class="sprout-budget-cycle-modal__list-item {{ old('cycle', $sourceBudget?->cycle ?? 'monthly') === $cycleValue ? 'sprout-budget-cycle-modal__list-item--active' : '' }}"
                    data-cycle-option
                    data-cycle-value="{{ $cycleValue }}"
                    data-cycle-label="{{ $cycleLabel }}"
                >
                    {{ $cycleLabel }}
                </button>
            @endforeach
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const cycleModal = document.getElementById('budget-cycle-modal')
    const cycleTrigger = document.getElementById('budget-cycle-trigger')
    const cycleDisplay = document.getElementById('budget-cycle-display')
    const cycleInput = document.getElementById('budget-cycle-input')
    const cycleOptions = document.querySelectorAll('[data-cycle-option]')
    const cycleCloseButton = document.getElementById('budget-cycle-close-button')
    const cycleCloseBackdrop = document.getElementById('budget-cycle-close-backdrop')

    const reusedInput = document.getElementById('budget-reused-input')
    const reusedToggle = document.getElementById('budget-reused-toggle')

    const infoModal = document.getElementById('budget-info-modal')
    const infoOpen = document.getElementById('budget-info-open')
    const infoCloseButton = document.getElementById('budget-info-close-button')
    const infoCloseBackdrop = document.getElementById('budget-info-close-backdrop')

    const openCycleModal = () => {
        cycleModal.classList.remove('sprout-budget-cycle-modal--hidden')
    }

    const closeCycleModal = () => {
        cycleModal.classList.add('sprout-budget-cycle-modal--hidden')
    }

    const openInfoModal = () => {
        infoModal.classList.remove('sprout-budget-info-modal--hidden')
    }

    const closeInfoModal = () => {
        infoModal.classList.add('sprout-budget-info-modal--hidden')
    }

    const updateReusedState = () => {
        const isActive = reusedInput.value === '1'

        reusedToggle.classList.toggle('sprout-budget-form__toggle-switch--active', isActive)
        reusedToggle.setAttribute('aria-pressed', isActive ? 'true' : 'false')
    }

    cycleTrigger?.addEventListener('click', openCycleModal)
    cycleCloseButton?.addEventListener('click', closeCycleModal)
    cycleCloseBackdrop?.addEventListener('click', closeCycleModal)

    infoOpen?.addEventListener('click', openInfoModal)
    infoCloseButton?.addEventListener('click', closeInfoModal)
    infoCloseBackdrop?.addEventListener('click', closeInfoModal)

    reusedToggle?.addEventListener('click', () => {
        reusedInput.value = reusedInput.value === '1' ? '0' : '1'
        updateReusedState()
    })

    cycleOptions.forEach((option) => {
        option.addEventListener('click', () => {
            const selectedValue = option.getAttribute('data-cycle-value') || 'monthly'
            const selectedLabel = option.getAttribute('data-cycle-label') || 'Monthly'

            cycleInput.value = selectedValue
            cycleDisplay.textContent = selectedLabel

            cycleOptions.forEach((item) => {
                item.classList.remove('sprout-budget-cycle-modal__list-item--active')
            })

            option.classList.add('sprout-budget-cycle-modal__list-item--active')
            closeCycleModal()
        })
    })

    updateReusedState()
})
</script>
</body>
</html>
