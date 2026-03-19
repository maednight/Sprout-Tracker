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
                <form action="{{ route('budget.allocate.update', $budget) }}" method="POST">
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

                                    <span class="sprout-budget-summary__item-name" data-budget-name-text>
                                        {{ $categoryRow['name'] }}
                                    </span>
                                </div>

                                <label class="sprout-budget-summary__item-input-wrap">
                                    <span class="sprout-budget-summary__peso-symbol">₱</span>

                                    <input
                                        type="text"
                                        name="amounts[{{ $categoryRow['key'] }}]"
                                        value="{{ $inputValue }}"
                                        class="sprout-budget-summary__item-input"
                                        data-budget-amount
                                        inputmode="numeric"
                                        autocomplete="off"
                                        placeholder="0"
                                    >
                                </label>
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

<script>
document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('budgetAllocationChart')
    const amountInputs = document.querySelectorAll('[data-budget-amount]')
    const totalAmount = document.getElementById('budget-total-amount')
    let budgetChart = null

    const parseAmount = (value) => {
        const cleanedValue = String(value || '').replace(/,/g, '').trim()
        const parsedValue = Number.parseFloat(cleanedValue)

        return Number.isFinite(parsedValue) ? Math.max(parsedValue, 0) : 0
    }

    const formatCurrencyWhole = (value) => {
        return `₱${new Intl.NumberFormat('en-PH', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(value)}`
    }

    const formatInputValue = (value) => {
        if (value === '' || value === null || typeof value === 'undefined') {
            return ''
        }

        const parsedValue = parseAmount(value)

        if (parsedValue <= 0) {
            return ''
        }

        return parsedValue.toLocaleString('en-PH', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        })
    }

    const updateRowState = (input) => {
        const row = input.closest('[data-budget-row]')
        const icon = row?.querySelector('[data-budget-icon]')

        if (!row || !icon) {
            return
        }

        const rowColor = row.getAttribute('data-budget-color') || '#E7E7E7'
        const amount = parseAmount(input.value)
        const isActive = amount > 0

        row.classList.toggle('sprout-budget-summary__item--inactive', !isActive)
        icon.style.setProperty('--budget-icon-bg', isActive ? rowColor : '#E7E7E7')
    }

    const rebuildChart = () => {
        if (!canvas) {
            return
        }

        const chartRows = []

        amountInputs.forEach((input) => {
            const row = input.closest('[data-budget-row]')
            const amount = parseAmount(input.value)

            if (!row || amount <= 0) {
                return
            }

            chartRows.push({
                name: row.getAttribute('data-budget-name') || '',
                color: row.getAttribute('data-budget-color') || '#E7E7E7',
                amount
            })
        })

        const total = chartRows.reduce((sum, item) => sum + item.amount, 0)
        totalAmount.textContent = formatCurrencyWhole(total)

        if (budgetChart) {
            budgetChart.destroy()
        }

        const datasetValues = chartRows.length > 0 ? chartRows.map((item) => item.amount) : [1]
        const datasetColors = chartRows.length > 0 ? chartRows.map((item) => item.color) : ['#E4E4E4']

        budgetChart = new Chart(canvas, {
            type: 'doughnut',
            data: {
                datasets: [
                    {
                        data: datasetValues,
                        backgroundColor: datasetColors,
                        borderWidth: 0,
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

    amountInputs.forEach((input) => {
        input.addEventListener('input', () => {
            const rawValue = input.value.replace(/[^\d]/g, '')
            input.value = rawValue
            updateRowState(input)
            rebuildChart()
        })

        input.addEventListener('blur', () => {
            input.value = formatInputValue(input.value)
            updateRowState(input)
            rebuildChart()
        })

        updateRowState(input)
    })

    rebuildChart()
})
</script>
</body>
</html>