<template>
  <!-- Dashboard Root -->
  <div class="sprout-dashboard-mobile">
    <!-- Dashboard Backdrop -->
    <div
      v-if="isFilterMenuVisible || isPeriodMenuVisible || isActionModalVisible"
      class="sprout-dashboard-mobile__backdrop"
      @click="closePanels"
    ></div>

    <!-- Dashboard Top Bar -->
    <header class="sprout-dashboard-mobile__topbar">
      <button
        type="button"
        class="sprout-dashboard-mobile__filter"
        @click.stop="toggleFilterMenu"
      >
        {{ selectedFilter }}
        <span class="sprout-dashboard-mobile__filter-caret">&#9662;</span>
      </button>

      <div class="sprout-dashboard-mobile__period">
        <button
          type="button"
          class="sprout-dashboard-mobile__period-arrow"
          @click="goPreviousPeriod"
          aria-label="Previous period"
        >
          &lsaquo;
        </button>

        <button
          type="button"
          class="sprout-dashboard-mobile__period-trigger"
          @click.stop="togglePeriodMenu"
        >
          {{ currentPeriodLabel }}
        </button>

        <button
          type="button"
          class="sprout-dashboard-mobile__period-arrow"
          @click="goNextPeriod"
          aria-label="Next period"
        >
          &rsaquo;
        </button>
      </div>

      <div class="sprout-dashboard-mobile__topbar-space"></div>
    </header>

    <!-- Dashboard Filter Dropdown -->
    <div
      v-if="isFilterMenuVisible"
      class="sprout-dashboard-mobile__dropdown"
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

    <!-- Dashboard Period Panel -->
    <section
      v-if="isPeriodMenuVisible"
      class="sprout-dashboard-mobile__period-panel"
    >
      <!-- Dashboard Period Tabs -->
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

      <!-- Dashboard Week Panel -->
      <div
        v-if="selectedPeriodView === 'week'"
        class="sprout-dashboard-mobile__week-panel"
      >
        <div class="sprout-dashboard-mobile__week-head">
          <button
            type="button"
            class="sprout-dashboard-mobile__week-arrow"
            @click="shiftWeekPanelMonth(-1)"
            aria-label="Previous month"
          >
            &#8249;
          </button>

          <div class="sprout-dashboard-mobile__week-value">
            {{ weekPanelLabel }}
          </div>

          <button
            type="button"
            class="sprout-dashboard-mobile__week-arrow"
            @click="shiftWeekPanelMonth(1)"
            aria-label="Next month"
          >
            &#8250;
          </button>
        </div>

        <div class="sprout-dashboard-mobile__week-weekdays">
          <span
            v-for="weekdayLabel in weekdayLabels"
            :key="`week-picker-${weekdayLabel}`"
          >
            {{ weekdayLabel }}
          </span>
        </div>

        <div class="sprout-dashboard-mobile__week-picker-grid">
          <button
            v-for="weekCell in weekPickerCells"
            :key="`picker-${weekCell.key}`"
            type="button"
            class="sprout-dashboard-mobile__week-date"
            :class="{
              'sprout-dashboard-mobile__week-date--muted': !weekCell.isCurrentMonth,
              'sprout-dashboard-mobile__week-date--week': weekCell.isInActiveWeek,
              'sprout-dashboard-mobile__week-date--start': weekCell.isWeekStart,
              'sprout-dashboard-mobile__week-date--end': weekCell.isWeekEnd
            }"
            @click="selectDate(weekCell.date)"
          >
            {{ weekCell.day }}
          </button>
        </div>
      </div>

      <!-- Dashboard Month Panel -->
      <div
        v-if="selectedPeriodView === 'month'"
        class="sprout-dashboard-mobile__month-panel"
      >
        <div class="sprout-dashboard-mobile__year-switcher sprout-dashboard-mobile__year-switcher--month">
          <button
            type="button"
            class="sprout-dashboard-mobile__year-arrow"
            @click="shiftMonthPanelYear(-1)"
            aria-label="Previous year"
          >
            &lsaquo;
          </button>

          <div class="sprout-dashboard-mobile__year-value">
            {{ currentDisplayDate.getFullYear() }}
          </div>

          <button
            type="button"
            class="sprout-dashboard-mobile__year-arrow"
            @click="shiftMonthPanelYear(1)"
            aria-label="Next year"
          >
            &rsaquo;
          </button>
        </div>

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

      <!-- Dashboard Year Panel -->
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
            &lsaquo;
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
            &rsaquo;
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

    <!-- Dashboard Calendar Card -->
    <section class="sprout-dashboard-mobile__calendar-card">
      <!-- Dashboard Calendar Heading -->
      <div class="sprout-dashboard-mobile__calendar-heading">
        {{ calendarHeading }}
      </div>

      <!-- Dashboard Weekday Row -->
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

      <!-- Dashboard Month View -->
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
            v-if="calendarCell.dailyIncome > 0 || calendarCell.dailyExpense > 0 || calendarCell.dailySavings > 0"
            class="sprout-dashboard-mobile__day-legends"
            :class="{
              'sprout-dashboard-mobile__day-legends--all': selectedFilter === 'All'
            }"
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

            <span
              v-if="calendarCell.dailySavings > 0"
              class="sprout-dashboard-mobile__day-amount sprout-dashboard-mobile__day-amount--savings"
            >
              ₱{{ formatCompactAmount(calendarCell.dailySavings) }}
            </span>
          </div>
        </button>
      </div>

      <!-- Dashboard Week View -->
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
            v-if="weekCell.dailyIncome > 0 || weekCell.dailyExpense > 0 || weekCell.dailySavings > 0"
            class="sprout-dashboard-mobile__day-legends"
            :class="{
              'sprout-dashboard-mobile__day-legends--all': selectedFilter === 'All'
            }"
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

            <span
              v-if="weekCell.dailySavings > 0"
              class="sprout-dashboard-mobile__day-amount sprout-dashboard-mobile__day-amount--savings"
            >
              ₱{{ formatCompactAmount(weekCell.dailySavings) }}
            </span>
          </div>
        </button>
      </div>

      <!-- Dashboard Year View -->
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
            class="sprout-dashboard-mobile__year-summary-amount sprout-dashboard-mobile__year-summary-amount--income"
            :class="{
              'sprout-dashboard-mobile__year-summary-amount--all': selectedFilter === 'All'
            }"
          >
            {{ formatYearLegendAmount(yearSummaryItem.income) }}
          </span>

          <span
            v-if="yearSummaryItem.expense > 0"
            class="sprout-dashboard-mobile__year-summary-amount sprout-dashboard-mobile__year-summary-amount--expense"
            :class="{
              'sprout-dashboard-mobile__year-summary-amount--all': selectedFilter === 'All'
            }"
          >
            {{ formatYearLegendAmount(yearSummaryItem.expense) }}
          </span>

          <span
            v-if="yearSummaryItem.savings > 0"
            class="sprout-dashboard-mobile__year-summary-amount sprout-dashboard-mobile__year-summary-amount--savings"
            :class="{
              'sprout-dashboard-mobile__year-summary-amount--all': selectedFilter === 'All'
            }"
          >
            {{ formatYearLegendAmount(yearSummaryItem.savings) }}
          </span>
        </button>
      </div>

      <!-- Dashboard Summary Row -->
      <div
        class="sprout-dashboard-mobile__summary-row"
        :class="{
          'sprout-dashboard-mobile__summary-row--four': !isSummaryCompact,
          'sprout-dashboard-mobile__summary-row--compact': isSummaryCompact
        }"
      >
        <div
          class="sprout-dashboard-mobile__summary-item sprout-dashboard-mobile__summary-item--income"
          :class="{
            'sprout-dashboard-mobile__summary-item--left': !isSummaryCompact
          }"
        >
          <div class="sprout-dashboard-mobile__summary-label">Income</div>
          <div
            class="sprout-dashboard-mobile__summary-value sprout-dashboard-mobile__summary-value--income"
            :class="{
              'sprout-dashboard-mobile__summary-value--compact': isSummaryCompact
            }"
          >
            ₱{{ formatMoney(periodSummary.income) }}
          </div>
        </div>

        <div
          class="sprout-dashboard-mobile__summary-item sprout-dashboard-mobile__summary-item--expense"
          :class="{
            'sprout-dashboard-mobile__summary-item--center': !isSummaryCompact
          }"
        >
          <div class="sprout-dashboard-mobile__summary-label">Expense</div>
          <div
            class="sprout-dashboard-mobile__summary-value sprout-dashboard-mobile__summary-value--expense"
            :class="{
              'sprout-dashboard-mobile__summary-value--compact': isSummaryCompact
            }"
          >
            ₱{{ formatMoney(periodSummary.expense) }}
          </div>
        </div>

        <div
          class="sprout-dashboard-mobile__summary-item sprout-dashboard-mobile__summary-item--savings"
          :class="{
            'sprout-dashboard-mobile__summary-item--center': !isSummaryCompact
          }"
        >
          <div class="sprout-dashboard-mobile__summary-label">Savings</div>
          <div
            class="sprout-dashboard-mobile__summary-value sprout-dashboard-mobile__summary-value--savings"
            :class="{
              'sprout-dashboard-mobile__summary-value--compact': isSummaryCompact
            }"
          >
            ₱{{ formatMoney(periodSummary.savings) }}
          </div>
        </div>

        <div
          class="sprout-dashboard-mobile__summary-item sprout-dashboard-mobile__summary-item--balance"
          :class="{
            'sprout-dashboard-mobile__summary-item--right': !isSummaryCompact
          }"
        >
          <div class="sprout-dashboard-mobile__summary-label">Balance</div>
          <div
            class="sprout-dashboard-mobile__summary-value sprout-dashboard-mobile__summary-value--balance"
            :class="{
              'sprout-dashboard-mobile__summary-value--compact': isSummaryCompact
            }"
          >
            ₱{{ formatMoney(periodSummary.balance) }}
          </div>
        </div>
      </div>
    </section>

    <!-- Dashboard History List -->
    <section class="sprout-dashboard-mobile__history-list">
      <article
        v-for="transactionGroup in visiblePeriodGroups"
        :key="transactionGroup.dateKey"
        :ref="(element) => setTransactionGroupRef(element, transactionGroup.dateKey)"
        class="sprout-dashboard-mobile__history-card"
      >
        <!-- Dashboard History Header -->
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

            <span
              v-if="transactionGroup.savings > 0"
              class="sprout-dashboard-mobile__history-total sprout-dashboard-mobile__history-total--savings"
            >
              SAVE ₱{{ formatMoney(transactionGroup.savings) }}
            </span>
          </div>
        </div>

        <!-- Dashboard Transaction Rows -->
        <button
          v-for="transactionItem in transactionGroup.transactions"
          :key="transactionItem.id"
          type="button"
          class="sprout-dashboard-mobile__transaction-row sprout-dashboard-mobile__transaction-row--button"
          @click="openTransactionActionModal(transactionItem, transactionGroup.dateLabel)"
        >
          <div class="sprout-dashboard-mobile__transaction-left">
            <div
              v-if="transactionItem.iconPath"
              class="sprout-dashboard-mobile__transaction-icon"
              :class="transactionIconClass(transactionItem.iconColor)"
            >
              <img
                :src="transactionItem.iconPath"
                :alt="transactionItem.category"
                class="sprout-dashboard-mobile__transaction-icon-image"
              >
            </div>

            <div
              class="sprout-dashboard-mobile__transaction-text"
              :class="{
                'sprout-dashboard-mobile__transaction-text--no-icon': !transactionItem.iconPath
              }"
            >
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
              {{ transactionDisplayPrefix(transactionItem.type) }}₱{{ formatMoney(transactionItem.amount) }}
            </div>

            <div class="sprout-dashboard-mobile__transaction-time">
              {{ transactionItem.time }}
            </div>
          </div>
        </button>
      </article>
    </section>

    <!-- Dashboard Floating Action Button -->
    <a
      :href="createTransactionHref"
      class="sprout-dashboard-mobile__fab"
      aria-label="Add transaction"
    >
      +
    </a>

    <!-- Dashboard Action Modal -->
    <div
      v-if="isActionModalVisible && activeTransaction"
      class="sprout-dashboard-mobile__action-modal"
    >
      <button
        type="button"
        class="sprout-dashboard-mobile__action-backdrop"
        @click="closeTransactionActionModal"
        aria-label="Close actions"
      ></button>

      <div class="sprout-dashboard-mobile__action-sheet">
        <div class="sprout-dashboard-mobile__action-title">
          Transaction Options
        </div>

        <div class="sprout-dashboard-mobile__action-subtitle">
          {{ activeTransaction.category }}
        </div>

        <button
          type="button"
          class="sprout-dashboard-mobile__action-button"
          @click="openTransactionViewModal"
        >
          View Transaction
        </button>

        <button
          type="button"
          class="sprout-dashboard-mobile__action-button"
          @click="goToEditTransaction"
        >
          Edit Transaction
        </button>

        <form
          :action="deleteTransactionUrl"
          method="POST"
        >
          <input type="hidden" name="_token" :value="csrfToken">
          <input type="hidden" name="_method" value="DELETE">

          <button
            type="submit"
            class="sprout-dashboard-mobile__action-button sprout-dashboard-mobile__action-button--delete"
          >
            Delete Transaction
          </button>
        </form>

        <button
          type="button"
          class="sprout-dashboard-mobile__action-button sprout-dashboard-mobile__action-button--cancel"
          @click="closeTransactionActionModal"
        >
          Cancel
        </button>
      </div>
    </div>

    <!-- Dashboard View Modal -->
    <div
      v-if="isViewModalVisible && activeTransaction"
      class="sprout-dashboard-mobile__action-modal"
    >
      <button
        type="button"
        class="sprout-dashboard-mobile__action-backdrop"
        @click="closeTransactionViewModal"
        aria-label="Close transaction details"
      ></button>

      <div class="sprout-dashboard-mobile__view-sheet">
        <div class="sprout-dashboard-mobile__view-title">
          Transaction Details
        </div>

        <div class="sprout-dashboard-mobile__view-card">
          <div class="sprout-dashboard-mobile__view-row">
            <span class="sprout-dashboard-mobile__view-label">Category</span>
            <span class="sprout-dashboard-mobile__view-value">
              {{ activeTransaction.category }}
            </span>
          </div>

          <div class="sprout-dashboard-mobile__view-row">
            <span class="sprout-dashboard-mobile__view-label">Type</span>
            <span class="sprout-dashboard-mobile__view-value">
              {{ formatTransactionTypeLabel(activeTransaction.type) }}
            </span>
          </div>

          <div class="sprout-dashboard-mobile__view-row">
            <span class="sprout-dashboard-mobile__view-label">Date</span>
            <span class="sprout-dashboard-mobile__view-value">
              {{ activeTransactionDateLabel }}
            </span>
          </div>

          <div class="sprout-dashboard-mobile__view-row">
            <span class="sprout-dashboard-mobile__view-label">Time</span>
            <span class="sprout-dashboard-mobile__view-value">
              {{ activeTransaction.time }}
            </span>
          </div>

          <div class="sprout-dashboard-mobile__view-row">
            <span class="sprout-dashboard-mobile__view-label">Amount</span>
            <span
              class="sprout-dashboard-mobile__view-value sprout-dashboard-mobile__view-value--amount"
              :class="transactionAmountClass(activeTransaction.type)"
            >
              {{ transactionDisplayPrefix(activeTransaction.type) }}₱{{ formatMoney(activeTransaction.amount) }}
            </span>
          </div>

          <div
            v-if="activeTransaction.account"
            class="sprout-dashboard-mobile__view-row"
          >
            <span class="sprout-dashboard-mobile__view-label">Account</span>
            <span class="sprout-dashboard-mobile__view-value">
              {{ activeTransaction.account }}
            </span>
          </div>

          <div
            v-if="activeTransaction.description"
            class="sprout-dashboard-mobile__view-description-block"
          >
            <div class="sprout-dashboard-mobile__view-description-label">
              Description
            </div>

            <div class="sprout-dashboard-mobile__view-description-value">
              {{ activeTransaction.description }}
            </div>
          </div>

          <!-- Receipt Photos -->
          <div
            v-if="hasActiveTransactionReceiptPhotos"
            class="sprout-dashboard-mobile__view-photos-block"
          >
            <div class="sprout-dashboard-mobile__view-description-label">
              Receipt Photos
            </div>

            <div class="sprout-dashboard-mobile__view-photos-grid">
              <a
                v-for="(receiptPhotoUrl, photoIndex) in activeTransaction.receiptPhotoUrls"
                :key="`${activeTransaction.id}-photo-${photoIndex}`"
                :href="receiptPhotoUrl"
                target="_blank"
                rel="noopener noreferrer"
                class="sprout-dashboard-mobile__view-photo-link"
              >
                <img
                  :src="receiptPhotoUrl"
                  :alt="`Receipt photo ${photoIndex + 1}`"
                  class="sprout-dashboard-mobile__view-photo-image"
                >
              </a>
            </div>
          </div>
        </div>

        <button
          type="button"
          class="sprout-dashboard-mobile__action-button sprout-dashboard-mobile__action-button--cancel"
          @click="closeTransactionViewModal"
        >
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
/* Vue Imports */
import { computed, nextTick, ref } from 'vue'

