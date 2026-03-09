<template>
  <!-- Dashboard Root -->
  <div class="sprout-dashboard-mobile">
    <!-- Top Bar -->
    <header class="sprout-dashboard-mobile__topbar">
      <button
        type="button"
        class="sprout-dashboard-mobile__filter"
        @click="toggleFilterMenu"
      >
        {{ selectedFilter }}
        <span class="sprout-dashboard-mobile__filter-caret">▼</span>
      </button>

      <div class="sprout-dashboard-mobile__period">
        <button
          type="button"
          class="sprout-dashboard-mobile__period-arrow"
          @click="goPreviousPeriod"
          aria-label="Previous period"
        >
          ‹
        </button>

        <button
          type="button"
          class="sprout-dashboard-mobile__period-trigger"
          @click="togglePeriodMenu"
        >
          {{ currentPeriodLabel }}
        </button>

        <button
          type="button"
          class="sprout-dashboard-mobile__period-arrow"
          @click="goNextPeriod"
          aria-label="Next period"
        >
          ›
        </button>
      </div>

      <div class="sprout-dashboard-mobile__topbar-space"></div>
    </header>

    <!-- Filter Dropdown -->
    <div
      v-if="isFilterMenuVisible"
      class="sprout-dashboard-mobile__dropdown sprout-dashboard-mobile__dropdown--filter"
    >
      <button
        v-for="filterOption in filterOptions"
        :key="filterOption"
        type="button"
        class="sprout-dashboard-mobile__dropdown-item"
        :class="{
          'sprout-dashboard-mobile__dropdown-item--active': selectedFilter === filterOption
        }"
        @click="selectFilter(filterOption)"
      >
        {{ filterOption }}
      </button>
    </div>

    <!-- Period Panel -->
    <section
      v-if="isPeriodMenuVisible"
      class="sprout-dashboard-mobile__period-panel"
    >
      <!-- Period Tabs -->
      <div class="sprout-dashboard-mobile__period-tabs">
        <button
          type="button"
          class="sprout-dashboard-mobile__period-tab"
          :class="{
            'sprout-dashboard-mobile__period-tab--active': selectedPeriodView === 'week'
          }"
          @click="setPeriodView('week')"
        >
          Week
        </button>

        <button
          type="button"
          class="sprout-dashboard-mobile__period-tab"
          :class="{
            'sprout-dashboard-mobile__period-tab--active': selectedPeriodView === 'month'
          }"
          @click="setPeriodView('month')"
        >
          Month
        </button>

        <button
          type="button"
          class="sprout-dashboard-mobile__period-tab"
          :class="{
            'sprout-dashboard-mobile__period-tab--active': selectedPeriodView === 'year'
          }"
          @click="setPeriodView('year')"
        >
          Year
        </button>
      </div>

      <!-- Week Options -->
      <div
        v-if="selectedPeriodView === 'week'"
        class="sprout-dashboard-mobile__week-panel"
      >
        <div class="sprout-dashboard-mobile__week-strip">
          <button
            v-for="weekCell in weekCalendarCells"
            :key="weekCell.key"
            type="button"
            class="sprout-dashboard-mobile__week-option"
            :class="{
              'sprout-dashboard-mobile__week-option--active': weekCell.isSelected
            }"
            @click="selectDate(weekCell.date)"
          >
            <span class="sprout-dashboard-mobile__week-option-label">
              {{ weekCell.weekdayShort }}
            </span>

            <span class="sprout-dashboard-mobile__week-option-number">
              {{ weekCell.day }}
            </span>
          </button>
        </div>
      </div>

      <!-- Month Options -->
      <div
        v-if="selectedPeriodView === 'month'"
        class="sprout-dashboard-mobile__month-panel"
      >
        <div class="sprout-dashboard-mobile__month-selector">
          <button
            v-for="monthOption in monthOptions"
            :key="monthOption.value"
            type="button"
            class="sprout-dashboard-mobile__month-option"
            :class="{
              'sprout-dashboard-mobile__month-option--active':
                monthOption.value === currentDisplayDate.getMonth()
            }"
            @click="selectMonth(monthOption.value)"
          >
            {{ monthOption.label }}
          </button>
        </div>
      </div>

      <!-- Year Options -->
      <div
        v-if="selectedPeriodView === 'year'"
        class="sprout-dashboard-mobile__year-panel"
      >
        <div class="sprout-dashboard-mobile__year-switcher">
          <button
            type="button"
            class="sprout-dashboard-mobile__year-arrow"
            @click="displayYear -= 1"
            aria-label="Previous year"
          >
            ‹
          </button>

          <div class="sprout-dashboard-mobile__year-value">
            {{ displayYear }}
          </div>

          <button
            type="button"
            class="sprout-dashboard-mobile__year-arrow"
            @click="displayYear += 1"
            aria-label="Next year"
          >
            ›
          </button>
        </div>

        <div class="sprout-dashboard-mobile__year-months">
          <button
            v-for="monthOption in monthOptions"
            :key="monthOption.value"
            type="button"
            class="sprout-dashboard-mobile__year-month"
            :class="{
              'sprout-dashboard-mobile__year-month--active':
                monthOption.value === currentDisplayDate.getMonth()
            }"
            @click="selectMonthFromYear(monthOption.value)"
          >
            {{ monthOption.label }}
          </button>
        </div>
      </div>
    </section>

    <!-- Calendar Card -->
    <section class="sprout-dashboard-mobile__calendar-card">
      <!-- Calendar Heading -->
      <div class="sprout-dashboard-mobile__calendar-heading">
        {{ calendarHeading }}
      </div>

      <!-- Weekday Labels -->
      <div
        v-if="selectedPeriodView !== 'year'"
        class="sprout-dashboard-mobile__weekday-row"
      >
        <span
          v-for="weekdayLabel in weekdayLabels"
          :key="weekdayLabel"
          class="sprout-dashboard-mobile__weekday"
        >
          {{ weekdayLabel }}
        </span>
      </div>

      <!-- Month View -->
      <div
        v-if="selectedPeriodView === 'month'"
        class="sprout-dashboard-mobile__month-grid"
      >
        <button
          v-for="calendarCell in monthCalendarCells"
          :key="calendarCell.key"
          type="button"
          class="sprout-dashboard-mobile__day-cell"
          :class="{
            'sprout-dashboard-mobile__day-cell--muted': !calendarCell.isCurrentMonth,
            'sprout-dashboard-mobile__day-cell--selected': calendarCell.isSelected
          }"
          @click="selectDate(calendarCell.date)"
        >
          <span class="sprout-dashboard-mobile__day-number">
            {{ calendarCell.day }}
          </span>

          <div
            v-if="calendarCell.isSelected && (calendarCell.dailyIncome > 0 || calendarCell.dailyExpense > 0)"
            class="sprout-dashboard-mobile__day-legends"
          >
            <span
              v-if="calendarCell.dailyIncome > 0"
              class="sprout-dashboard-mobile__day-amount sprout-dashboard-mobile__day-amount--income"
            >
              ₱{{ formatCompactAmount(calendarCell.dailyIncome) }}
            </span>

            <span
              v-if="calendarCell.dailyExpense > 0"
              class="sprout-dashboard-mobile__day-amount sprout-dashboard-mobile__day-amount--expense"
            >
              ₱{{ formatCompactAmount(calendarCell.dailyExpense) }}
            </span>
          </div>
        </button>
      </div>

      <!-- Week View -->
      <div
        v-else-if="selectedPeriodView === 'week'"
        class="sprout-dashboard-mobile__week-grid"
      >
        <button
          v-for="weekCell in weekCalendarCells"
          :key="weekCell.key"
          type="button"
          class="sprout-dashboard-mobile__day-cell sprout-dashboard-mobile__day-cell--week"
          :class="{
            'sprout-dashboard-mobile__day-cell--selected': weekCell.isSelected
          }"
          @click="selectDate(weekCell.date)"
        >
          <span class="sprout-dashboard-mobile__week-cell-label">
            {{ weekCell.weekdayShort }}
          </span>

          <span class="sprout-dashboard-mobile__day-number">
            {{ weekCell.day }}
          </span>

          <div
            v-if="weekCell.dailyIncome > 0 || weekCell.dailyExpense > 0"
            class="sprout-dashboard-mobile__day-legends"
          >
            <span
              v-if="weekCell.dailyIncome > 0"
              class="sprout-dashboard-mobile__day-amount sprout-dashboard-mobile__day-amount--income"
            >
              ₱{{ formatCompactAmount(weekCell.dailyIncome) }}
            </span>

            <span
              v-if="weekCell.dailyExpense > 0"
              class="sprout-dashboard-mobile__day-amount sprout-dashboard-mobile__day-amount--expense"
            >
              ₱{{ formatCompactAmount(weekCell.dailyExpense) }}
            </span>
          </div>
        </button>
      </div>

      <!-- Year View -->
      <div
        v-else
        class="sprout-dashboard-mobile__year-summary-grid"
      >
        <button
          v-for="yearSummaryItem in yearMonthSummaries"
          :key="yearSummaryItem.monthIndex"
          type="button"
          class="sprout-dashboard-mobile__year-summary-item"
          :class="{
            'sprout-dashboard-mobile__year-summary-item--active':
              yearSummaryItem.monthIndex === currentDisplayDate.getMonth()
          }"
          @click="selectMonthFromYear(yearSummaryItem.monthIndex)"
        >
          <span class="sprout-dashboard-mobile__year-summary-label">
            {{ yearSummaryItem.label }}
          </span>

          <span
            v-if="yearSummaryItem.income > 0"
            class="sprout-dashboard-mobile__year-summary-income"
          >
            ₱{{ formatCompactAmount(yearSummaryItem.income) }}
          </span>

          <span
            v-if="yearSummaryItem.expense > 0"
            class="sprout-dashboard-mobile__year-summary-expense"
          >
            ₱{{ formatCompactAmount(yearSummaryItem.expense) }}
          </span>
        </button>
      </div>

      <!-- Summary Row -->
      <div class="sprout-dashboard-mobile__summary-row">
        <div class="sprout-dashboard-mobile__summary-item sprout-dashboard-mobile__summary-item--left">
          <div class="sprout-dashboard-mobile__summary-label">Income</div>
          <div class="sprout-dashboard-mobile__summary-value sprout-dashboard-mobile__summary-value--income">
            ₱{{ formatMoney(periodSummary.income) }}
          </div>
        </div>

        <div class="sprout-dashboard-mobile__summary-item sprout-dashboard-mobile__summary-item--center">
          <div class="sprout-dashboard-mobile__summary-label">Expense</div>
          <div class="sprout-dashboard-mobile__summary-value sprout-dashboard-mobile__summary-value--expense">
            ₱{{ formatMoney(periodSummary.expense) }}
          </div>
        </div>

        <div class="sprout-dashboard-mobile__summary-item sprout-dashboard-mobile__summary-item--right">
          <div class="sprout-dashboard-mobile__summary-label">Balance</div>
          <div class="sprout-dashboard-mobile__summary-value sprout-dashboard-mobile__summary-value--balance">
            ₱{{ formatMoney(periodSummary.balance) }}
          </div>
        </div>
      </div>
    </section>

    <!-- Transaction History -->
    <section class="sprout-dashboard-mobile__history-list">
      <article
        v-for="transactionGroup in filteredTransactionGroups"
        :key="transactionGroup.dateKey"
        class="sprout-dashboard-mobile__history-card"
      >
        <!-- Group Header -->
        <div class="sprout-dashboard-mobile__history-header">
          <div class="sprout-dashboard-mobile__history-date">
            {{ transactionGroup.dateLabel }}
          </div>

          <div class="sprout-dashboard-mobile__history-totals">
            <span
              v-if="transactionGroup.income > 0"
              class="sprout-dashboard-mobile__history-total sprout-dashboard-mobile__history-total--income"
            >
              IN ₱{{ formatMoney(transactionGroup.income) }}
            </span>

            <span
              v-if="transactionGroup.expense > 0"
              class="sprout-dashboard-mobile__history-total sprout-dashboard-mobile__history-total--expense"
            >
              OUT ₱{{ formatMoney(transactionGroup.expense) }}
            </span>
          </div>
        </div>

        <!-- Group Transactions -->
        <div
          v-for="transactionItem in transactionGroup.transactions"
          :key="transactionItem.id"
          class="sprout-dashboard-mobile__transaction-row"
        >
          <div class="sprout-dashboard-mobile__transaction-left">
            <!-- Transaction Icon -->
            <div
              class="sprout-dashboard-mobile__transaction-icon"
              :class="transactionIconClass(transactionItem.iconColor)"
            >
              <img
                :src="transactionItem.iconPath"
                :alt="transactionItem.category"
                class="sprout-dashboard-mobile__transaction-icon-image"
              >
            </div>

            <div class="sprout-dashboard-mobile__transaction-text">
              <div class="sprout-dashboard-mobile__transaction-category">
                {{ transactionItem.category }}
              </div>

              <div
                v-if="transactionItem.description"
                class="sprout-dashboard-mobile__transaction-description"
              >
                Desc: {{ transactionItem.description }}
              </div>
            </div>
          </div>

          <div class="sprout-dashboard-mobile__transaction-right">
            <div
              class="sprout-dashboard-mobile__transaction-amount"
              :class="transactionAmountClass(transactionItem.type)"
            >
              {{ transactionItem.type === 'expense' ? '-' : '+' }}₱{{ formatMoney(transactionItem.amount) }}
            </div>

            <div class="sprout-dashboard-mobile__transaction-time">
              {{ transactionItem.time }}
            </div>
          </div>
        </div>
      </article>
    </section>

    <!-- Floating Action Button -->
    <a
      href="/transactions/create"
      class="sprout-dashboard-mobile__fab"
      aria-label="Add transaction"
    >
      +
    </a>
  </div>
