import './bootstrap'
import './transactions/transaction-create'
import './savings/savings-transfer-create'

import { createApp } from 'vue'
import DashboardHome from './home/DashboardHome.vue'

const dashboardElements = document.querySelectorAll('[data-dashboard-app]')

dashboardElements.forEach((dashboardElement) => {
    let dashboardPayload = {
        transactionGroups: [],
        initialDisplayDate: null
    }

    let csrfToken = ''
    let flashSuccess = ''
    let flashSuccessType = ''

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

    csrfToken = dashboardElement.getAttribute('data-csrf-token') || ''
    try {
        const rawFlashSuccess = dashboardElement.getAttribute('data-flash-success')
        flashSuccess = rawFlashSuccess ? JSON.parse(rawFlashSuccess) ?? '' : ''
    } catch (error) {
        console.error('Dashboard flash success parse error:', error)
        flashSuccess = ''
    }

    try {
        const rawFlashSuccessType = dashboardElement.getAttribute('data-flash-success-type')
        flashSuccessType = rawFlashSuccessType ? JSON.parse(rawFlashSuccessType) ?? '' : ''
    } catch (error) {
        console.error('Dashboard flash success type parse error:', error)
        flashSuccessType = ''
    }

    createApp(DashboardHome, {
        initialTransactionGroups: dashboardPayload.transactionGroups ?? [],
        initialDisplayDate: dashboardPayload.initialDisplayDate ?? null,
        csrfToken,
        initialSuccessMessage: flashSuccess,
        initialSuccessType: flashSuccessType
    }).mount(dashboardElement)
})

document.addEventListener('DOMContentLoaded', () => {
    const passwordToggleButtons = document.querySelectorAll('[data-password-toggle]')

    passwordToggleButtons.forEach((toggleButton) => {
        const handleToggle = (event) => {
            event.preventDefault()
            event.stopPropagation()

            const passwordWrapper = toggleButton.closest('.sprout-settings-mobile__password-wrap')
            const passwordInput = passwordWrapper?.querySelector('.sprout-settings-mobile__input--password')
            const passwordIcon = toggleButton.querySelector('.sprout-settings-mobile__password-toggle-icon')

            if (!passwordInput || !passwordIcon) {
                return
            }

            const isHidden = passwordInput.type === 'password'

            passwordInput.type = isHidden ? 'text' : 'password'
            passwordIcon.src = isHidden
                ? '/projectassets/icons/eyeopen.svg'
                : '/projectassets/icons/eyeclose.svg'
            passwordIcon.alt = isHidden ? 'Visible password' : 'Hidden password'
            toggleButton.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password')
        }

        toggleButton.addEventListener('click', handleToggle)
        toggleButton.addEventListener('touchstart', handleToggle, { passive: false })
    })

    const settingsAlerts = document.querySelectorAll('.sprout-settings-mobile__alert')

    if (settingsAlerts.length > 0) {
        window.setTimeout(() => {
            settingsAlerts.forEach((alertElement) => {
                alertElement.style.transition = 'opacity 0.3s ease'
                alertElement.style.opacity = '0'

                window.setTimeout(() => {
                    alertElement.remove()
                }, 300)
            })
        }, 3500)
    }
})