/* Dashboard Props */
const props = defineProps({
  initialTransactionGroups: {
    type: Array,
    default: () => []
  },
  initialDisplayDate: {
    type: String,
    default: ''
  },
  csrfToken: {
    type: String,
    default: ''
  }
})

/* Dashboard Filter Options */
const filterOptions = ['All', 'Income', 'Expense', 'Savings']

/* Dashboard Weekday Labels */
const weekdayLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']

/* Dashboard Month Options */
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

/* Dashboard Initial Date */
const resolvedInitialDate = (() => {
  if (!props.initialDisplayDate) {
    return new Date()
  }

  const [year, month, day] = props.initialDisplayDate.split('-').map(Number)

  if (!year || !month || !day) {
    return new Date()
  }

  return new Date(year, month - 1, day)
})()

/* Dashboard Display State */
const selectedFilter = ref('All')
const selectedPeriodView = ref('month')
const isFilterMenuVisible = ref(false)
const isPeriodMenuVisible = ref(false)
const currentDisplayDate = ref(new Date(resolvedInitialDate))
const selectedDate = ref(new Date(resolvedInitialDate))
const displayYear = ref(resolvedInitialDate.getFullYear())
const isActionModalVisible = ref(false)
const isViewModalVisible = ref(false)
const activeTransaction = ref(null)
const activeTransactionDateLabel = ref('')

