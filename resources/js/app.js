/* App Entry */

import './bootstrap'
import './pages/transaction-create'

import { createApp } from 'vue'
import DashboardHome from './pages/DashboardHome.vue'

/* Dashboard Mount */
const dashboardElement = document.querySelector('#app')

if (dashboardElement) {
  let dashboardPayload = {
    transactionGroups: [],
    initialDisplayDate: null
  }

  try {
    const rawDashboardPayload = dashboardElement.getAttribute('data-dashboard')
    dashboardPayload = rawDashboardPayload
      ? JSON.parse(rawDashboardPayload)
      : {
          transactionGroups: [],
          initialDisplayDate: null
        }
  } catch (error) {
    console.error('Dashboard payload parse error:', error)

    dashboardPayload = {
      transactionGroups: [],
      initialDisplayDate: null
    }
  }

  createApp(DashboardHome, {
    initialTransactionGroups: dashboardPayload.transactionGroups ?? [],
    initialDisplayDate: dashboardPayload.initialDisplayDate ?? null
  }).mount('#app')
}