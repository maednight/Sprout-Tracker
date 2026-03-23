<div class="sprout-shell">
    <div class="sprout-phone sprout-transactions">
        <main class="sprout-transactions__page">
            <div
                class="sprout-transactions__content"
                id="transaction-analytics-app"
                data-transaction-analytics='@json($transactionAnalyticsPayload)'
            >
                <div class="sprout-transactions__panel-overlay sprout-transactions__panel-overlay--hidden" data-panel-overlay></div>

                <section class="sprout-transactions__summary-view" data-summary-view>
                    <div class="sprout-transactions__controls">
                        <div class="sprout-transactions__controls-space"></div>

                        <div class="sprout-transactions__period">
                            <button type="button" class="sprout-transactions__period-arrow" data-shift="-1" aria-label="Previous period">
                                &#8249;
                            </button>
                            <button type="button" class="sprout-transactions__period-label" data-period-label data-period-trigger>This Month</button>
                            <button type="button" class="sprout-transactions__period-arrow" data-shift="1" aria-label="Next period">
                                &#8250;
                            </button>
                        </div>

                        <div class="sprout-transactions__controls-space"></div>
                    </div>

                    <section class="sprout-transactions__period-panel sprout-transactions__period-panel--hidden" data-period-panel>
                        <div class="sprout-transactions__period-tabs">
                            <button type="button" class="sprout-transactions__period-tab" data-scope="week">Week</button>
                            <button type="button" class="sprout-transactions__period-tab sprout-transactions__period-tab--active" data-scope="month">Month</button>
                            <button type="button" class="sprout-transactions__period-tab" data-scope="year">Year</button>
                        </div>

                        <div class="sprout-transactions__month-panel" data-month-panel>
                            <div class="sprout-transactions__year-head sprout-transactions__year-head--month">
                                <button type="button" class="sprout-transactions__year-arrow" data-month-year-shift="-1" aria-label="Previous year">
                                    &lsaquo;
                                </button>
                                <span class="sprout-transactions__year-value" data-month-year-value></span>
                                <button type="button" class="sprout-transactions__year-arrow" data-month-year-shift="1" aria-label="Next year">
                                    &rsaquo;
                                </button>
                            </div>

                            <div class="sprout-transactions__month-grid">
                                <button type="button" class="sprout-transactions__month-option" data-month-option="0">Jan</button>
                                <button type="button" class="sprout-transactions__month-option" data-month-option="1">Feb</button>
                                <button type="button" class="sprout-transactions__month-option" data-month-option="2">Mar</button>
                                <button type="button" class="sprout-transactions__month-option" data-month-option="3">Apr</button>
                                <button type="button" class="sprout-transactions__month-option" data-month-option="4">May</button>
                                <button type="button" class="sprout-transactions__month-option" data-month-option="5">Jun</button>
                                <button type="button" class="sprout-transactions__month-option" data-month-option="6">Jul</button>
                                <button type="button" class="sprout-transactions__month-option" data-month-option="7">Aug</button>
                                <button type="button" class="sprout-transactions__month-option" data-month-option="8">Sep</button>
                                <button type="button" class="sprout-transactions__month-option" data-month-option="9">Oct</button>
                                <button type="button" class="sprout-transactions__month-option" data-month-option="10">Nov</button>
                                <button type="button" class="sprout-transactions__month-option" data-month-option="11">Dec</button>
                            </div>
                        </div>

                        <div class="sprout-transactions__week-panel sprout-transactions__week-panel--hidden" data-week-panel>
                            <div class="sprout-transactions__week-head">
                                <button type="button" class="sprout-transactions__week-arrow" data-week-month-shift="-1" aria-label="Previous month">
                                    &#8249;
                                </button>
                                <span class="sprout-transactions__week-value" data-week-value></span>
                                <button type="button" class="sprout-transactions__week-arrow" data-week-month-shift="1" aria-label="Next month">
                                    &#8250;
                                </button>
                            </div>

                            <div class="sprout-transactions__week-weekdays">
                                <span>Mon</span>
                                <span>Tue</span>
                                <span>Wed</span>
                                <span>Thu</span>
                                <span>Fri</span>
                                <span>Sat</span>
                                <span>Sun</span>
                            </div>

                            <div class="sprout-transactions__week-grid" data-week-grid></div>
                        </div>

                        <div class="sprout-transactions__year-panel sprout-transactions__year-panel--hidden" data-year-panel>
                            <div class="sprout-transactions__year-head">
                                <button type="button" class="sprout-transactions__year-arrow" data-year-shift="-1" aria-label="Previous year">
                                    &lsaquo;
                                </button>
                                <span class="sprout-transactions__year-value" data-year-value></span>
                                <button type="button" class="sprout-transactions__year-arrow" data-year-shift="1" aria-label="Next year">
                                    &rsaquo;
                                </button>
                            </div>

                            <div class="sprout-transactions__year-months">
                                <button type="button" class="sprout-transactions__year-month" data-year-month="0">Jan</button>
                                <button type="button" class="sprout-transactions__year-month" data-year-month="1">Feb</button>
                                <button type="button" class="sprout-transactions__year-month" data-year-month="2">Mar</button>
                                <button type="button" class="sprout-transactions__year-month" data-year-month="3">Apr</button>
                                <button type="button" class="sprout-transactions__year-month" data-year-month="4">May</button>
                                <button type="button" class="sprout-transactions__year-month" data-year-month="5">Jun</button>
                                <button type="button" class="sprout-transactions__year-month" data-year-month="6">Jul</button>
                                <button type="button" class="sprout-transactions__year-month" data-year-month="7">Aug</button>
                                <button type="button" class="sprout-transactions__year-month" data-year-month="8">Sep</button>
                                <button type="button" class="sprout-transactions__year-month" data-year-month="9">Oct</button>
                                <button type="button" class="sprout-transactions__year-month" data-year-month="10">Nov</button>
                                <button type="button" class="sprout-transactions__year-month" data-year-month="11">Dec</button>
                            </div>
                        </div>
                    </section>

                    <section class="sprout-transactions__card sprout-transactions__card--chart">
                        <div class="sprout-transactions__line-wrap">
                            <canvas id="transactions-line-chart"></canvas>
                        </div>
                        <div class="sprout-transactions__trend-filters" data-line-legend></div>
                    </section>

                    <section class="sprout-transactions__card sprout-transactions__card--categories">
                        <div class="sprout-transactions__card-head">
                            <h2 class="sprout-transactions__card-title">Categories</h2>
                        </div>

                        <div class="sprout-transactions__trend-filters sprout-transactions__trend-filters--categories" data-category-filters></div>

                        <div class="sprout-transactions__pie-layout">
                            <div class="sprout-transactions__pie-wrap">
                                <canvas id="transactions-pie-chart"></canvas>
                            </div>

                            <div class="sprout-transactions__legend" data-legend></div>

                            <div class="sprout-transactions__pie-popup sprout-transactions__pie-popup--hidden" data-pie-popup>
                                <span class="sprout-transactions__pie-popup-name" data-pie-popup-name></span>
                                <span class="sprout-transactions__pie-popup-amount" data-pie-popup-amount></span>
                            </div>

                            <div class="sprout-transactions__category-empty sprout-transactions__category-empty--hidden" data-category-empty>
                                <img src="{{ asset('projectassets/icons/notes.svg') }}" alt="" class="sprout-transactions__category-empty-icon">
                                <span class="sprout-transactions__category-empty-text">No data available</span>
                            </div>
                        </div>

                        <button type="button" class="sprout-transactions__show-toggle" data-show-toggle aria-expanded="true">
                            <span class="sprout-transactions__show-toggle-line"></span>
                            <span class="sprout-transactions__show-toggle-text" data-show-toggle-text>show less</span>
                            <span class="sprout-transactions__show-toggle-line"></span>
                        </button>

                        <div class="sprout-transactions__stats" data-stats-panel>
                            <p class="sprout-transactions__stats-title">Categories Stat</p>
                            <div class="sprout-transactions__stats-list" data-stats></div>
                        </div>
                    </section>
                </section>

                <section class="sprout-transactions__detail-view sprout-transactions__detail-view--hidden" data-detail-view>
                    <header class="sprout-transactions__detail-header">
                        <button type="button" class="sprout-transactions__detail-icon-button" data-detail-close aria-label="Close category detail">
                            &#215;
                        </button>

                        <h2 class="sprout-transactions__detail-title" data-detail-title>Category</h2>

                        <div class="sprout-transactions__detail-filter-wrap">
                            <button type="button" class="sprout-transactions__detail-filter-button" data-sort-trigger aria-label="Sort transactions">
                                <img src="{{ asset('projectassets/icons/filtericon.svg') }}" alt="">
                            </button>

                            <div class="sprout-transactions__detail-filter-menu sprout-transactions__detail-filter-menu--hidden" data-sort-menu>
                                <button type="button" class="sprout-transactions__detail-filter-option sprout-transactions__detail-filter-option--active" data-sort="newest">
                                    <img src="{{ asset('projectassets/icons/filtericon.svg') }}" alt="">
                                    <span>Newest to oldest</span>
                                </button>
                                <button type="button" class="sprout-transactions__detail-filter-option" data-sort="oldest">
                                    <img src="{{ asset('projectassets/icons/filtericon.svg') }}" alt="">
                                    <span>Oldest to newest</span>
                                </button>
                                <button type="button" class="sprout-transactions__detail-filter-option" data-sort="highest">
                                    <img src="{{ asset('projectassets/icons/filtericon.svg') }}" alt="">
                                    <span>Price: highest to lowest</span>
                                </button>
                                <button type="button" class="sprout-transactions__detail-filter-option" data-sort="lowest">
                                    <img src="{{ asset('projectassets/icons/filtericon.svg') }}" alt="">
                                    <span>Price: lowest to highest</span>
                                </button>
                            </div>
                        </div>
                    </header>

                    <section class="sprout-transactions__detail-card" data-detail-budget-card>
                        <div class="sprout-transactions__detail-card-head">
                            <p class="sprout-transactions__detail-card-title" data-detail-card-title>Category Budget</p>
                            <span class="sprout-transactions__detail-exceed-pill sprout-transactions__detail-exceed-pill--hidden" data-detail-exceed-pill>
                                EXCEED <strong data-detail-exceed-pill-amount>&#8369;0</strong>
                            </span>
                        </div>

                        <div class="sprout-transactions__detail-donut-layout">
                            <div class="sprout-transactions__detail-donut-wrap">
                                <canvas id="transactions-detail-chart"></canvas>
                            </div>

                            <div class="sprout-transactions__detail-legend">
                                <div class="sprout-transactions__detail-legend-item">
                                    <span class="sprout-transactions__detail-legend-dot" data-detail-spent-dot></span>
                                    <div class="sprout-transactions__detail-legend-copy">
                                        <span class="sprout-transactions__detail-legend-name" data-detail-callout-name>Category</span>
                                        <span class="sprout-transactions__detail-legend-amount" data-detail-spent-callout>&#8369;0</span>
                                    </div>
                                </div>

                                <div class="sprout-transactions__detail-legend-item">
                                    <span class="sprout-transactions__detail-legend-dot sprout-transactions__detail-legend-dot--neutral"></span>
                                    <div class="sprout-transactions__detail-legend-copy">
                                        <span class="sprout-transactions__detail-legend-name">Budget</span>
                                        <span class="sprout-transactions__detail-legend-amount sprout-transactions__detail-legend-amount--neutral" data-detail-budget-callout>&#8369;0</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="sprout-transactions__detail-total-row">
                            <div class="sprout-transactions__detail-total">
                                <span class="sprout-transactions__detail-total-icon">
                                    <img src="{{ asset('projectassets/icons/coins.svg') }}" alt="">
                                </span>
                                <span class="sprout-transactions__detail-total-label">Total</span>
                            </div>

                            <div class="sprout-transactions__detail-summary-line">
                                <span class="sprout-transactions__detail-summary-budget">BUDGET <strong data-detail-budget-inline>&#8369;0</strong></span>
                                <span class="sprout-transactions__detail-summary-out">OUT <strong data-detail-out-inline>&#8369;0</strong></span>
                            </div>
                        </div>
                    </section>

                    <div class="sprout-transactions__detail-history" data-detail-history></div>
                </section>

                <section class="sprout-transactions__view-modal sprout-transactions__view-modal--hidden" data-transaction-view-modal>
                    <button
                        type="button"
                        class="sprout-transactions__view-backdrop"
                        data-transaction-view-close
                        aria-label="Close transaction details"
                    ></button>

                    <div class="sprout-transactions__view-sheet">
                        <div class="sprout-transactions__view-title">Transaction Details</div>

                        <div class="sprout-transactions__view-card">
                            <div class="sprout-transactions__view-row">
                                <span class="sprout-transactions__view-label">Category</span>
                                <span class="sprout-transactions__view-value" data-transaction-view-category></span>
                            </div>

                            <div class="sprout-transactions__view-row">
                                <span class="sprout-transactions__view-label">Type</span>
                                <span class="sprout-transactions__view-value" data-transaction-view-type></span>
                            </div>

                            <div class="sprout-transactions__view-row">
                                <span class="sprout-transactions__view-label">Date</span>
                                <span class="sprout-transactions__view-value" data-transaction-view-date></span>
                            </div>

                            <div class="sprout-transactions__view-row">
                                <span class="sprout-transactions__view-label">Time</span>
                                <span class="sprout-transactions__view-value" data-transaction-view-time></span>
                            </div>

                            <div class="sprout-transactions__view-row">
                                <span class="sprout-transactions__view-label">Amount</span>
                                <span class="sprout-transactions__view-value sprout-transactions__view-value--amount" data-transaction-view-amount></span>
                            </div>

                            <div class="sprout-transactions__view-row sprout-transactions__view-row--hidden" data-transaction-view-account-row>
                                <span class="sprout-transactions__view-label">Account</span>
                                <span class="sprout-transactions__view-value" data-transaction-view-account></span>
                            </div>

                            <div class="sprout-transactions__view-description-block sprout-transactions__view-description-block--hidden" data-transaction-view-description-row>
                                <div class="sprout-transactions__view-description-label">Description</div>
                                <div class="sprout-transactions__view-description-value" data-transaction-view-description></div>
                            </div>

                            <div class="sprout-transactions__view-photos-block sprout-transactions__view-photos-block--hidden" data-transaction-view-photos-row>
                                <div class="sprout-transactions__view-description-label">Receipt Photos</div>
                                <div class="sprout-transactions__view-photos-grid" data-transaction-view-photos></div>
                            </div>
                        </div>

                        <button type="button" class="sprout-transactions__view-close-button" data-transaction-view-close>
                            Close
                        </button>
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
        categoryMeta: [],
        budgetSnapshots: {},
        initialDisplayDate: new Date().toISOString().slice(0, 10),
    }

    try {
        const rawPayload = root.getAttribute('data-transaction-analytics')
        payload = rawPayload ? JSON.parse(rawPayload) : payload
    } catch (error) {
        console.error('Transaction analytics payload parse error:', error)
    }

    const summaryView = root.querySelector('[data-summary-view]')
    const detailView = root.querySelector('[data-detail-view]')
    const scopeTabs = Array.from(root.querySelectorAll('.sprout-transactions__period-tab[data-scope]'))
    const sortTabs = Array.from(root.querySelectorAll('.sprout-transactions__detail-filter-option[data-sort]'))
    const shiftButtons = Array.from(root.querySelectorAll('[data-shift]'))
    const weekMonthShiftButtons = Array.from(root.querySelectorAll('[data-week-month-shift]'))
    const yearShiftButtons = Array.from(root.querySelectorAll('[data-year-shift]'))
    const monthYearShiftButtons = Array.from(root.querySelectorAll('[data-month-year-shift]'))
    const yearMonthButtons = Array.from(root.querySelectorAll('[data-year-month]'))
    const monthOptionButtons = Array.from(root.querySelectorAll('[data-month-option]'))
    const periodLabel = root.querySelector('[data-period-label]')
    const periodTrigger = root.querySelector('[data-period-trigger]')
    const periodPanel = root.querySelector('[data-period-panel]')
    const panelOverlay = root.querySelector('[data-panel-overlay]')
    const weekPanel = root.querySelector('[data-week-panel]')
    const weekValue = root.querySelector('[data-week-value]')
    const weekGrid = root.querySelector('[data-week-grid]')
    const monthPanel = root.querySelector('[data-month-panel]')
    const yearPanel = root.querySelector('[data-year-panel]')
    const yearValue = root.querySelector('[data-year-value]')
    const monthYearValue = root.querySelector('[data-month-year-value]')
    const showToggle = root.querySelector('[data-show-toggle]')
    const showToggleText = root.querySelector('[data-show-toggle-text]')
    const statsPanel = root.querySelector('[data-stats-panel]')
    const stats = root.querySelector('[data-stats]')
    const legend = root.querySelector('[data-legend]')
    const categoryEmpty = root.querySelector('[data-category-empty]')
    const piePopup = root.querySelector('[data-pie-popup]')
    const piePopupName = root.querySelector('[data-pie-popup-name]')
    const piePopupAmount = root.querySelector('[data-pie-popup-amount]')
    const pieCanvas = document.getElementById('transactions-pie-chart')
    const lineLegend = root.querySelector('[data-line-legend]')
    const categoryFilters = root.querySelector('[data-category-filters]')
    const detailClose = root.querySelector('[data-detail-close]')
    const sortTrigger = root.querySelector('[data-sort-trigger]')
    const sortMenu = root.querySelector('[data-sort-menu]')
    const detailTitle = root.querySelector('[data-detail-title]')
    const detailBudgetCard = root.querySelector('[data-detail-budget-card]')
    const detailCardTitle = root.querySelector('[data-detail-card-title]')
    const detailCalloutName = root.querySelector('[data-detail-callout-name]')
    const detailSpentCallout = root.querySelector('[data-detail-spent-callout]')
    const detailSpentDot = root.querySelector('[data-detail-spent-dot]')
    const detailBudgetCallout = root.querySelector('[data-detail-budget-callout]')
    const detailBudgetInline = root.querySelector('[data-detail-budget-inline]')
    const detailOutInline = root.querySelector('[data-detail-out-inline]')
    const detailExceedPill = root.querySelector('[data-detail-exceed-pill]')
    const detailExceedPillAmount = root.querySelector('[data-detail-exceed-pill-amount]')
    const detailHistory = root.querySelector('[data-detail-history]')
    const transactionViewModal = root.querySelector('[data-transaction-view-modal]')
    const transactionViewCloseButtons = Array.from(root.querySelectorAll('[data-transaction-view-close]'))
    const transactionViewCategory = root.querySelector('[data-transaction-view-category]')
    const transactionViewType = root.querySelector('[data-transaction-view-type]')
    const transactionViewDate = root.querySelector('[data-transaction-view-date]')
    const transactionViewTime = root.querySelector('[data-transaction-view-time]')
    const transactionViewAmount = root.querySelector('[data-transaction-view-amount]')
    const transactionViewAccountRow = root.querySelector('[data-transaction-view-account-row]')
    const transactionViewAccount = root.querySelector('[data-transaction-view-account]')
    const transactionViewDescriptionRow = root.querySelector('[data-transaction-view-description-row]')
    const transactionViewDescription = root.querySelector('[data-transaction-view-description]')
    const transactionViewPhotosRow = root.querySelector('[data-transaction-view-photos-row]')
    const transactionViewPhotos = root.querySelector('[data-transaction-view-photos]')

    const parseDate = (dateKey) => {
        const [year, month, day] = String(dateKey || '').split('-').map(Number)
        return new Date(year, (month || 1) - 1, day || 1, 12, 0, 0, 0)
    }

    const formatMoney = (value) => new Intl.NumberFormat('en-PH', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number(value || 0))

    const formatCompactMoney = (value) => {
        const numericValue = Number(value || 0)

        if (numericValue >= 1000) {
            const compactValue = numericValue / 1000
            const roundedValue = Number.isInteger(compactValue)
                ? String(compactValue)
                : compactValue.toFixed(1).replace(/\.0$/, '')

            return `${roundedValue}k`
        }

        return formatMoney(numericValue)
    }

    const formatCurrency = (value) => `\u20b1${formatMoney(value)}`
    const formatTransactionTypeLabel = (type) => (
        type === 'expense' ? 'Expense' : type === 'savings' ? 'Savings' : 'Income'
    )
    const transactionDisplayPrefix = (type) => (type === 'expense' ? '-' : '+')
    const formatDetailDate = (date) => date.toLocaleDateString('en-US', {
        weekday: 'short',
        month: 'long',
        day: '2-digit',
    })
    const formatWeekRangeLabel = (date) => {
        const start = startOfWeek(date)
        const end = new Date(start)
        end.setDate(start.getDate() + 6)

        const formatPart = (value) => {
            const month = String(value.getMonth() + 1).padStart(2, '0')
            const day = String(value.getDate()).padStart(2, '0')

            return `${month}.${day}`
        }

        return `${formatPart(start)}-${formatPart(end)}`
    }

    const payloadGroups = Array.isArray(payload.transactionGroups) ? payload.transactionGroups : []
    const categoryMetaList = Array.isArray(payload.categoryMeta) ? payload.categoryMeta : []
    const budgetSnapshots = payload.budgetSnapshots && typeof payload.budgetSnapshots === 'object'
        ? payload.budgetSnapshots
        : {}

    const categoryMetaMap = new Map(categoryMetaList.map((category) => [
        category.key,
        category,
    ]))

    const transactions = payloadGroups.flatMap((group) => {
        const groupDate = parseDate(group.dateKey)

        return (group.transactions || []).map((transaction) => ({
            ...transaction,
            amount: Number(transaction.amount || 0),
            date: groupDate,
            dateKey: group.dateKey,
            monthKey: String(group.dateKey || '').slice(0, 7),
            sortDate: groupDate.getTime(),
        }))
    })

    const state = {
        scope: 'month',
        sort: 'newest',
        anchorDate: payload.initialDisplayDate ? parseDate(payload.initialDisplayDate) : new Date(),
        showStats: true,
        selectedCategoryKey: null,
        activeTrendType: 'expense',
        activeCategoryType: 'expense',
    }

    const palette = ['#ff6f61', '#2396f3', '#00f36b', '#f8c646', '#9b5de5', '#f15bb5', '#ff9f43', '#43c6ac']
    const summaryCategoryMeta = {
        income: {
            key: 'income',
            name: 'Income',
            color: '#00d95f',
            iconPath: '/projectassets/icons/notes.svg',
            type: 'income',
        },
        savings: {
            key: 'savings',
            name: 'Savings',
            color: '#2d9af0',
            iconPath: '/projectassets/icons/savings.svg',
            type: 'savings',
        },
    }
    const typePalettes = {
        income: ['#009a44', '#00b050', '#00c957', '#39de80', '#7ce9ad', '#c7f7dc'],
        savings: ['#0d47a1', '#1565c0', '#1e88e5', '#42a5f5', '#90caf9', '#d6ebff'],
    }

    const startOfWeek = (date) => {
        const start = new Date(date)
        const day = start.getDay()
        const diff = day === 0 ? -6 : 1 - day
        start.setDate(start.getDate() + diff)
        start.setHours(0, 0, 0, 0)
        return start
    }

    const getMonthKey = (date) => {
        const year = date.getFullYear()
        const month = String(date.getMonth() + 1).padStart(2, '0')
        return `${year}-${month}`
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

    const formatDateKey = (date) => {
        const year = date.getFullYear()
        const month = String(date.getMonth() + 1).padStart(2, '0')
        const day = String(date.getDate()).padStart(2, '0')

        return `${year}-${month}-${day}`
    }

    const isSameDate = (left, right) => (
        left.getFullYear() === right.getFullYear() &&
        left.getMonth() === right.getMonth() &&
        left.getDate() === right.getDate()
    )

    const closePanels = () => {
        periodPanel?.classList.add('sprout-transactions__period-panel--hidden')
        panelOverlay?.classList.add('sprout-transactions__panel-overlay--hidden')
        sortMenu?.classList.add('sprout-transactions__detail-filter-menu--hidden')
    }

    const closeTransactionView = () => {
        transactionViewModal?.classList.add('sprout-transactions__view-modal--hidden')
    }

    const openTransactionView = (transaction, dateLabel) => {
        if (!transactionViewModal || !transactionViewCategory || !transactionViewType || !transactionViewDate || !transactionViewTime || !transactionViewAmount) {
            return
        }

        transactionViewCategory.textContent = transaction.category || 'Transaction'
        transactionViewType.textContent = formatTransactionTypeLabel(transaction.type)
        transactionViewDate.textContent = dateLabel
        transactionViewTime.textContent = transaction.time || ''
        transactionViewAmount.textContent = `${transactionDisplayPrefix(transaction.type)}${formatCurrency(transaction.amount)}`
        transactionViewAmount.classList.toggle('sprout-transactions__view-value--expense', transaction.type === 'expense')
        transactionViewAmount.classList.toggle('sprout-transactions__view-value--income', transaction.type !== 'expense')

        const accountValue = transaction.account || transaction.accountName || ''
        const descriptionValue = transaction.description || ''

        if (transactionViewAccountRow && transactionViewAccount) {
            transactionViewAccountRow.classList.toggle('sprout-transactions__view-row--hidden', !accountValue)
            transactionViewAccount.textContent = accountValue
        }

        if (transactionViewDescriptionRow && transactionViewDescription) {
            transactionViewDescriptionRow.classList.toggle('sprout-transactions__view-description-block--hidden', !descriptionValue)
            transactionViewDescription.textContent = descriptionValue
        }

        const receiptPhotoUrls = Array.isArray(transaction.receiptPhotoUrls) ? transaction.receiptPhotoUrls : []

        if (transactionViewPhotosRow && transactionViewPhotos) {
            transactionViewPhotosRow.classList.toggle('sprout-transactions__view-photos-block--hidden', receiptPhotoUrls.length === 0)
            transactionViewPhotos.innerHTML = receiptPhotoUrls.map((receiptPhotoUrl, photoIndex) => `
                <a
                    href="${receiptPhotoUrl}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="sprout-transactions__view-photo-link"
                >
                    <img
                        src="${receiptPhotoUrl}"
                        alt="Receipt photo ${photoIndex + 1}"
                        class="sprout-transactions__view-photo-image"
                    >
                </a>
            `).join('')
        }

        transactionViewModal.classList.remove('sprout-transactions__view-modal--hidden')
    }

    const updatePeriodPanelState = () => {
        if (weekPanel) {
            weekPanel.classList.toggle('sprout-transactions__week-panel--hidden', state.scope !== 'week')
        }

        if (monthPanel) {
            monthPanel.classList.toggle('sprout-transactions__month-panel--hidden', state.scope !== 'month')
        }

        if (yearPanel) {
            yearPanel.classList.toggle('sprout-transactions__year-panel--hidden', state.scope !== 'year')
        }

        if (monthYearValue) {
            monthYearValue.textContent = String(state.anchorDate.getFullYear())
        }

        monthOptionButtons.forEach((button) => {
            const isActive = Number(button.dataset.monthOption) === state.anchorDate.getMonth()
            button.classList.toggle('sprout-transactions__month-option--active', isActive)
        })

        if (weekValue) {
            weekValue.textContent = state.anchorDate.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
            })
        }

        if (yearValue) {
            yearValue.textContent = String(state.anchorDate.getFullYear())
        }

        yearMonthButtons.forEach((button) => {
            const isActive = Number(button.dataset.yearMonth) === state.anchorDate.getMonth()
            button.classList.toggle('sprout-transactions__year-month--active', isActive)
        })

        if (weekGrid) {
            const monthStart = new Date(state.anchorDate.getFullYear(), state.anchorDate.getMonth(), 1)
            const startIndex = (monthStart.getDay() + 6) % 7
            const firstVisibleDate = new Date(monthStart)
            firstVisibleDate.setDate(monthStart.getDate() - startIndex)

            const activeWeekStart = startOfWeek(state.anchorDate)
            const activeWeekEnd = new Date(activeWeekStart)
            activeWeekEnd.setDate(activeWeekStart.getDate() + 6)

            weekGrid.innerHTML = Array.from({ length: 42 }, (_, index) => {
                const cellDate = new Date(firstVisibleDate)
                cellDate.setDate(firstVisibleDate.getDate() + index)

                const isCurrentMonth = cellDate.getMonth() === state.anchorDate.getMonth()
                const isInActiveWeek = cellDate >= activeWeekStart && cellDate <= activeWeekEnd
                const isWeekStart = isSameDate(cellDate, activeWeekStart)
                const isWeekEnd = isSameDate(cellDate, activeWeekEnd)

                return `
                    <button
                        type="button"
                        class="sprout-transactions__week-date ${isCurrentMonth ? '' : 'sprout-transactions__week-date--muted'} ${isInActiveWeek ? 'sprout-transactions__week-date--week' : ''} ${isWeekStart ? 'sprout-transactions__week-date--start' : ''} ${isWeekEnd ? 'sprout-transactions__week-date--end' : ''}"
                        data-week-date="${formatDateKey(cellDate)}"
                    >
                        ${cellDate.getDate()}
                    </button>
                `
            }).join('')

            Array.from(weekGrid.querySelectorAll('[data-week-date]')).forEach((button) => {
                button.addEventListener('click', () => {
                    const selectedDate = parseDate(button.getAttribute('data-week-date'))
                    state.anchorDate = selectedDate
                    updatePeriodPanelState()
                    render()
                    closePanels()
                })
            })
        }
    }

    const getVisibleTransactions = () => {
        const { start, end } = getRange()

        return transactions.filter((transaction) => transaction.date >= start && transaction.date <= end)
    }

    const getVisibleExpenseTransactions = () => {
        const { start, end } = getRange()

        return transactions.filter((transaction) => (
            transaction.type === 'expense' &&
            transaction.date >= start &&
            transaction.date <= end
        ))
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
                dateKey: `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`,
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

    const getNiceAxisStep = (maxValue) => {
        if (maxValue <= 1000) {
            return 250
        }

        if (maxValue <= 5000) {
            return 500
        }

        if (maxValue <= 10000) {
            return 1000
        }

        if (maxValue <= 25000) {
            return 2500
        }

        if (maxValue <= 50000) {
            return 5000
        }

        return 10000
    }

    const buildTypeCategories = (type, visibleTransactions) => {
        const meta = summaryCategoryMeta[type]
        const categoryMap = new Map()

        visibleTransactions
            .filter((transaction) => transaction.type === type)
            .forEach((transaction) => {
                const categoryKey = transaction.categoryKey || meta.key

                if (!categoryMap.has(categoryKey)) {
                    categoryMap.set(categoryKey, {
                        key: categoryKey,
                        name: transaction.category || meta.name,
                        amount: 0,
                        chartAmount: 0,
                        count: 0,
                        budgetAmount: 0,
                        iconPath: categoryKey === 'others'
                            ? '/projectassets/icons/others.svg'
                            : (!transaction.iconPath || transaction.iconPath.includes('/others.svg'))
                            ? meta.iconPath
                            : transaction.iconPath,
                        color: typePalettes[type][categoryMap.size % typePalettes[type].length] || meta.color,
                        type,
                        isBudgetTracked: false,
                    })
                }

                const category = categoryMap.get(categoryKey)
                category.amount += transaction.amount
                category.chartAmount = category.amount
                category.count += 1
            })

        return Array.from(categoryMap.values()).sort((left, right) => {
            if (right.amount !== left.amount) {
                return right.amount - left.amount
            }

            return left.name.localeCompare(right.name)
        })
    }

    const buildExpenseCategories = (visibleExpenseTransactions) => {
        const categoryMap = new Map()
        const activeBudgetSnapshot = budgetSnapshots[getMonthKey(state.anchorDate)] || {}

        visibleExpenseTransactions.forEach((transaction) => {
            const categoryKey = transaction.categoryKey || 'others'
            const meta = categoryMetaMap.get(categoryKey) || {}

            if (!categoryMap.has(categoryKey)) {
                categoryMap.set(categoryKey, {
                    key: categoryKey,
                    name: meta.name || transaction.category || 'Others',
                    amount: 0,
                    chartAmount: 0,
                    count: 0,
                    budgetAmount: Number(activeBudgetSnapshot[categoryKey]?.allocatedAmount || 0),
                    iconPath: meta.iconPath || transaction.iconPath || '/projectassets/icons/others.svg',
                    color: meta.color || palette[categoryMap.size % palette.length],
                    type: 'expense',
                    isBudgetTracked: true,
                })
            }

            const category = categoryMap.get(categoryKey)
            category.amount += transaction.amount
            category.chartAmount = category.amount
            category.count += 1
        })

        return Array.from(categoryMap.values()).sort((left, right) => {
            if (right.amount !== left.amount) {
                return right.amount - left.amount
            }

            return right.budgetAmount - left.budgetAmount
        })
    }

    const buildCategories = (visibleTransactions) => {
        const incomeCategories = buildTypeCategories('income', visibleTransactions)
        const expenseCategories = buildExpenseCategories(
            visibleTransactions.filter((transaction) => transaction.type === 'expense')
        )
        const savingsCategories = buildTypeCategories('savings', visibleTransactions)

        return [
            ...incomeCategories,
            ...expenseCategories,
            ...savingsCategories,
        ]
    }

    const sortDetailRows = (rows) => {
        return [...rows].sort((left, right) => {
            if (state.sort === 'oldest') {
                return left.sortDate - right.sortDate
            }

            if (state.sort === 'highest') {
                return right.amount - left.amount || right.sortDate - left.sortDate
            }

            if (state.sort === 'lowest') {
                return left.amount - right.amount || right.sortDate - left.sortDate
            }

            return right.sortDate - left.sortDate
        })
    }

    const buildDetailRows = (category) => {
        if (category.type === 'income' || category.type === 'savings') {
            return sortDetailRows(
                getVisibleTransactions().filter((transaction) => (
                    transaction.type === category.type &&
                    (transaction.categoryKey || category.type) === category.key
                ))
            )
        }

        return sortDetailRows(
            getVisibleExpenseTransactions()
                .filter((transaction) => (transaction.categoryKey || 'others') === category.key)
        )
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
                    display: false,
                },
                tooltip: {
                    backgroundColor: '#ffffff',
                    titleColor: '#1f1f1f',
                    bodyColor: '#1f1f1f',
                    borderColor: 'rgba(0, 0, 0, 0.08)',
                    borderWidth: 1,
                    displayColors: true,
                    callbacks: {
                        label(context) {
                            return `${context.dataset.label}: ${formatCurrency(context.parsed.y)}`
                        },
                    },
                },
            },
            layout: {
                padding: {
                    top: 10,
                    right: 8,
                    bottom: 0,
                    left: 2,
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grace: '14%',
                    ticks: {
                        callback: (value) => `\u20b1${formatCompactMoney(value)}`,
                        stepSize: 500,
                        maxTicksLimit: 6,
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
                        display: true,
                        color: 'rgba(120, 128, 120, 0.10)',
                        lineWidth: 0.8,
                        drawBorder: false,
                        drawTicks: false,
                    },
                },
                x: {
                    ticks: {
                        color: '#666666',
                        maxRotation: 0,
                        autoSkip: true,
                        maxTicksLimit: 11,
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
                        display: true,
                        color: 'rgba(120, 128, 120, 0.07)',
                        lineWidth: 0.8,
                        drawBorder: false,
                        drawTicks: false,
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
                    hoverRadius: 4.5,
                    borderWidth: 1.5,
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
                borderWidth: 1.5,
                borderColor: '#ffffff',
            }],
        },
        options: {
            maintainAspectRatio: false,
            cutout: '58%',
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    callbacks: {
                        label(context) {
                            return `${context.label}: ${formatCurrency(context.parsed)}`
                        },
                    },
                },
            },
        },
    })

    const detailChart = new Chart(document.getElementById('transactions-detail-chart'), {
        type: 'doughnut',
        data: {
            labels: ['Spent', 'Remaining'],
            datasets: [{
                data: [0, 1],
                backgroundColor: ['#ff6f61', '#ececec'],
                borderWidth: 0,
            }],
        },
        options: {
            maintainAspectRatio: false,
            cutout: '66%',
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    callbacks: {
                        label(context) {
                            return `${context.label}: ${formatCurrency(context.parsed)}`
                        },
                    },
                },
            },
        },
    })

    const hidePiePopup = () => {
        piePopup?.classList.add('sprout-transactions__pie-popup--hidden')
    }

    const showPiePopup = (category) => {
        if (!piePopup || !piePopupName || !piePopupAmount || !category) {
            return
        }

        piePopupName.textContent = category.name
        piePopupAmount.textContent = formatCurrency(category.amount)
        piePopupAmount.style.color = category.color
        piePopup.classList.remove('sprout-transactions__pie-popup--hidden')
    }

    const renderCategoryArea = (categories) => {
        if (categories.length === 0) {
            categoryEmpty?.classList.remove('sprout-transactions__category-empty--hidden')
            root.querySelector('.sprout-transactions__pie-wrap')?.classList.add('sprout-transactions__pie-wrap--hidden')
            legend.innerHTML = ''
            hidePiePopup()
            stats.innerHTML = '<div class="sprout-transactions__empty">No data available.</div>'
            return
        }

        categoryEmpty?.classList.add('sprout-transactions__category-empty--hidden')
        root.querySelector('.sprout-transactions__pie-wrap')?.classList.remove('sprout-transactions__pie-wrap--hidden')
        hidePiePopup()
        legend.innerHTML = categories.slice(0, 4).map((category) => `
            <div class="sprout-transactions__legend-item">
                <span class="sprout-transactions__legend-dot" style="background:${category.color};"></span>
                <span class="sprout-transactions__legend-text">${category.name}</span>
            </div>
        `).join('')

        const highestAmount = Math.max(...categories.map((category) => category.amount), 1)
        stats.innerHTML = categories.map((category) => `
            <button type="button" class="sprout-transactions__stat-row" data-category-key="${category.key}">
                <div class="sprout-transactions__stat-main">
                    <div class="sprout-transactions__stat-icon" style="background:${category.color};">
                        <img src="${category.iconPath}" alt="${category.name}">
                    </div>

                    <div class="sprout-transactions__stat-copy">
                        <div class="sprout-transactions__stat-heading">
                            <span class="sprout-transactions__stat-name">${category.name}</span>
                            <span class="sprout-transactions__stat-amount">${formatCurrency(category.amount)}</span>
                        </div>

                        <div class="sprout-transactions__stat-bar">
                            <span class="sprout-transactions__stat-bar-fill" style="background:${category.color}; width:${Math.max((category.amount / highestAmount) * 100, category.amount > 0 ? 10 : 0)}%;"></span>
                        </div>

                        <div class="sprout-transactions__stat-meta">
                            <span>${category.count} tx</span>
                            <span>${category.type === 'expense' ? 'Expense activity' : 'Dashboard data'}</span>
                        </div>
                    </div>
                </div>

                <span class="sprout-transactions__stat-chevron">&#8250;</span>
            </button>
        `).join('')

        Array.from(stats.querySelectorAll('[data-category-key]')).forEach((row) => {
            row.addEventListener('click', () => {
                state.selectedCategoryKey = row.getAttribute('data-category-key')
                renderDetailView(categories)
            })
        })
    }

    const renderLineLegend = (datasets) => {
        if (!lineLegend) {
            return
        }

        const totals = {
            expense: datasets.find((dataset) => dataset.key === 'expense')?.total ?? 0,
            income: datasets.find((dataset) => dataset.key === 'income')?.total ?? 0,
            savings: datasets.find((dataset) => dataset.key === 'savings')?.total ?? 0,
        }

        const items = [
            { key: 'expense', label: 'Expense', color: '#ff8a80', amount: totals.expense },
            { key: 'income', label: 'Income', color: '#00e676', amount: totals.income },
            { key: 'savings', label: 'Savings', color: '#2196f3', amount: totals.savings },
        ]

        lineLegend.innerHTML = items.map((item) => `
            <button
                type="button"
                class="sprout-transactions__trend-filter ${state.activeTrendType === item.key ? 'sprout-transactions__trend-filter--active' : ''}"
                data-trend-filter="${item.key}"
                style="--trend-filter-color:${item.color};"
            >
                <span class="sprout-transactions__trend-filter-label">${item.label}</span>
                <span class="sprout-transactions__trend-filter-value">${formatCurrency(item.amount)}</span>
            </button>
        `).join('')

        Array.from(lineLegend.querySelectorAll('[data-trend-filter]')).forEach((button) => {
            button.addEventListener('click', () => {
                state.activeTrendType = button.getAttribute('data-trend-filter') || 'expense'
                render()
            })
        })
    }

    const renderCategoryFilters = (allCategories) => {
        if (!categoryFilters) {
            return
        }

        const totals = {
            expense: allCategories
                .filter((category) => category.type === 'expense')
                .reduce((sum, category) => sum + category.amount, 0),
            income: allCategories
                .filter((category) => category.type === 'income')
                .reduce((sum, category) => sum + category.amount, 0),
            savings: allCategories
                .filter((category) => category.type === 'savings')
                .reduce((sum, category) => sum + category.amount, 0),
        }

        const items = [
            { key: 'expense', label: 'Expense', color: '#ff8a80', amount: totals.expense },
            { key: 'income', label: 'Income', color: '#00e676', amount: totals.income },
            { key: 'savings', label: 'Savings', color: '#2196f3', amount: totals.savings },
        ]

        categoryFilters.innerHTML = items.map((item) => `
            <button
                type="button"
                class="sprout-transactions__trend-filter ${state.activeCategoryType === item.key ? 'sprout-transactions__trend-filter--active' : ''}"
                data-category-filter="${item.key}"
                style="--trend-filter-color:${item.color};"
            >
                <span class="sprout-transactions__trend-filter-label">${item.label}</span>
                <span class="sprout-transactions__trend-filter-value">${formatCurrency(item.amount)}</span>
            </button>
        `).join('')

        Array.from(categoryFilters.querySelectorAll('[data-category-filter]')).forEach((button) => {
            button.addEventListener('click', () => {
                state.activeCategoryType = button.getAttribute('data-category-filter') || 'expense'
                state.selectedCategoryKey = null
                render()
            })
        })
    }

    const renderDetailView = (categories) => {
        const category = categories.find((item) => item.key === state.selectedCategoryKey)

        if (!category) {
            return
        }

        const detailRows = buildDetailRows(category)
        const spentAmount = detailRows.reduce((sum, transaction) => sum + transaction.amount, 0)
        const budgetAmount = Number(category.budgetAmount || 0)
        const remainingAmount = Math.max(budgetAmount - spentAmount, 0)
        const exceededAmount = Math.max(spentAmount - budgetAmount, 0)

        summaryView?.classList.add('sprout-transactions__summary-view--hidden')
        detailView?.classList.remove('sprout-transactions__detail-view--hidden')
        closePanels()

        detailTitle.textContent = category.name
        detailBudgetCard?.classList.toggle('sprout-transactions__detail-card--hidden', category.type !== 'expense')
        detailCardTitle.textContent = category.type === 'expense'
            ? `${category.name} Budget`
            : `${category.name} Overview`
        detailCalloutName.textContent = category.name
        detailSpentCallout.textContent = formatCurrency(spentAmount)
        detailSpentCallout.style.color = category.color
        if (detailSpentDot) {
            detailSpentDot.style.background = category.color
        }
        detailBudgetCallout.textContent = formatCurrency(budgetAmount)
        detailBudgetInline.textContent = formatCurrency(budgetAmount)
        detailOutInline.textContent = formatCurrency(spentAmount)
        if (detailExceedPill && detailExceedPillAmount) {
            const showExceeded = category.type === 'expense' && exceededAmount > 0
            detailExceedPill.classList.toggle('sprout-transactions__detail-exceed-pill--hidden', !showExceeded)
            detailExceedPillAmount.textContent = formatCurrency(exceededAmount)
        }

        detailChart.data.datasets[0].data = budgetAmount > 0
            ? [spentAmount, remainingAmount]
            : [spentAmount || 1, spentAmount ? 0 : 1]
        detailChart.data.datasets[0].backgroundColor = [category.color, '#ededed']
        detailChart.update()

        if (detailRows.length === 0) {
            detailHistory.innerHTML = '<div class="sprout-transactions__empty sprout-transactions__empty--detail">No transactions for this category in the selected period.</div>'
            return
        }

        detailHistory.innerHTML = detailRows.map((transaction, index) => `
            <button type="button" class="sprout-transactions__history-card sprout-transactions__history-card--button" data-detail-transaction-index="${index}">
                <div class="sprout-transactions__history-date-row">
                    <span class="sprout-transactions__history-date">${formatDetailDate(transaction.date)}</span>
                    <span class="sprout-transactions__history-out ${transaction.type === 'expense' ? 'sprout-transactions__history-out--expense' : 'sprout-transactions__history-out--income'}">${transaction.type === 'expense' ? 'OUT' : 'IN'} ${formatCurrency(transaction.amount)}</span>
                </div>

                <div class="sprout-transactions__history-item">
                    <div class="sprout-transactions__history-icon" style="background:${category.color};">
                        <img src="${category.iconPath || transaction.iconPath}" alt="${transaction.category}">
                    </div>

                    <div class="sprout-transactions__history-copy">
                        <span class="sprout-transactions__history-name">${transaction.category}</span>
                        ${transaction.description ? `<span class="sprout-transactions__history-description">Desc: ${transaction.description}</span>` : ''}
                    </div>

                    <div class="sprout-transactions__history-amount-wrap">
                        <span class="sprout-transactions__history-amount ${transaction.type === 'expense' ? 'sprout-transactions__history-amount--expense' : 'sprout-transactions__history-amount--income'}">${transaction.type === 'expense' ? '-' : '+'}${formatCurrency(transaction.amount)}</span>
                        <span class="sprout-transactions__history-timestamp">${transaction.time}</span>
                    </div>
                </div>
            </button>
        `).join('')

        Array.from(detailHistory.querySelectorAll('[data-detail-transaction-index]')).forEach((button) => {
            button.addEventListener('click', () => {
                const index = Number(button.getAttribute('data-detail-transaction-index') || 0)
                const transaction = detailRows[index]

                if (!transaction) {
                    return
                }

                openTransactionView(transaction, formatDetailDate(transaction.date))
            })
        })
    }

    const render = () => {
        const visibleTransactions = getVisibleTransactions()
        const trend = buildTrend(visibleTransactions)
        const allCategories = buildCategories(visibleTransactions)
        const categories = allCategories.filter((category) => category.type === state.activeCategoryType)
        const datasetConfigs = []
        const trendMaxValue = Math.max(
            ...trend.expense,
            ...trend.income,
            ...trend.savings,
            0
        )
        const yStepSize = getNiceAxisStep(trendMaxValue)
        const roundedMax = Math.ceil((trendMaxValue || yStepSize) / yStepSize) * yStepSize
        const yAxisMax = Math.max(yStepSize * 2, roundedMax + yStepSize)
        const pointRadius = state.scope === 'month' ? 2.5 : 4
        const pointHoverRadius = state.scope === 'month' ? 3 : 4.5

        datasetConfigs.push({
            key: 'expense',
            label: 'Expense',
            total: trend.expense.reduce((sum, value) => sum + value, 0),
            config: {
                label: 'Expense',
                data: trend.expense,
                clip: false,
                borderColor: 'rgba(255, 138, 128, 0.72)',
                backgroundColor: 'rgba(255, 138, 128, 0.72)',
                pointBorderColor: '#ff8a80',
                pointBackgroundColor: '#ffffff',
                pointHoverBackgroundColor: '#ffffff',
                pointHoverBorderColor: '#ff8a80',
                pointRadius: (context) => Number(context.raw || 0) > 0 ? pointRadius : 0,
                pointHoverRadius: (context) => Number(context.raw || 0) > 0 ? pointHoverRadius : 0,
            },
        })

        datasetConfigs.push({
            key: 'income',
            label: 'Income',
            total: trend.income.reduce((sum, value) => sum + value, 0),
            config: {
                label: 'Income',
                data: trend.income,
                clip: false,
                borderColor: 'rgba(0, 230, 118, 0.72)',
                backgroundColor: 'rgba(0, 230, 118, 0.72)',
                pointBorderColor: '#00e676',
                pointBackgroundColor: '#ffffff',
                pointHoverBackgroundColor: '#ffffff',
                pointHoverBorderColor: '#00e676',
                pointRadius: (context) => Number(context.raw || 0) > 0 ? pointRadius : 0,
                pointHoverRadius: (context) => Number(context.raw || 0) > 0 ? pointHoverRadius : 0,
            },
        })

        datasetConfigs.push({
            key: 'savings',
            label: 'Savings',
            total: trend.savings.reduce((sum, value) => sum + value, 0),
            config: {
                label: 'Savings',
                data: trend.savings,
                clip: false,
                borderColor: 'rgba(33, 150, 243, 0.72)',
                backgroundColor: 'rgba(33, 150, 243, 0.72)',
                pointBorderColor: '#2196f3',
                pointBackgroundColor: '#ffffff',
                pointHoverBackgroundColor: '#ffffff',
                pointHoverBorderColor: '#2196f3',
                pointRadius: (context) => Number(context.raw || 0) > 0 ? pointRadius : 0,
                pointHoverRadius: (context) => Number(context.raw || 0) > 0 ? pointHoverRadius : 0,
            },
        })

        const activeDataset = datasetConfigs.find((dataset) => dataset.key === state.activeTrendType) || datasetConfigs[0]
        const datasets = activeDataset ? [activeDataset.config] : []

        lineChart.options.scales.y.max = yAxisMax
        lineChart.options.scales.y.ticks.stepSize = yStepSize
        lineChart.data.labels = trend.labels
        lineChart.data.datasets = datasets
        lineChart.update()
        renderLineLegend(datasetConfigs)
        renderCategoryFilters(allCategories)

        pieChart.data.labels = categories.map((category) => category.name)
        pieChart.data.datasets[0].data = categories.length > 0
            ? categories.map((category) => category.chartAmount > 0 ? category.chartAmount : 0.00001)
            : []
        pieChart.data.datasets[0].backgroundColor = categories.map((category) => category.color)
        pieChart.data.datasets[0].borderWidth = categories.length > 1 ? 1.5 : 0
        pieChart.data.datasets[0].borderColor = '#ffffff'
        pieChart.update()

        renderCategoryArea(categories)

        if (state.scope === 'year') {
            periodLabel.textContent = state.anchorDate.toLocaleDateString('en-US', {
                year: 'numeric',
            })
        } else if (state.scope === 'month') {
            periodLabel.textContent = state.anchorDate.toLocaleDateString('en-US', {
                month: 'long',
                year: 'numeric',
            })
        } else {
            periodLabel.textContent = formatWeekRangeLabel(state.anchorDate)
        }

        if (state.selectedCategoryKey) {
            if (categories.some((category) => category.key === state.selectedCategoryKey)) {
                renderDetailView(categories)
            } else {
                state.selectedCategoryKey = null
                detailView?.classList.add('sprout-transactions__detail-view--hidden')
                summaryView?.classList.remove('sprout-transactions__summary-view--hidden')
            }
        }
    }

    scopeTabs.forEach((button) => {
        button.addEventListener('click', () => {
            state.scope = button.dataset.scope || 'week'
            scopeTabs.forEach((item) => item.classList.remove('sprout-transactions__period-tab--active'))
            button.classList.add('sprout-transactions__period-tab--active')
            updatePeriodPanelState()
            render()
        })
    })

    yearShiftButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const shift = Number(button.dataset.yearShift || 0)
            state.anchorDate = new Date(state.anchorDate.getFullYear() + shift, state.anchorDate.getMonth(), 1)
            updatePeriodPanelState()
            render()
        })
    })

    monthYearShiftButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const shift = Number(button.dataset.monthYearShift || 0)
            state.anchorDate = new Date(state.anchorDate.getFullYear() + shift, state.anchorDate.getMonth(), 1)
            updatePeriodPanelState()
            render()
        })
    })

    weekMonthShiftButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const shift = Number(button.dataset.weekMonthShift || 0)
            state.anchorDate = new Date(state.anchorDate.getFullYear(), state.anchorDate.getMonth() + shift, 1)
            updatePeriodPanelState()
            render()
        })
    })

    yearMonthButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const monthIndex = Number(button.dataset.yearMonth || 0)
            state.anchorDate = new Date(state.anchorDate.getFullYear(), monthIndex, 1)
            updatePeriodPanelState()
            render()
            closePanels()
        })
    })

    monthOptionButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const monthIndex = Number(button.dataset.monthOption || 0)
            state.anchorDate = new Date(state.anchorDate.getFullYear(), monthIndex, 1)
            updatePeriodPanelState()
            render()
            closePanels()
        })
    })

    sortTabs.forEach((button) => {
        button.addEventListener('click', () => {
            state.sort = button.dataset.sort || 'newest'
            sortTabs.forEach((item) => item.classList.remove('sprout-transactions__detail-filter-option--active'))
            button.classList.add('sprout-transactions__detail-filter-option--active')
            closePanels()
            render()
        })
    })

    periodTrigger?.addEventListener('click', (event) => {
        event.stopPropagation()
        const isHidden = periodPanel.classList.contains('sprout-transactions__period-panel--hidden')
        closePanels()

        if (isHidden) {
            updatePeriodPanelState()
            panelOverlay?.classList.remove('sprout-transactions__panel-overlay--hidden')
            periodPanel.classList.remove('sprout-transactions__period-panel--hidden')
        }
    })

    sortTrigger?.addEventListener('click', (event) => {
        event.stopPropagation()
        const isHidden = sortMenu.classList.contains('sprout-transactions__detail-filter-menu--hidden')
        closePanels()

        if (isHidden) {
            sortMenu.classList.remove('sprout-transactions__detail-filter-menu--hidden')
        }
    })

    showToggle?.addEventListener('click', () => {
        state.showStats = !state.showStats
        statsPanel?.classList.toggle('sprout-transactions__stats--hidden', !state.showStats)
        showToggle?.setAttribute('aria-expanded', state.showStats ? 'true' : 'false')
        showToggleText.textContent = state.showStats ? 'show less' : 'show more'
    })

    pieCanvas?.addEventListener('click', (event) => {
        const points = pieChart.getElementsAtEventForMode(event, 'nearest', { intersect: true }, true)

        if (!points.length) {
            hidePiePopup()
            return
        }

        const visibleTransactions = getVisibleTransactions()
        const allCategories = buildCategories(visibleTransactions)
        const visibleCategories = allCategories.filter((category) => category.type === state.activeCategoryType)
        const category = visibleCategories[points[0].index]

        showPiePopup(category)
    })

    document.addEventListener('click', (event) => {
        if (event.target === pieCanvas) {
            return
        }

        hidePiePopup()
    })

    detailClose?.addEventListener('click', () => {
        state.selectedCategoryKey = null
        detailView?.classList.add('sprout-transactions__detail-view--hidden')
        summaryView?.classList.remove('sprout-transactions__summary-view--hidden')
        closePanels()
    })

    transactionViewCloseButtons.forEach((button) => {
        button.addEventListener('click', closeTransactionView)
    })

    document.addEventListener('click', (event) => {
        if (!root.contains(event.target)) {
            closePanels()
            return
        }

        const isPeriodPanelOpen = periodPanel && !periodPanel.classList.contains('sprout-transactions__period-panel--hidden')

        if (
            isPeriodPanelOpen &&
            periodPanel &&
            periodTrigger &&
            !periodPanel.contains(event.target) &&
            !periodTrigger.contains(event.target)
        ) {
            closePanels()
            return
        }

        if (periodPanel && periodTrigger && !periodPanel.contains(event.target) && !periodTrigger.contains(event.target)) {
            periodPanel.classList.add('sprout-transactions__period-panel--hidden')
        }

        if (sortMenu && sortTrigger && !sortMenu.contains(event.target) && !sortTrigger.contains(event.target)) {
            sortMenu.classList.add('sprout-transactions__detail-filter-menu--hidden')
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

    panelOverlay?.addEventListener('click', () => {
        closePanels()
    })

    updatePeriodPanelState()
    render()
})
</script>


