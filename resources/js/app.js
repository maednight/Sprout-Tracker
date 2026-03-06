import './bootstrap'
import './pages/transaction-create'

import { createApp } from 'vue'
import DashboardHome from './pages/DashboardHome.vue'

const dashboardElement = document.querySelector('#app')

if (dashboardElement) {
    createApp(DashboardHome).mount('#app')
}