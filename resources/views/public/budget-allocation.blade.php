<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Budget Allocation | Sprout Income Expense Tracker</title>
<link rel="icon" type="image/svg+xml" href="/projectassets/images/logo/sprout-logo.svg">

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
    <div class="sprout-phone sprout-budget sprout-budget--form">
        <main class="sprout-budget__page">
            <div class="sprout-budget__content sprout-budget-allocation">

                <header class="sprout-budget-summary__topbar">
                    <a
                        href="{{ route('budget_index', ['month' => $selectedMonthValue]) }}"
                        class="sprout-budget-summary__back"
                        aria-label="Back to budget page"
                    >
                        ‹
                    </a>

                    <h1 class="sprout-budget-summary__title">Budget</h1>

                    <span class="sprout-budget-summary__topbar-space"></span>
                </header>

                @if (session('success'))
                    <div class="sprout-budget-form__alert sprout-budget-form__alert--success">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="sprout-budget-form__alert sprout-budget-form__alert--error">
                        Please check your budget amounts and try again.
                    </div>
                @endif

                <div class="sprout-budget-allocation__card">
                <form action="{{ route('budget_allocate_update', $budget) }}" method="POST" id="budget-allocation-form">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="month" value="{{ $selectedMonthValue }}">
                    <input type="hidden" name="name" id="budget-name-input" value="{{ old('name', $budget->name) }}">
                    <input type="hidden" name="cycle" id="budget-cycle-input" value="{{ old('cycle', $budget->cycle) }}">

                    <div class="sprout-budget-summary__hero">
                        <p class="sprout-budget-summary__repeat-indicator sprout-budget-summary__repeat-indicator--corner {{ $budget->is_reused ? 'sprout-budget-summary__repeat-indicator--reused' : 'sprout-budget-summary__repeat-indicator--one-time' }}">
                            {{ $budget->is_reused ? 'Repeats next cycle' : 'One-time budget' }}
                        </p>

                        <div class="sprout-budget-summary__chart-wrap">
                            <canvas id="budgetAllocationChart" width="120" height="120"></canvas>
                        </div>

                        <div class="sprout-budget-summary__amount-block">
                            <p class="sprout-budget-summary__name" id="budget-name-display">
                                {{ $budget->name }}
                            </p>

                            <p class="sprout-budget-summary__cycle">
                                <span id="budget-cycle-display">{{ ucfirst($budget->cycle) }}</span>
                            </p>


                            <div class="sprout-budget-summary__amount-row">
                            <p class="sprout-budget-summary__amount" id="budget-total-amount">
                                ₱{{ number_format($totalAllocated, 0) }}
                            </p>
                            <button
                                type="button"
                                class="sprout-budget-summary__edit-button"
                                id="budget-edit-trigger"
                                aria-label="Edit budget details"
                            >
                                <img src="{{ asset('projectassets/icons/edit.svg') }}" alt="Edit budget">
                            </button>
                        </div>
                    </div>

                    </div>

                    <div class="sprout-budget-summary__list sprout-budget-summary__list--allocation">
                        @foreach ($categoryRows as $categoryRow)
                            @php
                                $inputValue = old(
                                    'amounts.' . $categoryRow['key'],
                                    $categoryRow['amount'] > 0 ? number_format($categoryRow['amount'], 0, '.', '') : ''
                                );
                            @endphp

                            <div
                                class="sprout-budget-summary__item sprout-budget-summary__item--editable {{ !$categoryRow['is_active'] ? 'sprout-budget-summary__item--inactive' : '' }}"
                                data-budget-row
                                data-budget-name="{{ $categoryRow['name'] }}"
                                data-budget-color="{{ $categoryRow['color'] }}"
                                data-budget-key="{{ $categoryRow['key'] }}"
                            >
                                <div class="sprout-budget-summary__item-main">
                                    <div
                                        class="sprout-budget-summary__item-icon"
                                        style="--budget-icon-bg: {{ $categoryRow['is_active'] ? $categoryRow['color'] : '#E7E7E7' }};"
                                        data-budget-icon
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

                                <button
                                    type="button"
                                    class="sprout-budget-summary__item-trigger"
                                    data-budget-open
                                >
                                    <span class="sprout-budget-summary__peso-symbol">₱</span>
                                    <span class="sprout-budget-summary__item-trigger-value" data-budget-display>
                                        {{ $categoryRow['amount'] > 0 ? number_format($categoryRow['amount'], 0) : '0' }}
                                    </span>
                                </button>

                                <input
                                    type="hidden"
                                    name="amounts[{{ $categoryRow['key'] }}]"
                                    value="{{ $inputValue }}"
                                    data-budget-hidden-input
                                >
                            </div>
                        @endforeach
                    </div>

                    <div class="sprout-budget-summary__actions">
                        <button type="submit" class="sprout-budget-form__next-button">
                            Confirm
                        </button>
                    </div>
                </form>

                <form
                    action="{{ route('budget_destroy', $budget) }}"
                    method="POST"
                    class="sprout-budget-summary__reset-form"
                    onsubmit="return confirm('Reset this budget? This will remove the budget and its allocation.')"
                >
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="month" value="{{ $selectedMonthValue }}">

                    <button type="submit" class="sprout-budget-summary__reset-button">
                        Reset Budget
                    </button>
                </form>
                </div>

                @if ($isInheritedView)
                    <a
                        href="{{ route('budget_create', ['month' => $selectedMonthValue, 'source_budget_id' => $budget->id]) }}"
                        class="sprout-budget-allocation__override-link"
                    >
                        Customize only this month
                    </a>
                @endif

            </div>
        </main>
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
                &times;
            </button>

            <h2 class="sprout-budget-cycle-modal__title">Edit Budget</h2>

            <span class="sprout-budget-cycle-modal__header-space"></span>
        </div>

        <div class="sprout-budget-cycle-modal__field">
            <label class="sprout-budget-cycle-modal__field-label" for="budget-name-modal-input">Budget Name</label>
            <input
                type="text"
                id="budget-name-modal-input"
                class="sprout-budget-cycle-modal__field-input"
                value="{{ old('name', $budget->name) }}"
                maxlength="80"
                autocomplete="off"
            >
        </div>

        <p class="sprout-budget-cycle-modal__field-label sprout-budget-cycle-modal__field-label--section">
            Budget Cycle
        </p>

        <div class="sprout-budget-cycle-modal__list">
            @foreach ($cycleOptions as $cycleValue => $cycleLabel)
                <button
                    type="button"
                    class="sprout-budget-cycle-modal__list-item {{ old('cycle', $budget->cycle) === $cycleValue ? 'sprout-budget-cycle-modal__list-item--active' : '' }}"
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