</template>

<script setup>
/* Vue Imports */
import { computed, ref } from 'vue'

/* Filter Options */
const filterOptions = ['All', 'Income', 'Expense', 'Savings']

/* Weekday Labels */
const weekdayLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']

/* Month Options */
const monthOptions = [
  { value: 0, label: 'Jan' },
  { value: 1, label: 'Feb' },
  { value: 2, label: 'Mar' },
  { value: 3, label: 'Apr' },
  { value: 4, label: 'May' },
  { value: 5, label: 'Jun' },
  { value: 6, label: 'Jul' },
  { value: 7, label: 'Aug' },
  { value: 8, label: 'Sep' },
  { value: 9, label: 'Oct' },
  { value: 10, label: 'Nov' },
  { value: 11, label: 'Dec' }
]

/* Display State */
const selectedFilter = ref('All')
const selectedPeriodView = ref('month')
const isFilterMenuVisible = ref(false)
const isPeriodMenuVisible = ref(false)
const currentDisplayDate = ref(new Date(2026, 2, 3))
const selectedDate = ref(new Date(2026, 2, 3))
const displayYear = ref(2026)

/* Mock Transaction Data */
const transactionGroups = ref([
  {
    dateKey: '2026-03-03',
    dateLabel: 'Tue, March 03',
    income: 2500,
    expense: 2000,
    transactions: [
      {
        id: 1,
        type: 'expense',
        category: 'Shopping',
        amount: 1500,
        time: '12:30pm',
        description: '',
        iconPath: '/projectassets/icons/shopping.svg',
        iconColor: 'coral'
      },
      {
        id: 2,
        type: 'income',
        category: 'Salary',
        amount: 2500,
        time: '5:00pm',
        description: '',
        iconPath: '/projectassets/icons/salary.svg',
        iconColor: 'green'
      },
      {
        id: 3,
        type: 'expense',
        category: 'Transport',
        amount: 500,
        time: '8:30am',
        description: 'With my Friends',
        iconPath: '/projectassets/icons/transport.svg',
        iconColor: 'blue'
      }
    ]
  },
  {
    dateKey: '2026-03-02',
    dateLabel: 'Mon, March 02',
    income: 0,
    expense: 1500,
    transactions: [
      {
        id: 4,
        type: 'expense',
        category: 'Shopping',
        amount: 1500,
        time: '1:30pm',
        description: '',
        iconPath: '/projectassets/icons/shopping.svg',
        iconColor: 'coral'
      }
    ]
  }
])