/* Dashboard Create Transaction Href */
const createTransactionHref = computed(() => {
  const selectedDateKey = formatDateKey(selectedDate.value)
  return `/transactions/create?date=${selectedDateKey}`
})

/* Dashboard Transaction Data */
const transactionGroups = ref(props.initialTransactionGroups)

/* Dashboard Transaction Group Refs */
const transactionGroupRefs = ref({})

/* Dashboard Current Period Label */
const currentPeriodLabel = computed(() => {
  if (selectedPeriodView.value === 'week') {
    return 'This Week'
  }

  if (selectedPeriodView.value === 'year') {
    return 'This Year'
  }

  return 'This Month'
})

/* Dashboard Week Panel Label */
const weekPanelLabel = computed(() => {
  return currentDisplayDate.value.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short'
  })
})

/* Dashboard Calendar Heading */
const calendarHeading = computed(() => {
  if (selectedPeriodView.value === 'year') {
    return String(displayYear.value)
  }

  return currentDisplayDate.value.toLocaleDateString('en-US', {
    month: 'long'
  })
})

/* Dashboard Active Transaction Receipt Photos */
const hasActiveTransactionReceiptPhotos = computed(() => {
  return Array.isArray(activeTransaction.value?.receiptPhotoUrls)
    && activeTransaction.value.receiptPhotoUrls.length > 0
})