<div class="sprout-budget-amount-modal sprout-budget-amount-modal--hidden" id="budget-amount-modal">
    <button
        type="button"
        class="sprout-budget-amount-modal__backdrop"
        id="budget-amount-modal-backdrop"
    ></button>

    <div class="sprout-budget-amount-modal__sheet">
        <div class="sprout-budget-amount-modal__header">
            <h2 class="sprout-budget-amount-modal__title" id="budget-amount-modal-title">
                Category
            </h2>

            <button
                type="button"
                class="sprout-budget-amount-modal__close"
                id="budget-amount-modal-close"
                aria-label="Close amount modal"
            >
                ×
            </button>
        </div>

        <div class="sprout-budget-amount-modal__body">
            <label class="sprout-budget-amount-modal__label" for="budget-amount-modal-input">
                Enter Amount
            </label>

            <div class="sprout-budget-amount-modal__input-wrap">
                <span class="sprout-budget-amount-modal__peso">₱</span>

                <input
                    type="text"
                    id="budget-amount-modal-input"
                    class="sprout-budget-amount-modal__input"
                    inputmode="numeric"
                    autocomplete="off"
                    placeholder="0"
                >
            </div>

            <button
                type="button"
                class="sprout-budget-amount-modal__save"
                id="budget-amount-modal-save"
            >
                Save
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('budgetAllocationChart')
    const totalAmount = document.getElementById('budget-total-amount')
    const budgetRows = document.querySelectorAll('[data-budget-row]')
    const amountModal = document.getElementById('budget-amount-modal')
    const amountModalBackdrop = document.getElementById('budget-amount-modal-backdrop')
    const amountModalClose = document.getElementById('budget-amount-modal-close')
    const amountModalSave = document.getElementById('budget-amount-modal-save')
    const amountModalInput = document.getElementById('budget-amount-modal-input')
    const amountModalTitle = document.getElementById('budget-amount-modal-title')
    const cycleModal = document.getElementById('budget-cycle-modal')
    const budgetEditTrigger = document.getElementById('budget-edit-trigger')
    const cycleDisplay = document.getElementById('budget-cycle-display')
    const budgetNameDisplay = document.getElementById('budget-name-display')
    const budgetNameInput = document.getElementById('budget-name-input')
    const budgetNameModalInput = document.getElementById('budget-name-modal-input')
    const cycleInput = document.getElementById('budget-cycle-input')
    const cycleOptions = document.querySelectorAll('[data-cycle-option]')
    const cycleCloseButton = document.getElementById('budget-cycle-close-button')
    const cycleCloseBackdrop = document.getElementById('budget-cycle-close-backdrop')

    let budgetChart = null
    let activeRow = null

    const parseAmount = (value) => {
        const cleanedValue = String(value || '').replace(/[^\d]/g, '').trim()
        const parsedValue = Number.parseInt(cleanedValue, 10)

        return Number.isFinite(parsedValue) ? Math.max(parsedValue, 0) : 0
    }

    const formatCurrencyWhole = (value) => {
        return `₱${new Intl.NumberFormat('en-PH', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(value)}`
    }

    const formatWholeNumber = (value) => {
        return new Intl.NumberFormat('en-PH', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(value)
    }

    const getRowHiddenInput = (row) => row.querySelector('[data-budget-hidden-input]')
    const getRowDisplay = (row) => row.querySelector('[data-budget-display]')
    const getRowIcon = (row) => row.querySelector('[data-budget-icon]')

    const updateRowState = (row) => {
        const hiddenInput = getRowHiddenInput(row)
        const display = getRowDisplay(row)
        const icon = getRowIcon(row)

        if (!hiddenInput || !display || !icon) {
            return
        }

        const amount = parseAmount(hiddenInput.value)
        const rowColor = row.getAttribute('data-budget-color') || '#E7E7E7'
        const isActive = amount > 0

        row.classList.toggle('sprout-budget-summary__item--inactive', !isActive)
        icon.style.setProperty('--budget-icon-bg', isActive ? rowColor : '#E7E7E7')
        display.textContent = amount > 0 ? formatWholeNumber(amount) : '0'
    }

    const rebuildChart = () => {
        if (!canvas || typeof Chart === 'undefined') {
            return
        }

        const chartRows = []

        budgetRows.forEach((row) => {
            const hiddenInput = getRowHiddenInput(row)
            const amount = parseAmount(hiddenInput?.value || '')

            if (amount <= 0) {
                return
            }

            chartRows.push({
                color: row.getAttribute('data-budget-color') || '#E7E7E7',
                amount
            })
        })

        const total = chartRows.reduce((sum, item) => sum + item.amount, 0)
        totalAmount.textContent = formatCurrencyWhole(total)

        if (budgetChart) {
            budgetChart.destroy()
        }

        const hasData = chartRows.length > 0
        const datasetValues = hasData ? chartRows.map((item) => item.amount) : [1]
        const datasetColors = hasData ? chartRows.map((item) => item.color) : ['#E4E4E4']

        budgetChart = new Chart(canvas, {
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

    const openAmountModal = (row) => {
        const hiddenInput = getRowHiddenInput(row)
        const categoryName = row.getAttribute('data-budget-name') || 'Category'
        const currentAmount = parseAmount(hiddenInput?.value || '')

        activeRow = row
        amountModalTitle.textContent = categoryName
        amountModalInput.value = currentAmount > 0 ? String(currentAmount) : ''
        amountModal.classList.remove('sprout-budget-amount-modal--hidden')

        window.setTimeout(() => {
            amountModalInput.focus()
        }, 50)
    }

    const closeAmountModal = () => {
        amountModal.classList.add('sprout-budget-amount-modal--hidden')
        amountModalInput.value = ''
        activeRow = null
    }

    const openCycleModal = () => {
        if (budgetNameInput && budgetNameModalInput) {
            budgetNameModalInput.value = budgetNameInput.value
        }

        cycleModal?.classList.remove('sprout-budget-cycle-modal--hidden')
    }

    const closeCycleModal = () => {
        cycleModal?.classList.add('sprout-budget-cycle-modal--hidden')
    }

    const saveAmountModal = () => {
        if (!activeRow) {
            closeAmountModal()
            return
        }

        const hiddenInput = getRowHiddenInput(activeRow)
        const amount = parseAmount(amountModalInput.value)

        if (hiddenInput) {
            hiddenInput.value = amount > 0 ? String(amount) : ''
        }

        updateRowState(activeRow)
        rebuildChart()
        closeAmountModal()
    }

    budgetRows.forEach((row) => {
        const openButton = row.querySelector('[data-budget-open]')

        openButton?.addEventListener('click', () => {
            openAmountModal(row)
        })

        updateRowState(row)
    })

    amountModalInput?.addEventListener('input', () => {
        amountModalInput.value = amountModalInput.value.replace(/[^\d]/g, '')
    })

    amountModalSave?.addEventListener('click', saveAmountModal)
    amountModalClose?.addEventListener('click', closeAmountModal)
    amountModalBackdrop?.addEventListener('click', closeAmountModal)
    budgetEditTrigger?.addEventListener('click', openCycleModal)
    cycleCloseButton?.addEventListener('click', closeCycleModal)
    cycleCloseBackdrop?.addEventListener('click', closeCycleModal)

    budgetNameModalInput?.addEventListener('input', () => {
        if (!budgetNameInput || !budgetNameModalInput) {
            return
        }

        budgetNameInput.value = budgetNameModalInput.value

        if (budgetNameDisplay) {
            budgetNameDisplay.textContent = budgetNameModalInput.value || 'Budget'
        }
    })

    cycleOptions.forEach((option) => {
        option.addEventListener('click', () => {
            const selectedValue = option.getAttribute('data-cycle-value') || 'monthly'
            const selectedLabel = option.getAttribute('data-cycle-label') || 'Monthly'

            if (cycleInput) {
                cycleInput.value = selectedValue
            }

            if (cycleDisplay) {
                cycleDisplay.textContent = selectedLabel
            }

            cycleOptions.forEach((item) => {
                item.classList.remove('sprout-budget-cycle-modal__list-item--active')
            })

            option.classList.add('sprout-budget-cycle-modal__list-item--active')
            closeCycleModal()
        })
    })

    amountModalInput?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault()
            saveAmountModal()
        }
    })

    rebuildChart()
})
</script>
</body>
</html>