/* Current Period Label */
const currentPeriodLabel = computed(() => {
  if (selectedPeriodView.value === 'week') {
    return 'This Week'
  }

  if (selectedPeriodView.value === 'year') {
    return 'This Year'
  }

  return 'This Month'
})

/* Calendar Heading */
const calendarHeading = computed(() => {
  if (selectedPeriodView.value === 'year') {
    return String(displayYear.value)
  }

  return currentDisplayDate.value.toLocaleDateString('en-US', {
    month: 'long'
  })
})

/* Transaction Summary By Date */
const transactionSummaryByDate = computed(() => {
  const summaryMap = {}

  transactionGroups.value.forEach((transactionGroup) => {
    summaryMap[transactionGroup.dateKey] = {
      income: transactionGroup.income,
      expense: transactionGroup.expense
    }
  })

  return summaryMap
})

/* Filtered Transaction Groups */
const filteredTransactionGroups = computed(() => {
  if (selectedFilter.value === 'All') {
    return transactionGroups.value
  }

  const targetType = selectedFilter.value.toLowerCase()

  return transactionGroups.value
    .map((transactionGroup) => {
      const filteredTransactions = transactionGroup.transactions.filter((transactionItem) => {
        return transactionItem.type === targetType
      })

      const recalculatedIncome = filteredTransactions
        .filter((transactionItem) => transactionItem.type === 'income')
        .reduce((totalAmount, transactionItem) => totalAmount + Number(transactionItem.amount), 0)

      const recalculatedExpense = filteredTransactions
        .filter((transactionItem) => transactionItem.type === 'expense')
        .reduce((totalAmount, transactionItem) => totalAmount + Number(transactionItem.amount), 0)

      return {
        ...transactionGroup,
        transactions: filteredTransactions,
        income: recalculatedIncome,
        expense: recalculatedExpense
      }
    })
    .filter((transactionGroup) => transactionGroup.transactions.length > 0)
})