/* Dashboard Filtered Transaction Groups */
const filteredTransactionGroups = computed(() => {
  if (selectedFilter.value === 'All') {
    return transactionGroups.value.map((transactionGroup) => ({
      ...transactionGroup,
      income: Number(transactionGroup.income || 0),
      expense: Number(transactionGroup.expense || 0),
      savings: Number(transactionGroup.savings || 0),
      transactions: (transactionGroup.transactions || []).map((transactionItem) => ({
        ...transactionItem,
        receiptPhotoUrls: Array.isArray(transactionItem.receiptPhotoUrls)
          ? transactionItem.receiptPhotoUrls
          : []
      }))
    }))
  }

  const targetType = selectedFilter.value.toLowerCase()

  return transactionGroups.value
    .map((transactionGroup) => {
      const filteredTransactions = (transactionGroup.transactions || [])
        .filter((transactionItem) => {
          return transactionItem.type === targetType
        })
        .map((transactionItem) => ({
          ...transactionItem,
          receiptPhotoUrls: Array.isArray(transactionItem.receiptPhotoUrls)
            ? transactionItem.receiptPhotoUrls
            : []
        }))

      const recalculatedIncome = filteredTransactions
        .filter((transactionItem) => transactionItem.type === 'income')
        .reduce((totalAmount, transactionItem) => totalAmount + Number(transactionItem.amount), 0)

      const recalculatedExpense = filteredTransactions
        .filter((transactionItem) => transactionItem.type === 'expense')
        .reduce((totalAmount, transactionItem) => totalAmount + Number(transactionItem.amount), 0)

      const recalculatedSavings = filteredTransactions
        .filter((transactionItem) => transactionItem.type === 'savings')
        .reduce((totalAmount, transactionItem) => totalAmount + Number(transactionItem.amount), 0)

      return {
        ...transactionGroup,
        transactions: filteredTransactions,
        income: recalculatedIncome,
        expense: recalculatedExpense,
        savings: recalculatedSavings
      }
    })
    .filter((transactionGroup) => transactionGroup.transactions.length > 0)
})

