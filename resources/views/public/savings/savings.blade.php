<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Savings | Sprout Income Expense Tracker</title>
    <link rel="icon" type="image/svg+xml" href="/projectassets/images/logo/sprout-logo.svg">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inknut+Antiqua:wght@400;600;700&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="sprout-font sprout-savings-screen">
    <div class="sprout-appshell">
        <div class="sprout-view sprout-view--mobile">
            <div class="sprout-phone sprout-app sprout-app--mobile">
                <main class="sprout-savings-mobile">
                    @include('public.shared.savings-content', ['savingsScope' => 'mobile'])
                </main>

                @include('public.shared.nav-mobile')
            </div>
        </div>

        <div class="sprout-view sprout-view--desktop">
            <div class="sprout-savings-desktop">
                @include('public.shared.nav-desktop')

                <main class="sprout-savings-desktop__content">
                    @include('public.shared.savings-content', ['savingsScope' => 'desktop'])
                </main>
            </div>
        </div>
    </div>

    <script>
        (() => {
            const hiddenClass = 'sprout-savings__modal--hidden'
            const panels = document.querySelectorAll('[data-savings-panel]')
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

            panels.forEach((panel) => {
                const indexUrl = panel.getAttribute('data-savings-index-url') || '/savings'
                const initialScope = panel.getAttribute('data-savings-scope') || 'month'
                const initialAnchor = panel.getAttribute('data-savings-anchor') || new Date().toISOString().slice(0, 10)
                const historyJson = panel.querySelector('[data-savings-history-json]')
                let allHistoryItems = []

                try {
                    allHistoryItems = JSON.parse(historyJson?.textContent || '[]')
                } catch (error) {
                    allHistoryItems = []
                }
                const backdrop = panel.querySelector('[data-savings-backdrop]')
                const periodTrigger = panel.querySelector('[data-savings-period-trigger]')
                const periodPanel = panel.querySelector('[data-savings-period-panel]')
                const periodViewButtons = panel.querySelectorAll('[data-savings-period-view]')
                const pickerViews = panel.querySelectorAll('[data-savings-picker-view]')
                const weekLabel = panel.querySelector('[data-savings-week-label]')
                const weekGrid = panel.querySelector('[data-savings-week-grid]')
                const monthYear = panel.querySelector('[data-savings-month-year]')
                const monthGrid = panel.querySelector('[data-savings-month-grid]')
                const displayYearValue = panel.querySelector('[data-savings-display-year]')
                const yearGrid = panel.querySelector('[data-savings-year-grid]')
                const worthValue = panel.querySelector('[data-savings-worth-value]')
                const worthToggle = panel.querySelector('[data-savings-worth-toggle]')
                const worthIcon = panel.querySelector('[data-savings-worth-icon]')
                const sortTrigger = panel.querySelector('[data-savings-sort-trigger]')
                const sortMenu = panel.querySelector('[data-savings-sort-menu]')
                const showToggle = panel.querySelector('[data-savings-show-toggle]')
                const showToggleText = panel.querySelector('[data-savings-show-toggle-text]')
                const historyList = panel.querySelector('[data-savings-history-list]')
                const csrfToken = panel.querySelector('[data-savings-csrf-token]')?.value || ''
                const actionModal = panel.querySelector('[data-savings-action-modal]')
                const actionCloseButtons = panel.querySelectorAll('[data-savings-action-close]')
                const actionSubtitle = panel.querySelector('[data-savings-action-subtitle]')
                const actionViewButton = panel.querySelector('[data-savings-action-view]')
                const actionEditButton = panel.querySelector('[data-savings-action-edit]')
                const actionDeleteForm = panel.querySelector('[data-savings-action-delete-form]')
                const actionDeleteButton = panel.querySelector('[data-savings-action-delete-button]')
                const deleteModal = panel.querySelector('[data-savings-delete-modal]')
                const deleteForm = panel.querySelector('[data-savings-delete-form]')
                const deleteTitle = panel.querySelector('[data-savings-delete-title]')
                const deleteMessage = panel.querySelector('[data-savings-delete-message]')
                const deleteCancelButtons = panel.querySelectorAll('[data-savings-delete-cancel]')
                const donut = panel.querySelector('[data-savings-donut]')
                const popup = panel.querySelector('[data-savings-pie-popup]')
                const popupName = panel.querySelector('[data-savings-pie-popup-name]')
                const popupAmount = panel.querySelector('[data-savings-pie-popup-amount]')
                const detailModal = panel.querySelector('[data-savings-detail-modal]')
                const detailCloseButtons = panel.querySelectorAll('[data-savings-detail-close]')
                const detailCategory = panel.querySelector('[data-savings-detail-category]')
                const detailType = panel.querySelector('[data-savings-detail-type]')
                const detailDate = panel.querySelector('[data-savings-detail-date]')
                const detailTime = panel.querySelector('[data-savings-detail-time]')
                const detailAmount = panel.querySelector('[data-savings-detail-amount]')
                const detailAccountRow = panel.querySelector('[data-savings-detail-account-row]')
                const detailAccount = panel.querySelector('[data-savings-detail-account]')
                const detailDescriptionRow = panel.querySelector('[data-savings-detail-description-row]')
                const detailDescription = panel.querySelector('[data-savings-detail-description]')
                const detailPhotosRow = panel.querySelector('[data-savings-detail-photos-row]')
                const detailPhotos = panel.querySelector('[data-savings-detail-photos]')
                const categoryCards = panel.querySelectorAll('[data-savings-category-name]')
                const categoryModal = panel.querySelector('[data-savings-category-modal]')
                const categoryCloseButtons = panel.querySelectorAll('[data-savings-category-close]')
                const categoryTitle = panel.querySelector('[data-savings-category-title]')
                const categoryHistory = panel.querySelector('[data-savings-category-history]')
                const categorySortTrigger = panel.querySelector('[data-savings-category-sort-trigger]')
                const categorySortMenu = panel.querySelector('[data-savings-category-sort-menu]')

                let selectedPeriodView = initialScope
                let currentDisplayDate = new Date(`${initialAnchor}T00:00:00`)
                let selectedDate = new Date(`${initialAnchor}T00:00:00`)
                let displayYear = currentDisplayDate.getFullYear()
                let activeHistoryItem = null
                let activeCategoryHistoryItems = []
                let showHistory = true

                const periodPanelHiddenClass = 'sprout-savings__period-panel--hidden'
                const detailHiddenClass = 'sprout-savings__detail-modal--hidden'
                const actionHiddenClass = 'sprout-savings__action-modal--hidden'
                const deleteHiddenClass = 'sprout-savings__delete-modal--hidden'
                const categoryHiddenClass = 'sprout-savings__category-modal--hidden'

                const pad = (value) => String(value).padStart(2, '0')

                const syncHistoryVisibility = () => {
                    if (historyList) {
                        historyList.hidden = !showHistory
                        historyList.classList.toggle('sprout-savings__history-list--hidden', !showHistory)
                    }

                    if (showToggle) {
                        showToggle.setAttribute('aria-expanded', showHistory ? 'true' : 'false')
                    }

                    if (showToggleText) {
                        showToggleText.textContent = showHistory ? 'show less' : 'show more'
                    }
                }

                const formatDateKey = (date) => {
                    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`
                }

                const isSameDate = (firstDate, secondDate) => {
                    return firstDate.getFullYear() === secondDate.getFullYear()
                        && firstDate.getMonth() === secondDate.getMonth()
                        && firstDate.getDate() === secondDate.getDate()
                }

                const startOfWeek = (date) => {
                    const nextDate = new Date(date)
                    const startIndex = (nextDate.getDay() + 6) % 7
                    nextDate.setDate(nextDate.getDate() - startIndex)
                    nextDate.setHours(0, 0, 0, 0)
                    return nextDate
                }

                const endOfWeek = (date) => {
                    const nextDate = startOfWeek(date)
                    nextDate.setDate(nextDate.getDate() + 6)
                    nextDate.setHours(23, 59, 59, 999)
                    return nextDate
                }

                const buildSavingsUrl = (scope, anchor) => {
                    const nextUrl = new URL(indexUrl, window.location.origin)
                    nextUrl.searchParams.set('scope', scope)
                    nextUrl.searchParams.set('anchor', anchor)
                    return nextUrl.toString()
                }

                const showBackdrop = () => {
                    backdrop?.classList.remove('sprout-savings__backdrop--hidden')
                }

                const hideBackdrop = () => {
                    backdrop?.classList.add('sprout-savings__backdrop--hidden')
                }

                const closePeriodPanel = () => {
                    periodPanel?.classList.add(periodPanelHiddenClass)
                    hideBackdrop()
                }

                const closeActionModal = () => {
                    actionModal?.classList.add(actionHiddenClass)
                }

                const openDeleteModal = () => {
                    if (!activeHistoryItem?.deleteUrl) {
                        return
                    }

                    const deleteLabel = activeHistoryItem.deleteLabel || 'Delete Activity'
                    const deleteItemName = deleteLabel.replace(/^Delete\s+/i, '').toLowerCase()

                    if (deleteTitle) {
                        deleteTitle.textContent = deleteLabel
                    }

                    if (deleteMessage) {
                        deleteMessage.textContent = `Are you sure you want to delete this ${deleteItemName}?`
                    }

                    if (deleteForm) {
                        deleteForm.setAttribute('action', activeHistoryItem.deleteUrl || '#')
                    }

                    actionModal?.classList.add(actionHiddenClass)
                    deleteModal?.classList.remove(deleteHiddenClass)
                }

                const closeDeleteModal = () => {
                    deleteModal?.classList.add(deleteHiddenClass)

                    if (activeHistoryItem?.deleteUrl) {
                        actionModal?.classList.remove(actionHiddenClass)
                    }
                }

                const closeDetailModal = () => {
                    detailModal?.classList.add(detailHiddenClass)
                }

                const sortHistoryItems = (items, sortKey) => {
                    return [...items].sort((leftItem, rightItem) => {
                        const leftTimestamp = Number(leftItem.timestamp || 0)
                        const rightTimestamp = Number(rightItem.timestamp || 0)
                        const leftAmount = Number(leftItem.amount || 0)
                        const rightAmount = Number(rightItem.amount || 0)

                        if (sortKey === 'oldest') {
                            return leftTimestamp - rightTimestamp
                        }

                        if (sortKey === 'highest') {
                            return rightAmount - leftAmount || rightTimestamp - leftTimestamp
                        }

                        if (sortKey === 'lowest') {
                            return leftAmount - rightAmount || rightTimestamp - leftTimestamp
                        }

                        return rightTimestamp - leftTimestamp
                    })
                }

                const renderCategoryHistory = (items) => {
                    if (!categoryHistory) {
                        return
                    }

                    if (!Array.isArray(items) || items.length === 0) {
                        categoryHistory.innerHTML = '<div class="sprout-savings__history-empty">No transactions for this category in the selected period.</div>'
                        return
                    }

                    categoryHistory.innerHTML = items.map((item) => `
                        <button
                            type="button"
                            class="sprout-savings__history-card sprout-savings__history-card--button"
                            data-savings-category-history-item="${encodeURIComponent(JSON.stringify(item))}"
                        >
                            <div class="sprout-savings__history-head">
                                <div class="sprout-savings__history-date">${item.dateLabel || ''}</div>
                                <div class="sprout-savings__history-state sprout-savings__history-state--${item.kind === 'transfer' ? 'transfer' : (item.direction || 'in')}">
                                    ${item.kind === 'transfer' ? 'TRANSFER' : (item.direction === 'out' ? 'OUT' : 'IN')} ₱${Number(item.amount || 0).toLocaleString('en-PH')}
                                </div>
                            </div>

                            <div class="sprout-savings__history-row">
                                <div class="sprout-savings__history-left">
                                    <div class="sprout-savings__history-icon">
                                        <img src="${item.iconPath || '/projectassets/icons/savings.svg'}" alt="${item.category || 'Savings'}" class="sprout-savings__history-icon-image">
                                    </div>

                                    <div class="sprout-savings__history-copy">
                                        <div class="sprout-savings__history-category">${item.category || 'Savings'}</div>
                                        ${item.description ? `<div class="sprout-savings__history-description">Desc: ${item.description}</div>` : ''}
                                    </div>
                                </div>

                                <div class="sprout-savings__history-right">
                                    <div class="sprout-savings__history-amount sprout-savings__history-amount--${item.direction || 'in'}">
                                        ${item.direction === 'out' ? '-' : '+'}₱${Number(item.amount || 0).toLocaleString('en-PH')}
                                    </div>
                                    <div class="sprout-savings__history-time">${item.time || ''}</div>
                                </div>
                            </div>
                        </button>
                    `).join('')

                    categoryHistory.querySelectorAll('[data-savings-category-history-item]').forEach((button) => {
                        button.addEventListener('click', () => {
                            const item = JSON.parse(
                                decodeURIComponent(button.getAttribute('data-savings-category-history-item') || '%7B%7D')
                            )
                            openDetail(item)
                        })
                    })
                }

                const closeCategoryModal = () => {
                    closeDetailModal()
                    categoryModal?.classList.add(categoryHiddenClass)
                    categorySortMenu?.classList.add('sprout-savings__category-filter-menu--hidden')
                }

                const openCategoryModal = (categoryName, items) => {
                    if (!categoryModal || !categoryTitle) {
                        return
                    }

                    activeCategoryHistoryItems = sortHistoryItems(items, 'newest')
                    categoryTitle.textContent = categoryName || 'Category'
                    renderCategoryHistory(activeCategoryHistoryItems)
                    categoryModal.classList.remove(categoryHiddenClass)
                }

                const openDetail = (item) => {
                    if (!detailModal || !detailCategory || !detailType || !detailDate || !detailTime || !detailAmount) {
                        return
                    }

                    detailCategory.textContent = item.category || 'Savings'
                    detailType.textContent = item.typeLabel || 'Savings'
                    detailDate.textContent = item.dateLabel || ''
                    detailTime.textContent = item.time || ''
                    detailAmount.textContent = `${item.direction === 'out' ? '-' : '+'}₱${Number(item.amount || 0).toLocaleString('en-PH')}`
                    detailAmount.style.color = item.direction === 'out' ? '#ff6f5d' : '#00c957'

                    if (detailAccountRow && detailAccount) {
                        const accountValue = item.account || ''
                        detailAccountRow.classList.toggle('sprout-savings__detail-row--hidden', !accountValue)
                        detailAccount.textContent = accountValue
                    }

                    if (detailDescriptionRow && detailDescription) {
                        const descriptionValue = item.description || ''
                        detailDescriptionRow.classList.toggle('sprout-savings__detail-description--hidden', !descriptionValue)
                        detailDescription.textContent = descriptionValue
                    }

                    if (detailPhotosRow && detailPhotos) {
                        const receiptPhotoUrls = Array.isArray(item.receiptPhotoUrls) ? item.receiptPhotoUrls : []
                        detailPhotosRow.classList.toggle('sprout-savings__detail-photos--hidden', receiptPhotoUrls.length === 0)
                        detailPhotos.innerHTML = receiptPhotoUrls.map((receiptPhotoUrl, photoIndex) => `
                            <button
                                type="button"
                                class="sprout-savings__detail-photo-button"
                                aria-label="Open receipt photo ${photoIndex + 1}"
                            >
                                <img src="${receiptPhotoUrl}" alt="Receipt photo ${photoIndex + 1}" class="sprout-savings__detail-photo-image">
                            </button>
                        `).join('')
                    }

                    detailModal.classList.remove(detailHiddenClass)
                }

                const openActionModal = (item) => {
                    if (!actionModal) {
                        return
                    }

                    activeHistoryItem = item

                    if (actionSubtitle) {
                        actionSubtitle.textContent = item.category || 'Savings'
                    }

                    if (actionEditButton) {
                        actionEditButton.textContent = item.editLabel || 'Edit Activity'
                        actionEditButton.disabled = !item.editUrl
                    }

                    if (actionDeleteForm) {
                        actionDeleteForm.setAttribute('action', item.deleteUrl || '#')
                    }

                    if (actionDeleteButton) {
                        actionDeleteButton.textContent = item.deleteLabel || 'Delete Activity'
                        actionDeleteButton.disabled = !item.deleteUrl
                    }

                    actionModal.classList.remove(actionHiddenClass)
                }

                const renderWeekGrid = () => {
                    if (!weekGrid || !weekLabel) {
                        return
                    }

                    weekLabel.textContent = currentDisplayDate.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short'
                    })

                    const monthStart = new Date(currentDisplayDate.getFullYear(), currentDisplayDate.getMonth(), 1)
                    const startIndex = (monthStart.getDay() + 6) % 7
                    const firstVisibleDate = new Date(monthStart)
                    firstVisibleDate.setDate(monthStart.getDate() - startIndex)
                    const activeWeekStart = startOfWeek(selectedDate)
                    const activeWeekEnd = endOfWeek(selectedDate)

                    weekGrid.innerHTML = Array.from({ length: 42 }, (_, index) => {
                        const cellDate = new Date(firstVisibleDate)
                        cellDate.setDate(firstVisibleDate.getDate() + index)

                        const classes = ['sprout-savings__date-chip']

                        if (cellDate.getMonth() !== currentDisplayDate.getMonth()) {
                            classes.push('sprout-savings__date-chip--muted')
                        }

                        if (cellDate >= activeWeekStart && cellDate <= activeWeekEnd) {
                            classes.push('sprout-savings__date-chip--week')
                        }

                        if (isSameDate(cellDate, activeWeekStart)) {
                            classes.push('sprout-savings__date-chip--start')
                        }

                        if (isSameDate(cellDate, activeWeekEnd)) {
                            classes.push('sprout-savings__date-chip--end')
                        }

                        return `<button type="button" class="${classes.join(' ')}" data-savings-select-week="${formatDateKey(cellDate)}">${cellDate.getDate()}</button>`
                    }).join('')
                }

                const renderMonthGrid = () => {
                    if (!monthGrid || !monthYear) {
                        return
                    }

                    monthYear.textContent = String(currentDisplayDate.getFullYear())
                    monthGrid.innerHTML = monthOptions.map((monthOption) => {
                        const classes = ['sprout-savings__month-chip']

                        if (
                            monthOption.value === currentDisplayDate.getMonth()
                            && selectedPeriodView === 'month'
                        ) {
                            classes.push('sprout-savings__month-chip--active')
                        }

                        return `<button type="button" class="${classes.join(' ')}" data-savings-select-month="${monthOption.value}">${monthOption.label}</button>`
                    }).join('')
                }

                const renderYearGrid = () => {
                    if (!yearGrid || !displayYearValue) {
                        return
                    }

                    displayYearValue.textContent = String(displayYear)
                    yearGrid.innerHTML = monthOptions.map((monthOption) => {
                        const classes = ['sprout-savings__month-chip']

                        if (
                            displayYear === currentDisplayDate.getFullYear()
                            && monthOption.value === currentDisplayDate.getMonth()
                        ) {
                            classes.push('sprout-savings__month-chip--active')
                        }

                        return `<button type="button" class="${classes.join(' ')}" data-savings-select-year-month="${monthOption.value}">${monthOption.label}</button>`
                    }).join('')
                }

                const renderPeriodPanel = () => {
                    periodViewButtons.forEach((button) => {
                        button.classList.toggle(
                            'sprout-savings__scope-tab--active',
                            button.getAttribute('data-savings-period-view') === selectedPeriodView
                        )
                    })

                    pickerViews.forEach((view) => {
                        view.classList.toggle(
                            'sprout-savings__picker-view--hidden',
                            view.getAttribute('data-savings-picker-view') !== selectedPeriodView
                        )
                    })

                    renderWeekGrid()
                    renderMonthGrid()
                    renderYearGrid()
                }

                const openPeriodPanel = () => {
                    if (!periodPanel) {
                        return
                    }

                    renderPeriodPanel()
                    periodPanel.classList.remove(periodPanelHiddenClass)
                    showBackdrop()
                }

                if (periodTrigger && periodPanel) {
                    periodTrigger.addEventListener('click', (event) => {
                        event.stopPropagation()
                        if (periodPanel.classList.contains(periodPanelHiddenClass)) {
                            openPeriodPanel()
                            return
                        }

                        closePeriodPanel()
                    })

                    periodViewButtons.forEach((button) => {
                        button.addEventListener('click', () => {
                            selectedPeriodView = button.getAttribute('data-savings-period-view') || 'month'
                            renderPeriodPanel()
                        })
                    })

                    panel.querySelectorAll('[data-savings-week-shift]').forEach((button) => {
                        button.addEventListener('click', () => {
                            const shift = Number(button.getAttribute('data-savings-week-shift') || 0)
                            currentDisplayDate = new Date(currentDisplayDate.getFullYear(), currentDisplayDate.getMonth() + shift, 1)
                            renderWeekGrid()
                        })
                    })

                    panel.querySelectorAll('[data-savings-month-year-shift]').forEach((button) => {
                        button.addEventListener('click', () => {
                            const shift = Number(button.getAttribute('data-savings-month-year-shift') || 0)
                            currentDisplayDate = new Date(currentDisplayDate.getFullYear() + shift, currentDisplayDate.getMonth(), 1)
                            renderMonthGrid()
                        })
                    })

                    panel.querySelectorAll('[data-savings-display-year-shift]').forEach((button) => {
                        button.addEventListener('click', () => {
                            displayYear += Number(button.getAttribute('data-savings-display-year-shift') || 0)
                            renderYearGrid()
                        })
                    })

                    panel.addEventListener('click', (event) => {
                        const weekButton = event.target.closest('[data-savings-select-week]')
                        const monthButton = event.target.closest('[data-savings-select-month]')
                        const yearMonthButton = event.target.closest('[data-savings-select-year-month]')

                        if (weekButton) {
                            window.location.href = buildSavingsUrl('week', weekButton.getAttribute('data-savings-select-week'))
                        }

                        if (monthButton) {
                            const monthIndex = Number(monthButton.getAttribute('data-savings-select-month') || 0)
                            const nextDate = new Date(currentDisplayDate.getFullYear(), monthIndex, 1)
                            window.location.href = buildSavingsUrl('month', formatDateKey(nextDate))
                        }

                        if (yearMonthButton) {
                            const monthIndex = Number(yearMonthButton.getAttribute('data-savings-select-year-month') || 0)
                            const nextDate = new Date(displayYear, monthIndex, 1)
                            window.location.href = buildSavingsUrl('month', formatDateKey(nextDate))
                        }
                    })
                }

                if (worthToggle && worthValue && worthIcon) {
                    const openIcon = '/projectassets/icons/eyeopen.svg'
                    const closeIcon = '/projectassets/icons/eyeclose.svg'
                    const actualValue = worthValue.textContent
                    let isHidden = false

                    worthToggle.addEventListener('click', () => {
                        isHidden = !isHidden
                        worthValue.textContent = isHidden ? '••••••' : actualValue
                        worthIcon.setAttribute('src', isHidden ? closeIcon : openIcon)
                        worthToggle.setAttribute('aria-pressed', String(isHidden))
                        worthToggle.setAttribute('aria-label', isHidden ? 'Show savings worth' : 'Hide savings worth')
                    })
                }

                if (sortTrigger && sortMenu) {
                    const historyContainer = panel.querySelector('.sprout-savings__history')
                    const historyButtons = Array.from(panel.querySelectorAll('[data-savings-history-item]'))

                    const sortHistory = (sortKey) => {
                        if (!historyContainer || !historyButtons.length) {
                            return
                        }

                        const sortedButtons = [...historyButtons].sort((leftButton, rightButton) => {
                            const leftItem = JSON.parse(leftButton.getAttribute('data-savings-history-item') || '{}')
                            const rightItem = JSON.parse(rightButton.getAttribute('data-savings-history-item') || '{}')
                            const leftTimestamp = Number(leftItem.timestamp || 0)
                            const rightTimestamp = Number(rightItem.timestamp || 0)
                            const leftAmount = Number(leftItem.amount || 0)
                            const rightAmount = Number(rightItem.amount || 0)

                            if (sortKey === 'oldest') {
                                return leftTimestamp - rightTimestamp
                            }

                            if (sortKey === 'highest') {
                                return rightAmount - leftAmount || rightTimestamp - leftTimestamp
                            }

                            if (sortKey === 'lowest') {
                                return leftAmount - rightAmount || rightTimestamp - leftTimestamp
                            }

                            return rightTimestamp - leftTimestamp
                        })

                        sortedButtons.forEach((button) => {
                            historyContainer.appendChild(button)
                        })
                    }

                    sortTrigger.addEventListener('click', (event) => {
                        event.stopPropagation()
                        closePeriodPanel()
                        sortMenu.classList.toggle('sprout-savings__activity-filter-menu--hidden')
                    })

                    sortMenu.querySelectorAll('[data-savings-sort]').forEach((button) => {
                        button.addEventListener('click', () => {
                            const sortKey = button.getAttribute('data-savings-sort') || 'newest'
                            sortHistory(sortKey)

                            sortMenu.querySelectorAll('[data-savings-sort]').forEach((option) => {
                                option.classList.toggle(
                                    'sprout-savings__activity-filter-option--active',
                                    option === button
                                )
                            })

                            sortMenu.classList.add('sprout-savings__activity-filter-menu--hidden')
                        })
                    })
                }

                if (detailModal && detailCategory && detailType && detailDate && detailTime && detailAmount) {
                    const detailHiddenClass = 'sprout-savings__detail-modal--hidden'
                    const openDetail = (item) => {
                        detailCategory.textContent = item.category || 'Savings'
                        detailType.textContent = item.typeLabel || 'Savings'
                        detailDate.textContent = item.dateLabel || ''
                        detailTime.textContent = item.time || ''
                        detailAmount.textContent = `${item.direction === 'out' ? '-' : '+'}₱${Number(item.amount || 0).toLocaleString('en-PH')}`
                        detailAmount.style.color = item.direction === 'out' ? '#ff6f5d' : '#00c957'

                        if (detailAccountRow && detailAccount) {
                            const accountValue = item.account || ''
                            detailAccountRow.classList.toggle('sprout-savings__detail-row--hidden', !accountValue)
                            detailAccount.textContent = accountValue
                        }

                        if (detailDescriptionRow && detailDescription) {
                            const descriptionValue = item.description || ''
                            detailDescriptionRow.classList.toggle('sprout-savings__detail-description--hidden', !descriptionValue)
                            detailDescription.textContent = descriptionValue
                        }

                        if (detailPhotosRow && detailPhotos) {
                            const receiptPhotoUrls = Array.isArray(item.receiptPhotoUrls) ? item.receiptPhotoUrls : []
                            detailPhotosRow.classList.toggle('sprout-savings__detail-photos--hidden', receiptPhotoUrls.length === 0)
                            detailPhotos.innerHTML = receiptPhotoUrls.map((receiptPhotoUrl, photoIndex) => `
                                <button
                                    type="button"
                                    class="sprout-savings__detail-photo-button"
                                    aria-label="Open receipt photo ${photoIndex + 1}"
                                >
                                    <img src="${receiptPhotoUrl}" alt="Receipt photo ${photoIndex + 1}" class="sprout-savings__detail-photo-image">
                                </button>
                            `).join('')
                        }

                        detailModal.classList.remove(detailHiddenClass)
                    }

                    panel.querySelectorAll('[data-savings-history-item]').forEach((button) => {
                        button.addEventListener('click', () => {
                            const item = JSON.parse(button.getAttribute('data-savings-history-item') || '{}')
                            openActionModal(item)
                        })
                    })

                    detailCloseButtons.forEach((button) => {
                        button.addEventListener('click', () => {
                            detailModal.classList.add(detailHiddenClass)
                        })
                    })
                }

                actionCloseButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        closeActionModal()
                    })
                })

                actionViewButton?.addEventListener('click', () => {
                    if (!activeHistoryItem) {
                        return
                    }

                    closeActionModal()
                    openDetail(activeHistoryItem)
                })

                actionEditButton?.addEventListener('click', () => {
                    if (!activeHistoryItem?.editUrl) {
                        return
                    }

                    window.location.href = activeHistoryItem.editUrl
                })

                actionDeleteButton?.addEventListener('click', () => {
                    if (!csrfToken || !activeHistoryItem?.deleteUrl) {
                        return
                    }

                    openDeleteModal()
                })

                deleteCancelButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        closeDeleteModal()
                    })
                })

                    deleteForm?.addEventListener('submit', (event) => {
                    if (!csrfToken || !activeHistoryItem?.deleteUrl) {
                        event.preventDefault()
                    }
                })

                categoryCards.forEach((button) => {
                    const openCategoryCard = () => {
                        const categoryName = button.getAttribute('data-savings-category-name') || 'Category'
                        const items = Array.isArray(allHistoryItems)
                            ? allHistoryItems.filter((item) => (item.category || '') === categoryName)
                            : []

                        openCategoryModal(categoryName, items)
                    }

                    button.addEventListener('click', openCategoryCard)
                })

                categoryCloseButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        closeCategoryModal()
                    })
                })

                categorySortTrigger?.addEventListener('click', (event) => {
                    event.stopPropagation()
                    categorySortMenu?.classList.toggle('sprout-savings__category-filter-menu--hidden')
                })

                categorySortMenu?.querySelectorAll('[data-savings-category-sort]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const sortKey = button.getAttribute('data-savings-category-sort') || 'newest'
                        activeCategoryHistoryItems = sortHistoryItems(activeCategoryHistoryItems, sortKey)
                        renderCategoryHistory(activeCategoryHistoryItems)

                        categorySortMenu.querySelectorAll('[data-savings-category-sort]').forEach((option) => {
                            option.classList.toggle(
                                'sprout-savings__category-filter-option--active',
                                option === button
                            )
                        })

                        categorySortMenu.classList.add('sprout-savings__category-filter-menu--hidden')
                    })
                })

                showToggle?.addEventListener('click', () => {
                    showHistory = !showHistory
                    syncHistoryVisibility()
                })

                syncHistoryVisibility()

                if (donut && popup && popupName && popupAmount) {
                    const categories = JSON.parse(donut.getAttribute('data-savings-categories') || '[]')
                    const total = categories.reduce((sum, category) => sum + Number(category.amount || 0), 0)

                    const hidePopup = () => {
                        popup.classList.add('sprout-savings__pie-popup--hidden')
                    }

                    const showPopup = (category) => {
                        popupName.textContent = category.name || 'Savings'
                        popupAmount.textContent = `₱${Number(category.amount || 0).toLocaleString('en-PH')}`
                        popupAmount.style.color = category.color || '#2d9af0'
                        popup.classList.remove('sprout-savings__pie-popup--hidden')
                    }

                    donut.addEventListener('click', (event) => {
                        if (!Array.isArray(categories) || !categories.length || total <= 0) {
                            hidePopup()
                            return
                        }

                        const rect = donut.getBoundingClientRect()
                        const centerX = rect.left + (rect.width / 2)
                        const centerY = rect.top + (rect.height / 2)
                        const dx = event.clientX - centerX
                        const dy = event.clientY - centerY
                        const distance = Math.sqrt((dx * dx) + (dy * dy))
                        const outerRadius = rect.width / 2
                        const innerRadius = outerRadius - 36

                        if (distance < innerRadius || distance > outerRadius) {
                            hidePopup()
                            return
                        }

                        let angle = Math.atan2(dy, dx) * (180 / Math.PI) + 90

                        if (angle < 0) {
                            angle += 360
                        }

                        let cursor = 0
                        const selectedCategory = categories.find((category) => {
                            const portion = total > 0 ? (Number(category.amount || 0) / total) * 360 : 0
                            const start = cursor
                            const end = cursor + portion
                            cursor = end
                            return angle >= start && angle < end
                        })

                        if (!selectedCategory) {
                            hidePopup()
                            return
                        }

                        showPopup(selectedCategory)
                    })

                    document.addEventListener('click', (event) => {
                        if (!panel.contains(event.target) || (!donut.contains(event.target) && !popup.contains(event.target))) {
                            hidePopup()
                        }

                        if (sortMenu && sortTrigger && !sortMenu.contains(event.target) && !sortTrigger.contains(event.target)) {
                            sortMenu.classList.add('sprout-savings__activity-filter-menu--hidden')
                        }

                        if (periodPanel && periodTrigger && !periodPanel.contains(event.target) && !periodTrigger.contains(event.target)) {
                            closePeriodPanel()
                        }

                        if (categorySortMenu && categorySortTrigger && !categorySortMenu.contains(event.target) && !categorySortTrigger.contains(event.target)) {
                            categorySortMenu.classList.add('sprout-savings__category-filter-menu--hidden')
                        }

                    })
                }

                backdrop?.addEventListener('click', () => {
                    closePeriodPanel()
                })

                document.addEventListener('click', (event) => {
                    if (categorySortMenu && categorySortTrigger && !categorySortMenu.contains(event.target) && !categorySortTrigger.contains(event.target)) {
                        categorySortMenu.classList.add('sprout-savings__category-filter-menu--hidden')
                    }
                })
            })
        })()
    </script>
</body>
</html>