/* Period Summary */
const periodSummary = computed(() => {
  let totalIncome = 0
  let totalExpense = 0

  filteredTransactionGroups.value.forEach((transactionGroup) => {
    totalIncome += Number(transactionGroup.income)
    totalExpense += Number(transactionGroup.expense)
  })

  const rawBalance = totalIncome - totalExpense

  return {
    income: totalIncome,
    expense: totalExpense,
    balance: rawBalance < 0 ? 0 : rawBalance
  }
})

/* Month Calendar Cells */
const monthCalendarCells = computed(() => {
  const year = currentDisplayDate.value.getFullYear()
  const month = currentDisplayDate.value.getMonth()

  const firstDayOfMonth = new Date(year, month, 1)
  const startIndex = (firstDayOfMonth.getDay() + 6) % 7
  const firstVisibleDate = new Date(year, month, 1 - startIndex)

  return Array.from({ length: 42 }, (_, index) => {
    const cellDate = new Date(firstVisibleDate)
    cellDate.setDate(firstVisibleDate.getDate() + index)

    const dateKey = formatDateKey(cellDate)
    const daySummary = transactionSummaryByDate.value[dateKey] ?? { income: 0, expense: 0 }

    return {
      key: `${dateKey}-${index}`,
      date: cellDate,
      day: cellDate.getDate(),
      isCurrentMonth: cellDate.getMonth() === month,
      isSelected: isSameDate(cellDate, selectedDate.value),
      dailyIncome: daySummary.income,
      dailyExpense: daySummary.expense
    }
  })
})