/* Dashboard Transaction Summary By Date */
const transactionSummaryByDate = computed(() => {
  const summaryMap = {}

  filteredTransactionGroups.value.forEach((transactionGroup) => {
    summaryMap[transactionGroup.dateKey] = {
      income: Number(transactionGroup.income || 0),
      expense: Number(transactionGroup.expense || 0),
      savings: Number(transactionGroup.savings || 0)
    }
  })

  return summaryMap
})

/* Dashboard Visible Period Groups */
const visiblePeriodGroups = computed(() => {
  if (selectedPeriodView.value === 'year') {
    return filteredTransactionGroups.value.filter((transactionGroup) => {
      const [year] = transactionGroup.dateKey.split('-').map(Number)

      return year === displayYear.value
    })
  }

  if (selectedPeriodView.value === 'week') {
    const focusedDate = new Date(selectedDate.value)
    const startIndex = (focusedDate.getDay() + 6) % 7
    const firstDateOfWeek = new Date(focusedDate)

    firstDateOfWeek.setDate(focusedDate.getDate() - startIndex)
    firstDateOfWeek.setHours(0, 0, 0, 0)

    const lastDateOfWeek = new Date(firstDateOfWeek)

    lastDateOfWeek.setDate(firstDateOfWeek.getDate() + 6)
    lastDateOfWeek.setHours(23, 59, 59, 999)

    return filteredTransactionGroups.value.filter((transactionGroup) => {
      const transactionDate = buildDateFromKey(transactionGroup.dateKey)

      return transactionDate >= firstDateOfWeek && transactionDate <= lastDateOfWeek
    })
  }

  return filteredTransactionGroups.value.filter((transactionGroup) => {
    const [year, month] = transactionGroup.dateKey.split('-').map(Number)

    return year === currentDisplayDate.value.getFullYear()
      && month - 1 === currentDisplayDate.value.getMonth()
  })
})

