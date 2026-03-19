<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Budget Allocation - Sprout</title>

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

                <!-- Budget Header -->
                <header class="sprout-budget-summary__topbar">
                    <a
                        href="{{ route('budget.index', ['month' => optional($budget->period_date)->format('Y-m')]) }}"
                        class="sprout-budget-summary__back"
                        aria-label="Back to budget page"
                    >
                        ‹
                    </a>

                    <h1 class="sprout-budget-summary__title">Budget</h1>

                    <span class="sprout-budget-summary__topbar-space"></span>
                </header>

                <!-- Budget Error Alert -->
                @if ($errors->any())
                    <div class="sprout-budget-form__alert sprout-budget-form__alert--error">
                        Please check your budget amounts and try again.
                    </div>
                @endif

                <!-- Budget Allocation Form -->
                <form action="{{ route('budget.allocate.update', $budget) }}" method="POST" id="budget-allocation-form">
                    @csrf
                    @method('PUT')

                    <!-- Budget Hero -->
                    <div class="sprout-budget-summary__hero">
                        <div class="sprout-budget-summary__chart-wrap">
                            <canvas id="budgetAllocationChart" width="120" height="120"></canvas>
                        </div>

                        <div class="sprout-budget-summary__amount-block">
                            <p class="sprout-budget-summary__cycle">
                                {{ ucfirst($budget->cycle) }}
                            </p>

                            <p class="sprout-budget-summary__amount" id="budget-total-amount">
                                ₱{{ number_format($totalAllocated, 0) }}
                            </p>

                            <span class="sprout-budget-summary__edit">
                                <img src="{{ asset('projectassets/icons/edit.svg') }}" alt="Edit budget">
                            </span>
                        </div>
                    </div>

                    <!-- Budget Category List -->
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

                    <!-- Budget Actions -->
                    <div class="sprout-budget-summary__actions">
                        <button type="submit" class="sprout-budget-form__next-button">
                            Confirm
                        </button>
                    </div>
                </form>

            </div>
        </main>
    </div>
</div>

<!-- Budget Amount Modal -->
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
        if (!canvas) {
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