/* Week Calendar Cells */
const weekCalendarCells = computed(() => {
  const focusedDate = new Date(selectedDate.value)
  const startIndex = (focusedDate.getDay() + 6) % 7
  const firstDateOfWeek = new Date(focusedDate)
  firstDateOfWeek.setDate(focusedDate.getDate() - startIndex)

  return Array.from({ length: 7 }, (_, index) => {
    const cellDate = new Date(firstDateOfWeek)
    cellDate.setDate(firstDateOfWeek.getDate() + index)

    const dateKey = formatDateKey(cellDate)
    const daySummary = transactionSummaryByDate.value[dateKey] ?? { income: 0, expense: 0 }

    return {
      key: `${dateKey}-${index}`,
      date: cellDate,
      day: cellDate.getDate(),
      weekdayShort: cellDate.toLocaleDateString('en-US', { weekday: 'short' }),
      isSelected: isSameDate(cellDate, selectedDate.value),
      dailyIncome: daySummary.income,
      dailyExpense: daySummary.expense
    }
  })
})

/* Year Month Summaries */
const yearMonthSummaries = computed(() => {
  return monthOptions.map((monthOption) => {
    let totalIncome = 0
    let totalExpense = 0

    filteredTransactionGroups.value.forEach((transactionGroup) => {
      const groupDate = new Date(transactionGroup.dateKey)

      if (
        groupDate.getFullYear() === displayYear.value &&
        groupDate.getMonth() === monthOption.value
      ) {
        totalIncome += Number(transactionGroup.income)
        totalExpense += Number(transactionGroup.expense)
      }
    })

    return {
      monthIndex: monthOption.value,
      label: monthOption.label,
      income: totalIncome,
      expense: totalExpense
    }
  })
})