/* Dashboard Period Summary */
const periodSummary = computed(() => {
  let totalIncome = 0
  let totalExpense = 0
  let totalSavings = 0

  visiblePeriodGroups.value.forEach((transactionGroup) => {
    totalIncome += Number(transactionGroup.income || 0)
    totalExpense += Number(transactionGroup.expense || 0)
    totalSavings += Number(transactionGroup.savings || 0)
  })

  return {
    income: totalIncome,
    expense: totalExpense,
    savings: totalSavings,
    balance: totalIncome - totalExpense - totalSavings
  }
})

/* Dashboard Summary Compact State */
const isSummaryCompact = computed(() => {
  const formattedValues = [
    formatMoney(periodSummary.value.income),
    formatMoney(periodSummary.value.expense),
    formatMoney(periodSummary.value.savings),
    formatMoney(periodSummary.value.balance)
  ]

  return formattedValues.some((formattedValue) => formattedValue.length >= 9)
})

/* Dashboard Month Calendar Cells */
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
    const daySummary = transactionSummaryByDate.value[dateKey] ?? {
      income: 0,
      expense: 0,
      savings: 0
    }

    return {
      key: `${dateKey}-${index}`,
      date: cellDate,
      day: cellDate.getDate(),
      isCurrentMonth: cellDate.getMonth() === month,
      isSelected: isSameDate(cellDate, selectedDate.value),
      dailyIncome: Number(daySummary.income || 0),
      dailyExpense: Number(daySummary.expense || 0),
      dailySavings: Number(daySummary.savings || 0)
    }
  })
})

/* Dashboard Week Calendar Cells */
const weekCalendarCells = computed(() => {
  const focusedDate = new Date(selectedDate.value)
  const startIndex = (focusedDate.getDay() + 6) % 7
  const firstDateOfWeek = new Date(focusedDate)

  firstDateOfWeek.setDate(focusedDate.getDate() - startIndex)

  return Array.from({ length: 7 }, (_, index) => {
    const cellDate = new Date(firstDateOfWeek)

    cellDate.setDate(firstDateOfWeek.getDate() + index)

    const dateKey = formatDateKey(cellDate)
    const daySummary = transactionSummaryByDate.value[dateKey] ?? {
      income: 0,
      expense: 0,
      savings: 0
    }

    return {
      key: `${dateKey}-${index}`,
      date: cellDate,
      day: cellDate.getDate(),
      weekdayShort: cellDate.toLocaleDateString('en-US', { weekday: 'short' }),
      isSelected: isSameDate(cellDate, selectedDate.value),
      dailyIncome: Number(daySummary.income || 0),
      dailyExpense: Number(daySummary.expense || 0),
      dailySavings: Number(daySummary.savings || 0)
    }
  })
})

/* Dashboard Week Picker Cells */
const weekPickerCells = computed(() => {
  const monthStart = new Date(
    currentDisplayDate.value.getFullYear(),
    currentDisplayDate.value.getMonth(),
    1
  )
  const startIndex = (monthStart.getDay() + 6) % 7
  const firstVisibleDate = new Date(monthStart)

  firstVisibleDate.setDate(monthStart.getDate() - startIndex)

  const activeWeekStart = startOfWeek(selectedDate.value)
  const activeWeekEnd = endOfWeek(selectedDate.value)

  return Array.from({ length: 42 }, (_, index) => {
    const cellDate = new Date(firstVisibleDate)

    cellDate.setDate(firstVisibleDate.getDate() + index)

    return {
      key: formatDateKey(cellDate),
      date: cellDate,
      day: cellDate.getDate(),
      isCurrentMonth: cellDate.getMonth() === currentDisplayDate.value.getMonth(),
      isInActiveWeek: cellDate >= activeWeekStart && cellDate <= activeWeekEnd,
      isWeekStart: isSameDate(cellDate, activeWeekStart),
      isWeekEnd: isSameDate(cellDate, activeWeekEnd)
    }
  })
})

