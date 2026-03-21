<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - Sprout</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inknut+Antiqua:wght@400;600;700&family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @vite(['resources/css/app.css', 'resources/css/pages/transactions.css', 'resources/js/app.js'])
</head>
<body class="sprout-font">
<div class="sprout-shell">
    <div class="sprout-phone sprout-transactions">
        <main class="sprout-transactions__page">
            <div
                class="sprout-transactions__content"
                id="transaction-analytics-app"
                data-transaction-analytics='@json($transactionAnalyticsPayload)'
            >
                <header class="sprout-transactions__header">
                    <h1 class="sprout-transactions__title">Transactions</h1>
                </header>

                <div class="sprout-transactions__controls">
                    <div class="sprout-transactions__filter-wrap">
                        <button type="button" class="sprout-transactions__filter-trigger" data-filter-trigger>
                            <span data-filter-label>All</span>
                            <span class="sprout-transactions__filter-caret">▼</span>
                        </button>

                        <div class="sprout-transactions__filter-menu sprout-transactions__filter-menu--hidden" data-filter-menu>
                            <button type="button" class="sprout-transactions__filter-option sprout-transactions__filter-option--active" data-filter="all">All</button>
                            <button type="button" class="sprout-transactions__filter-option" data-filter="income">Income</button>
                            <button type="button" class="sprout-transactions__filter-option" data-filter="expense">Expense</button>
                            <button type="button" class="sprout-transactions__filter-option" data-filter="savings">Savings</button>
                        </div>
                    </div>

                    <div class="sprout-transactions__period">
                        <button type="button" class="sprout-transactions__period-arrow" data-shift="-1" aria-label="Previous period">
                            &#8249;
                        </button>
                        <button type="button" class="sprout-transactions__period-label" data-period-label data-period-trigger>This Week</button>
                        <button type="button" class="sprout-transactions__period-arrow" data-shift="1" aria-label="Next period">
                            &#8250;
                        </button>
                    </div>

                    <div class="sprout-transactions__controls-space"></div>
                </div>

                <section class="sprout-transactions__period-panel sprout-transactions__period-panel--hidden" data-period-panel>
                    <div class="sprout-transactions__period-tabs">
                        <button type="button" class="sprout-transactions__period-tab sprout-transactions__period-tab--active" data-scope="week">Week</button>
                        <button type="button" class="sprout-transactions__period-tab" data-scope="month">Month</button>
                        <button type="button" class="sprout-transactions__period-tab" data-scope="year">Year</button>
                    </div>
                </section>

                <section class="sprout-transactions__card sprout-transactions__card--chart">
                    <div class="sprout-transactions__line-wrap">
                        <canvas id="transactions-line-chart"></canvas>
                    </div>
                </section>

                <section class="sprout-transactions__card sprout-transactions__card--categories">
                    <div class="sprout-transactions__card-head">
                        <h2 class="sprout-transactions__card-title">Categories</h2>
                    </div>

                    <div class="sprout-transactions__pie-layout">
                        <div class="sprout-transactions__callouts" data-callouts></div>

                        <div class="sprout-transactions__pie-wrap">
                            <canvas id="transactions-pie-chart"></canvas>
                        </div>

                        <div class="sprout-transactions__legend" data-legend></div>
                    </div>

                    <div class="sprout-transactions__show-less">
                        <span class="sprout-transactions__show-less-line"></span>
                        <span class="sprout-transactions__show-less-text">show less</span>
                        <span class="sprout-transactions__show-less-line"></span>
                    </div>

                    <div class="sprout-transactions__stats">
                        <p class="sprout-transactions__stats-title">Categories Stat</p>
                        <div class="sprout-transactions__stats-list" data-stats></div>
                    </div>
                </section>
            </div>
        </main>

        @include('public.partials.nav-mobile')
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('transaction-analytics-app')

    if (!root || typeof Chart === 'undefined') {
        return
    }

    let payload = {
        transactionGroups: [],
        initialDisplayDate: new Date().toISOString().slice(0, 10),
    }

    try {
        const rawPayload = root.getAttribute('data-transaction-analytics')
        payload = rawPayload ? JSON.parse(rawPayload) : payload
    } catch (error) {
        console.error('Transaction analytics payload parse error:', error)
    }

    const scopeTabs = Array.from(root.querySelectorAll('.sprout-transactions__period-tab[data-scope]'))
    const filterTabs = Array.from(root.querySelectorAll('.sprout-transactions__filter-option[data-filter]'))
    const shiftButtons = Array.from(root.querySelectorAll('[data-shift]'))
    const periodLabel = root.querySelector('[data-period-label]')
    const periodTrigger = root.querySelector('[data-period-trigger]')
    const periodPanel = root.querySelector('[data-period-panel]')
    const filterTrigger = root.querySelector('[data-filter-trigger]')
    const filterMenu = root.querySelector('[data-filter-menu]')
    const filterLabel = root.querySelector('[data-filter-label]')
    const legend = root.querySelector('[data-legend]')
    const stats = root.querySelector('[data-stats]')
    const callouts = root.querySelector('[data-callouts]')

    const parseDate = (dateKey) => {
        const [year, month, day] = dateKey.split('-').map(Number)
        return new Date(year, month - 1, day)
    }

    const formatMoney = (value) => new Intl.NumberFormat('en-PH').format(Number(value || 0))
    const payloadGroups = Array.isArray(payload.transactionGroups) ? payload.transactionGroups : []

    const transactions = payloadGroups.flatMap((group) => {
        const groupDate = parseDate(group.dateKey)

        return (group.transactions || []).map((transaction) => ({
            ...transaction,
            amount: Number(transaction.amount || 0),
            date: groupDate,
            dateKey: group.dateKey,
        }))
    })

    const state = {
        scope: 'week',
        filter: 'all',
        anchorDate: payload.initialDisplayDate ? parseDate(payload.initialDisplayDate) : new Date(),
    }

    const closePanels = () => {
        periodPanel?.classList.add('sprout-transactions__period-panel--hidden')
        filterMenu?.classList.add('sprout-transactions__filter-menu--hidden')
    }

    const palette = ['#ff6f61', '#2396f3', '#00f36b', '#f8c646', '#9b5de5', '#f15bb5', '#ff9f43', '#43c6ac']

    const startOfWeek = (date) => {
        const start = new Date(date)
        const day = start.getDay()
        const diff = day === 0 ? -6 : 1 - day
        start.setDate(start.getDate() + diff)
        start.setHours(0, 0, 0, 0)
        return start
    }

    const getRange = () => {
        if (state.scope === 'month') {
            return {
                start: new Date(state.anchorDate.getFullYear(), state.anchorDate.getMonth(), 1),
                end: new Date(state.anchorDate.getFullYear(), state.anchorDate.getMonth() + 1, 0, 23, 59, 59, 999),
            }
        }

        if (state.scope === 'year') {
            return {
                start: new Date(state.anchorDate.getFullYear(), 0, 1),
                end: new Date(state.anchorDate.getFullYear(), 11, 31, 23, 59, 59, 999),
            }
        }

        const start = startOfWeek(state.anchorDate)
        const end = new Date(start)
        end.setDate(start.getDate() + 6)
        end.setHours(23, 59, 59, 999)
        return { start, end }
    }

    const getVisibleTransactions = () => {
        const { start, end } = getRange()

        return transactions.filter((transaction) => {
            const inRange = transaction.date >= start && transaction.date <= end
            const matchesType = state.filter === 'all' || transaction.type === state.filter

            return inRange && matchesType
        })
    }

    const buildTrend = (visibleTransactions) => {
        if (state.scope === 'month') {
            const totalDays = new Date(state.anchorDate.getFullYear(), state.anchorDate.getMonth() + 1, 0).getDate()
            const labels = Array.from({ length: totalDays }, (_, index) => String(index + 1))
            const buckets = labels.map(() => ({ income: 0, expense: 0, savings: 0 }))

            visibleTransactions.forEach((transaction) => {
                const index = transaction.date.getDate() - 1

                if (buckets[index]) {
                    buckets[index][transaction.type] += transaction.amount
                }
            })

            return {
                labels,
                income: buckets.map((bucket) => bucket.income),
                expense: buckets.map((bucket) => bucket.expense),
                savings: buckets.map((bucket) => bucket.savings),
            }
        }

        if (state.scope === 'year') {
            const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
            const buckets = labels.map(() => ({ income: 0, expense: 0, savings: 0 }))

            visibleTransactions.forEach((transaction) => {
                buckets[transaction.date.getMonth()][transaction.type] += transaction.amount
            })

            return {
                labels,
                income: buckets.map((bucket) => bucket.income),
                expense: buckets.map((bucket) => bucket.expense),
                savings: buckets.map((bucket) => bucket.savings),
            }
        }

        const labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
        const weekStart = startOfWeek(state.anchorDate)
        const buckets = labels.map((label, index) => {
            const date = new Date(weekStart)
            date.setDate(weekStart.getDate() + index)

            return {
                label,
                dateKey: date.toISOString().slice(0, 10),
                income: 0,
                expense: 0,
                savings: 0,
            }
        })

        visibleTransactions.forEach((transaction) => {
            const bucket = buckets.find((item) => item.dateKey === transaction.dateKey)

            if (bucket) {
                bucket[transaction.type] += transaction.amount
            }
        })

        return {
            labels,
            income: buckets.map((bucket) => bucket.income),
            expense: buckets.map((bucket) => bucket.expense),
            savings: buckets.map((bucket) => bucket.savings),
        }
    }

    const buildCategories = (visibleTransactions) => {
        const categoryMap = new Map()

        visibleTransactions.forEach((transaction) => {
            const key = transaction.category || 'Others'

            if (!categoryMap.has(key)) {
                categoryMap.set(key, {
                    name: key,
                    amount: 0,
                    count: 0,
                    iconPath: transaction.iconPath || '/projectassets/icons/others.svg',
                    color: palette[categoryMap.size % palette.length],
                })
            }

            const category = categoryMap.get(key)
            category.amount += transaction.amount
            category.count += 1
        })

        return Array.from(categoryMap.values()).sort((left, right) => right.amount - left.amount)
    }

    const lineChart = new Chart(document.getElementById('transactions-line-chart'), {
        type: 'line',
        data: {
            labels: [],
            datasets: [],
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        boxWidth: 8,
                        boxHeight: 8,
                        padding: 16,
                        font: {
                            family: 'Inter',
                            size: 11,
                        },
                    },
                },
                tooltip: {
                    backgroundColor: '#ffffff',
                    titleColor: '#1f1f1f',
                    bodyColor: '#1f1f1f',
                    borderColor: 'rgba(0, 0, 0, 0.08)',
                    borderWidth: 1,
                    displayColors: true,
                },
            },
            layout: {
                padding: {
                    top: 6,
                    right: 8,
                    bottom: 0,
                    left: 2,
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (value) => `₱${formatMoney(value)}`,
                        stepSize: 500,
                        color: '#666666',
                        font: {
                            family: 'Inter',
                            size: 10,
                            weight: '500',
                        },
                    },
                    border: {
                        display: false,
                    },
                    grid: {
                        color: '#d9d9e7',
                        borderDash: [2, 3],
                        drawBorder: false,
                    },
                },
                x: {
                    ticks: {
                        color: '#666666',
                        font: {
                            family: 'Inter',
                            size: 10,
                            weight: '500',
                        },
                    },
                    border: {
                        display: false,
                    },
                    grid: {
                        color: '#d9d9e7',
                        borderDash: [2, 3],
                        drawBorder: false,
                    },
                },
            },
            elements: {
                line: {
                    borderWidth: 2,
                    tension: 0.35,
                },
                point: {
                    radius: 4,
                    hoverRadius: 4,
                    borderWidth: 2,
                    backgroundColor: '#ffffff',
                },
            },
        },
    })

    const pieChart = new Chart(document.getElementById('transactions-pie-chart'), {
        type: 'doughnut',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: [],
                borderWidth: 0,
            }],
        },
        options: {
            maintainAspectRatio: false,
            cutout: '58%',
            plugins: {
                legend: {
                    display: false,
                },
            },
        },
    })

    const renderCategoryArea = (categories) => {
        if (categories.length === 0) {
            legend.innerHTML = ''
            callouts.innerHTML = ''
            stats.innerHTML = '<div class="sprout-transactions__empty">No transactions for this period.</div>'
            return
        }

        legend.innerHTML = categories.slice(0, 4).map((category) => `
            <div class="sprout-transactions__legend-item">
                <span class="sprout-transactions__legend-dot" style="background:${category.color};"></span>
                <span class="sprout-transactions__legend-text">${category.name}</span>
            </div>
        `).join('')

        const calloutItems = categories.slice(0, 2)
        callouts.innerHTML = calloutItems.map((category, index) => `
            <div class="sprout-transactions__callout sprout-transactions__callout--${index === 0 ? 'left' : 'right'}">
                <span class="sprout-transactions__callout-name">${category.name}</span>
                <span class="sprout-transactions__callout-amount" style="color:${category.color};">₱${formatMoney(category.amount)}</span>
            </div>
        `).join('')

        const highestAmount = categories[0].amount || 1
        stats.innerHTML = categories.map((category) => `
            <div class="sprout-transactions__stat-row">
                <div class="sprout-transactions__stat-main">
                    <div class="sprout-transactions__stat-icon-wrap">
                        <div class="sprout-transactions__stat-icon" style="background:${category.color};">
                            <img src="${category.iconPath}" alt="${category.name}">
                        </div>
                        <div class="sprout-transactions__stat-bar">
                            <span class="sprout-transactions__stat-bar-fill" style="background:${category.color}; width:${Math.max((category.amount / highestAmount) * 100, 12)}%;"></span>
                        </div>
                    </div>

                    <div class="sprout-transactions__stat-copy">
                        <div class="sprout-transactions__stat-name">${category.name}</div>
                        <div class="sprout-transactions__stat-count">${category.count} tx.</div>
                    </div>
                </div>

                <div class="sprout-transactions__stat-side">
                    <div class="sprout-transactions__stat-amount">₱${formatMoney(category.amount)}</div>
                    <div class="sprout-transactions__stat-chevron">›</div>
                </div>
            </div>
        `).join('')
    }

    const render = () => {
        const visibleTransactions = getVisibleTransactions()
        const trend = buildTrend(visibleTransactions)
        const categories = buildCategories(visibleTransactions)
        const datasets = []

        if (state.filter === 'all' || state.filter === 'expense') {
            datasets.push({
                label: 'Expense',
                data: trend.expense,
                borderColor: '#ff6f61',
                backgroundColor: '#ff6f61',
                pointBorderColor: '#ff6f61',
            })
        }

        if (state.filter === 'all' || state.filter === 'income') {
            datasets.push({
                label: 'Income',
                data: trend.income,
                borderColor: '#00f36b',
                backgroundColor: '#00f36b',
                pointBorderColor: '#00f36b',
            })
        }

        if (state.filter === 'all' || state.filter === 'savings') {
            datasets.push({
                label: 'Savings',
                data: trend.savings,
                borderColor: '#2396f3',
                backgroundColor: '#2396f3',
                pointBorderColor: '#2396f3',
            })
        }

        lineChart.data.labels = trend.labels
        lineChart.data.datasets = datasets
        lineChart.update()

        pieChart.data.labels = categories.map((category) => category.name)
        pieChart.data.datasets[0].data = categories.map((category) => category.amount)
        pieChart.data.datasets[0].backgroundColor = categories.map((category) => category.color)
        pieChart.update()

        renderCategoryArea(categories)

        if (state.scope === 'month') {
            periodLabel.textContent = state.anchorDate.toLocaleDateString('en-US', { month: 'long' })
        } else if (state.scope === 'year') {
            periodLabel.textContent = String(state.anchorDate.getFullYear())
        } else {
            periodLabel.textContent = 'This Week'
        }

        filterLabel.textContent = state.filter === 'all'
            ? 'All'
            : state.filter.charAt(0).toUpperCase() + state.filter.slice(1)
    }

    scopeTabs.forEach((button) => {
        button.addEventListener('click', () => {
            state.scope = button.dataset.scope || 'week'
            scopeTabs.forEach((item) => item.classList.remove('sprout-transactions__period-tab--active'))
            button.classList.add('sprout-transactions__period-tab--active')
            closePanels()
            render()
        })
    })

    filterTabs.forEach((button) => {
        button.addEventListener('click', () => {
            state.filter = button.dataset.filter || 'all'
            filterTabs.forEach((item) => item.classList.remove('sprout-transactions__filter-option--active'))
            button.classList.add('sprout-transactions__filter-option--active')
            closePanels()
            render()
        })
    })

    filterTrigger?.addEventListener('click', (event) => {
        event.stopPropagation()
        const isHidden = filterMenu.classList.contains('sprout-transactions__filter-menu--hidden')
        closePanels()

        if (isHidden) {
            filterMenu.classList.remove('sprout-transactions__filter-menu--hidden')
        }
    })

    periodTrigger?.addEventListener('click', (event) => {
        event.stopPropagation()
        const isHidden = periodPanel.classList.contains('sprout-transactions__period-panel--hidden')
        closePanels()

        if (isHidden) {
            periodPanel.classList.remove('sprout-transactions__period-panel--hidden')
        }
    })

    document.addEventListener('click', (event) => {
        if (!root.contains(event.target)) {
            closePanels()
        }
    })

    shiftButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const shift = Number(button.dataset.shift || 0)

            if (state.scope === 'month') {
                state.anchorDate = new Date(state.anchorDate.getFullYear(), state.anchorDate.getMonth() + shift, 1)
            } else if (state.scope === 'year') {
                state.anchorDate = new Date(state.anchorDate.getFullYear() + shift, 0, 1)
            } else {
                const nextDate = new Date(state.anchorDate)
                nextDate.setDate(nextDate.getDate() + (shift * 7))
                state.anchorDate = nextDate
            }

            render()
        })
    })

    render()
})
</script>
</body>
</html>