/* Toggle Filter Menu */
const toggleFilterMenu = () => {
  isFilterMenuVisible.value = !isFilterMenuVisible.value
  isPeriodMenuVisible.value = false
}

/* Toggle Period Menu */
const togglePeriodMenu = () => {
  isPeriodMenuVisible.value = !isPeriodMenuVisible.value
  isFilterMenuVisible.value = false
}

/* Select Filter */
const selectFilter = (filterOption) => {
  selectedFilter.value = filterOption
  isFilterMenuVisible.value = false
}

/* Set Period View */
const setPeriodView = (periodView) => {
  selectedPeriodView.value = periodView
}

/* Select Date */
const selectDate = (date) => {
  selectedDate.value = new Date(date)
  currentDisplayDate.value = new Date(date)
}

/* Select Month */
const selectMonth = (monthIndex) => {
  currentDisplayDate.value = new Date(
    currentDisplayDate.value.getFullYear(),
    monthIndex,
    1
  )

  selectedDate.value = new Date(
    currentDisplayDate.value.getFullYear(),
    monthIndex,
    1
  )

  isPeriodMenuVisible.value = false
}

/* Select Month From Year */
const selectMonthFromYear = (monthIndex) => {
  currentDisplayDate.value = new Date(displayYear.value, monthIndex, 1)
  selectedDate.value = new Date(displayYear.value, monthIndex, 1)
  selectedPeriodView.value = 'month'
  isPeriodMenuVisible.value = false
}

/* Go Previous Period */
const goPreviousPeriod = () => {
  if (selectedPeriodView.value === 'week') {
    const previousWeekDate = new Date(selectedDate.value)
    previousWeekDate.setDate(previousWeekDate.getDate() - 7)
    selectedDate.value = previousWeekDate
    currentDisplayDate.value = new Date(previousWeekDate)
    return
  }

  if (selectedPeriodView.value === 'year') {
    displayYear.value -= 1
    return
  }

  const previousMonthDate = new Date(currentDisplayDate.value)
  previousMonthDate.setMonth(previousMonthDate.getMonth() - 1)
  currentDisplayDate.value = previousMonthDate
  selectedDate.value = new Date(previousMonthDate)
}

/* Go Next Period */
const goNextPeriod = () => {
  if (selectedPeriodView.value === 'week') {
    const nextWeekDate = new Date(selectedDate.value)
    nextWeekDate.setDate(nextWeekDate.getDate() + 7)
    selectedDate.value = nextWeekDate
    currentDisplayDate.value = new Date(nextWeekDate)
    return
  }

  if (selectedPeriodView.value === 'year') {
    displayYear.value += 1
    return
  }

  const nextMonthDate = new Date(currentDisplayDate.value)
  nextMonthDate.setMonth(nextMonthDate.getMonth() + 1)
  currentDisplayDate.value = nextMonthDate
  selectedDate.value = new Date(nextMonthDate)
}

/* Format Date Key */
const formatDateKey = (date) => {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')

  return `${year}-${month}-${day}`
}

/* Same Date Check */
const isSameDate = (firstDate, secondDate) => {
  return firstDate.getFullYear() === secondDate.getFullYear()
    && firstDate.getMonth() === secondDate.getMonth()
    && firstDate.getDate() === secondDate.getDate()
}

/* Format Money */
const formatMoney = (amount) => {
  return Number(amount || 0).toLocaleString('en-PH')
}

/* Format Compact Amount */
const formatCompactAmount = (amount) => {
  return Number(amount || 0).toLocaleString('en-PH')
}

/* Transaction Icon Class */
const transactionIconClass = (iconColor) => {
  return {
    'sprout-dashboard-mobile__transaction-icon--coral': iconColor === 'coral',
    'sprout-dashboard-mobile__transaction-icon--green': iconColor === 'green',
    'sprout-dashboard-mobile__transaction-icon--blue': iconColor === 'blue'
  }
}

/* Transaction Amount Class */
const transactionAmountClass = (transactionType) => {
  return {
    'sprout-dashboard-mobile__transaction-amount--income': transactionType === 'income',
    'sprout-dashboard-mobile__transaction-amount--expense': transactionType === 'expense',
    'sprout-dashboard-mobile__transaction-amount--savings': transactionType === 'savings'
  }
}
</script>