/* Dashboard Year Month Summaries */
const yearMonthSummaries = computed(() => {
  return monthOptions.map((monthOption) => {
    let totalIncome = 0
    let totalExpense = 0
    let totalSavings = 0

    filteredTransactionGroups.value.forEach((transactionGroup) => {
      const [year, month] = transactionGroup.dateKey.split('-').map(Number)

      if (
        year === displayYear.value &&
        month - 1 === monthOption.value
      ) {
        totalIncome += Number(transactionGroup.income || 0)
        totalExpense += Number(transactionGroup.expense || 0)
        totalSavings += Number(transactionGroup.savings || 0)
      }
    })

    return {
      monthIndex: monthOption.value,
      label: monthOption.label,
      income: totalIncome,
      expense: totalExpense,
      savings: totalSavings
    }
  })
})

/* Dashboard Delete Transaction Url */
const deleteTransactionUrl = computed(() => {
  if (!activeTransaction.value) {
    return '#'
  }

  return `/transactions/${activeTransaction.value.id}`
})

/* Dashboard Set Transaction Group Ref */
const setTransactionGroupRef = (element, dateKey) => {
  if (element) {
    transactionGroupRefs.value[dateKey] = element
    return
  }

  delete transactionGroupRefs.value[dateKey]
}

/* Dashboard Scroll To Transaction Group */
const scrollToTransactionGroup = async (date) => {
  await nextTick()

  const dateKey = formatDateKey(date)
  const targetElement = transactionGroupRefs.value[dateKey]

  if (!targetElement) {
    return
  }

  targetElement.scrollIntoView({
    behavior: 'smooth',
    block: 'start'
  })
}

/* Dashboard Close Panels */
const closePanels = () => {
  isFilterMenuVisible.value = false
  isPeriodMenuVisible.value = false
  isActionModalVisible.value = false
  isViewModalVisible.value = false
  activeTransaction.value = null
  activeTransactionDateLabel.value = ''
}

/* Dashboard Toggle Filter Menu */
const toggleFilterMenu = () => {
  isFilterMenuVisible.value = !isFilterMenuVisible.value
  isPeriodMenuVisible.value = false
  isActionModalVisible.value = false
}

/* Dashboard Toggle Period Menu */
const togglePeriodMenu = () => {
  isPeriodMenuVisible.value = !isPeriodMenuVisible.value
  isFilterMenuVisible.value = false
  isActionModalVisible.value = false
}

/* Dashboard Select Filter */
const selectFilter = (filterOption) => {
  selectedFilter.value = filterOption
  isFilterMenuVisible.value = false
}

/* Dashboard Set Period View */
const setPeriodView = (periodView) => {
  selectedPeriodView.value = periodView

  if (periodView === 'year') {
    displayYear.value = currentDisplayDate.value.getFullYear()
  }
}

/* Dashboard Select Date */
const selectDate = async (date) => {
  selectedDate.value = new Date(date)
  currentDisplayDate.value = new Date(date)
  displayYear.value = currentDisplayDate.value.getFullYear()
  isPeriodMenuVisible.value = false

  await scrollToTransactionGroup(date)
}

/* Dashboard Select Month */
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

  displayYear.value = currentDisplayDate.value.getFullYear()
  isPeriodMenuVisible.value = false
}

/* Dashboard Shift Month Panel Year */
const shiftMonthPanelYear = (shift) => {
  currentDisplayDate.value = new Date(
    currentDisplayDate.value.getFullYear() + shift,
    currentDisplayDate.value.getMonth(),
    1
  )

  selectedDate.value = new Date(
    currentDisplayDate.value.getFullYear(),
    currentDisplayDate.value.getMonth(),
    1
  )

  displayYear.value = currentDisplayDate.value.getFullYear()
}

/* Dashboard Shift Week Panel Month */
const shiftWeekPanelMonth = (shift) => {
  currentDisplayDate.value = new Date(
    currentDisplayDate.value.getFullYear(),
    currentDisplayDate.value.getMonth() + shift,
    1
  )
}

/* Dashboard Select Month From Year */
const selectMonthFromYear = (monthIndex) => {
  currentDisplayDate.value = new Date(displayYear.value, monthIndex, 1)
  selectedDate.value = new Date(displayYear.value, monthIndex, 1)
  selectedPeriodView.value = 'month'
  isPeriodMenuVisible.value = false
}

/* Dashboard Go Previous Period */
const goPreviousPeriod = () => {
  if (selectedPeriodView.value === 'week') {
    const previousWeekDate = new Date(selectedDate.value)

    previousWeekDate.setDate(previousWeekDate.getDate() - 7)

    selectedDate.value = previousWeekDate
    currentDisplayDate.value = new Date(previousWeekDate)
    displayYear.value = currentDisplayDate.value.getFullYear()

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
  displayYear.value = currentDisplayDate.value.getFullYear()
}

/* Dashboard Go Next Period */
const goNextPeriod = () => {
  if (selectedPeriodView.value === 'week') {
    const nextWeekDate = new Date(selectedDate.value)

    nextWeekDate.setDate(nextWeekDate.getDate() + 7)

    selectedDate.value = nextWeekDate
    currentDisplayDate.value = new Date(nextWeekDate)
    displayYear.value = currentDisplayDate.value.getFullYear()

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
  displayYear.value = currentDisplayDate.value.getFullYear()
}

/* Dashboard Open Transaction Action Modal */
const openTransactionActionModal = (transactionItem, transactionDateLabel) => {
  activeTransaction.value = {
    ...transactionItem,
    receiptPhotoUrls: Array.isArray(transactionItem.receiptPhotoUrls)
      ? transactionItem.receiptPhotoUrls
      : []
  }

  activeTransactionDateLabel.value = transactionDateLabel ?? ''
  isActionModalVisible.value = true
  isViewModalVisible.value = false
  isFilterMenuVisible.value = false
  isPeriodMenuVisible.value = false
}

/* Dashboard Open Transaction View Modal */
const openTransactionViewModal = () => {
  isActionModalVisible.value = false
  isViewModalVisible.value = true
}

/* Dashboard Close Transaction View Modal */
const closeTransactionViewModal = () => {
  isViewModalVisible.value = false
}

/* Dashboard Close Transaction Action Modal */
const closeTransactionActionModal = () => {
  isActionModalVisible.value = false
  activeTransaction.value = null
  activeTransactionDateLabel.value = ''
}

/* Dashboard Go To Edit Transaction */
const goToEditTransaction = () => {
  if (!activeTransaction.value) {
    return
  }

  window.location.href = `/transactions/${activeTransaction.value.id}/edit`
}

/* Dashboard Build Date From Key */
const buildDateFromKey = (dateKey) => {
  const [year, month, day] = dateKey.split('-').map(Number)

  return new Date(year, month - 1, day)
}

/* Dashboard Start Of Week */
const startOfWeek = (date) => {
  const nextDate = new Date(date)
  const startIndex = (nextDate.getDay() + 6) % 7

  nextDate.setDate(nextDate.getDate() - startIndex)
  nextDate.setHours(0, 0, 0, 0)

  return nextDate
}

/* Dashboard End Of Week */
const endOfWeek = (date) => {
  const nextDate = startOfWeek(date)

  nextDate.setDate(nextDate.getDate() + 6)
  nextDate.setHours(23, 59, 59, 999)

  return nextDate
}

/* Dashboard Format Date Key */
const formatDateKey = (date) => {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')

  return `${year}-${month}-${day}`
}

/* Dashboard Same Date Check */
const isSameDate = (firstDate, secondDate) => {
  return firstDate.getFullYear() === secondDate.getFullYear()
    && firstDate.getMonth() === secondDate.getMonth()
    && firstDate.getDate() === secondDate.getDate()
}

/* Dashboard Format Money */
const formatMoney = (amount) => {
  return Number(amount || 0).toLocaleString('en-PH')
}

/* Dashboard Format Compact Amount */
const formatCompactAmount = (amount) => {
  return Number(amount || 0).toLocaleString('en-PH')
}

/* Dashboard Format Year Legend Amount */
const formatYearLegendAmount = (amount) => {
  const numericAmount = Number(amount || 0)

  if (numericAmount >= 1000) {
    return `₱${(numericAmount / 1000).toFixed(1)}k`
  }

  return `₱${numericAmount.toLocaleString('en-PH')}`
}

/* Dashboard Format Transaction Type Label */
const formatTransactionTypeLabel = (transactionType) => {
  const typeMap = {
    income: 'Income',
    expense: 'Expense',
    savings: 'Savings'
  }

  return typeMap[transactionType] ?? 'Transaction'
}

/* Dashboard Transaction Icon Class */
const transactionIconClass = (iconColor) => {
  return {
    'sprout-dashboard-mobile__transaction-icon--coral': iconColor === 'coral',
    'sprout-dashboard-mobile__transaction-icon--green': iconColor === 'green',
    'sprout-dashboard-mobile__transaction-icon--blue': iconColor === 'blue'
  }
}

/* Dashboard Transaction Amount Class */
const transactionAmountClass = (transactionType) => {
  return {
    'sprout-dashboard-mobile__transaction-amount--income': transactionType === 'income',
    'sprout-dashboard-mobile__transaction-amount--expense': transactionType === 'expense',
    'sprout-dashboard-mobile__transaction-amount--savings': transactionType === 'savings'
  }
}

/* Dashboard Transaction Display Prefix */
const transactionDisplayPrefix = (transactionType) => {
  return transactionType === 'expense' ? '-' : '+'
}
</script>
