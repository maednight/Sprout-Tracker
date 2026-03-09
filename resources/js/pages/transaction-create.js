/* Selectors */
const transactionSelectors = {
    tabs: '[data-transaction-tab]',
    transactionTitle: '[data-transaction-title]',
    transactionTypeInput: '[data-transaction-type-input]',

    dateTrigger: '[data-date-trigger]',
    dateInput: '#transaction_date',
    dateModal: '[data-date-modal]',
    dateCloseButtons: '[data-date-close]',
    datePrevButton: '[data-date-prev]',
    dateNextButton: '[data-date-next]',
    dateGrid: '[data-date-grid]',
    dateTodayButton: '[data-date-today]',
    dateMonthSelect: '[data-date-month-select]',
    dateYearSelect: '[data-date-year-select]',
    dateIndicator: '[data-date-indicator]',

    amountInput: '#amount',

    categoryTrigger: '[data-category-trigger]',
    categoryInput: '[data-category-input]',
    categorySelectedText: '[data-category-selected-text]',
    categoryModal: '[data-category-modal]',
    categoryCloseButtons: '[data-category-close]',
    categoryGrid: '[data-category-grid]',
    categoryItems: '[data-category-item]',

    accountTrigger: '[data-account-trigger]',
    accountInput: '[data-account-input]',
    accountSelectedText: '[data-account-selected-text]',
    accountModal: '[data-account-modal]',
    accountCloseButtons: '[data-account-close]',
    accountItems: '[data-account-item]',

    addCategoryButton: '.sprout-category-modal__add-button',
    addAccountButton: '.sprout-account-modal__add-button',

    addCategoryOverlay: '[data-add-category-overlay]',
    addCategoryCloseButtons: '[data-add-category-close]',
    addCategoryInput: '[data-add-category-input]',
    addCategorySave: '[data-add-category-save]',
    addCategoryTitle: '[data-add-category-title]',

    addAccountOverlay: '[data-add-account-overlay]',
    addAccountCloseButtons: '[data-add-account-close]',
    addAccountInput: '[data-add-account-input]',
    addAccountSave: '[data-add-account-save]'
}

/* CSS Classes */
const transactionClasses = {
    activeTab: 'sprout-transaction__tab--active',
    hiddenDateModal: 'sprout-date-modal--hidden',
    hiddenCategoryModal: 'sprout-category-modal--hidden',
    selectedCategoryItem: 'sprout-category-modal__item--selected',
    hiddenAccountModal: 'sprout-account-modal--hidden',
    selectedAccountItem: 'sprout-account-modal__item--selected',
    hiddenAddOptionOverlay: 'sprout-add-option-overlay--hidden',
    emptyPickerText: 'sprout-transaction__picker-text--empty'
}

/* Category Options Per Transaction Type */
const categoryOptionsByType = {
    expense: [
        'Food',
        'Transportation',
        'Pets',
        'Culture',
        'Household',
        'Apparel',
        'Beauty',
        'Health',
        'Education',
        'Work',
        'Gift',
        'Others'
    ],
    income: [
        'Allowance',
        'Salary',
        'Petty Cash',
        'Bonus',
        'Others'
    ],
    savings: [
        'Emergency',
        'Retirement',
        'Travel',
        'Education',
        'House',
        'Gadget',
        'Car',
        'Investment',
        'Insurance',
        'Family',
        'Goal',
        'Others'
    ]
}

/* Account Options Per Transaction Type */
const accountOptionsByType = {
    expense: [
        'Cash',
        'Bank',
        'Card'
    ],
    income: [
        'Cash',
        'Bank',
        'Card',
        'Petty Cash'
    ],
    savings: [
        'Bank',
        'Digital Wallet',
        'Cash',
        'Others'
    ]
}

/* Current Transaction Type */
let currentTransactionType = 'expense'

/* Calendar State */
let currentCalendarDate = new Date()

/* Format Peso Currency */
const formatPesoCurrency = (digits) => {
    if (!digits) {
        return ''
    }

    const number = Number(digits)

    if (Number.isNaN(number)) {
        return ''
    }

    return `₱${number.toLocaleString('en-PH')}.00`
}

/* Format Date For Input */
const formatDateForInput = (date) => {
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')
    const year = date.getFullYear()

    return `${month}/${day}/${year}`
}

/* Parse Input Date */
const parseInputDate = (value) => {
    if (!value) {
        return null
    }

    const matchedParts = value.match(/^(\d{2})\/(\d{2})\/(\d{4})$/)

    if (!matchedParts) {
        return null
    }

    const month = Number(matchedParts[1]) - 1
    const day = Number(matchedParts[2])
    const year = Number(matchedParts[3])

    const parsedDate = new Date(year, month, day)

    if (
        parsedDate.getFullYear() !== year ||
        parsedDate.getMonth() !== month ||
        parsedDate.getDate() !== day
    ) {
        return null
    }

    return parsedDate
}

/* Format Visible Date Indicator */
const formatDateIndicator = (date) => {
    return date.toLocaleDateString('en-US', {
        month: 'long',
        year: 'numeric'
    })
}

/* Same Date Check */
const isSameDate = (firstDate, secondDate) => {
    return firstDate.getFullYear() === secondDate.getFullYear()
        && firstDate.getMonth() === secondDate.getMonth()
        && firstDate.getDate() === secondDate.getDate()
}

/* Populate Year Select */
const populateYearSelect = (yearSelectElement, selectedYear) => {
    if (!yearSelectElement) {
        return
    }

    const currentYear = new Date().getFullYear()
    const startYear = currentYear - 20
    const endYear = currentYear + 20

    yearSelectElement.innerHTML = ''

    for (let year = endYear; year >= startYear; year -= 1) {
        const optionElement = document.createElement('option')

        optionElement.value = String(year)
        optionElement.textContent = String(year)

        if (year === selectedYear) {
            optionElement.selected = true
        }

        yearSelectElement.appendChild(optionElement)
    }
}

/* Sync Date Selects */
const syncDateSelects = (monthSelectElement, yearSelectElement, viewDate) => {
    if (monthSelectElement) {
        monthSelectElement.value = String(viewDate.getMonth())
    }

    populateYearSelect(yearSelectElement, viewDate.getFullYear())
}

/* Create Calendar Day Button */
const createCalendarDayButton = (
    date,
    isMuted,
    isSelected,
    isToday,
    inputElement,
    modalElement,
    triggerElement
) => {
    const dayButton = document.createElement('button')

    dayButton.type = 'button'
    dayButton.className = 'sprout-date-modal__day'
    dayButton.textContent = String(date.getDate())

    if (isMuted) {
        dayButton.classList.add('sprout-date-modal__day--muted')
    }

    if (isSelected) {
        dayButton.classList.add('sprout-date-modal__day--selected')
    }

    if (isToday) {
        dayButton.classList.add('sprout-date-modal__day--today')
    }

    if (isSelected && isToday) {
        dayButton.classList.remove('sprout-date-modal__day--today')
    }

    dayButton.addEventListener('click', () => {
        if (inputElement) {
            inputElement.value = formatDateForInput(date)
        }

        closeDateModal(modalElement, triggerElement)
    })

    return dayButton
}

/* Render Calendar */
const renderDateCalendar = (
    viewDate,
    inputElement,
    modalElement,
    triggerElement,
    gridElement,
    monthSelectElement,
    yearSelectElement,
    indicatorElement
) => {
    if (!gridElement) {
        return
    }

    const selectedDate = parseInputDate(inputElement?.value ?? '')
    const today = new Date()

    syncDateSelects(monthSelectElement, yearSelectElement, viewDate)
    gridElement.innerHTML = ''

    if (indicatorElement) {
        indicatorElement.textContent = formatDateIndicator(viewDate)
    }

    const year = viewDate.getFullYear()
    const month = viewDate.getMonth()

    const firstDayOfMonth = new Date(year, month, 1)
    const lastDayOfMonth = new Date(year, month + 1, 0)

    const startDayIndex = firstDayOfMonth.getDay()
    const totalDaysInMonth = lastDayOfMonth.getDate()
    const previousMonthLastDay = new Date(year, month, 0).getDate()

    for (let index = startDayIndex - 1; index >= 0; index -= 1) {
        const date = new Date(year, month - 1, previousMonthLastDay - index)
        const isToday = isSameDate(date, today)
        const isSelected = selectedDate ? isSameDate(date, selectedDate) : false

        gridElement.appendChild(
            createCalendarDayButton(
                date,
                true,
                isSelected,
                isToday,
                inputElement,
                modalElement,
                triggerElement
            )
        )
    }

    for (let day = 1; day <= totalDaysInMonth; day += 1) {
        const date = new Date(year, month, day)
        const isToday = isSameDate(date, today)
        const isSelected = selectedDate ? isSameDate(date, selectedDate) : false

        gridElement.appendChild(
            createCalendarDayButton(
                date,
                false,
                isSelected,
                isToday,
                inputElement,
                modalElement,
                triggerElement
            )
        )
    }

    const renderedCells = gridElement.children.length
    const remainingCells = renderedCells % 7 === 0 ? 0 : 7 - (renderedCells % 7)

    for (let day = 1; day <= remainingCells; day += 1) {
        const date = new Date(year, month + 1, day)
        const isToday = isSameDate(date, today)
        const isSelected = selectedDate ? isSameDate(date, selectedDate) : false

        gridElement.appendChild(
            createCalendarDayButton(
                date,
                true,
                isSelected,
                isToday,
                inputElement,
                modalElement,
                triggerElement
            )
        )
    }
}

/* Initialize Amount Formatter */
const initializeAmountFormatter = () => {
    const amountInput = document.querySelector(transactionSelectors.amountInput)

    if (!amountInput) {
        return
    }

    const initialDigits = (amountInput.value ?? '').replace(/[^\d]/g, '')
    amountInput.dataset.rawDigits = initialDigits

    if (initialDigits) {
        amountInput.value = formatPesoCurrency(initialDigits)
    }

    amountInput.addEventListener('keydown', (event) => {
        const allowedKeys = [
            'Backspace',
            'Delete',
            'Tab',
            'ArrowLeft',
            'ArrowRight',
            'ArrowUp',
            'ArrowDown',
            'Home',
            'End'
        ]

        if (allowedKeys.includes(event.key)) {
            if (event.key === 'Backspace') {
                event.preventDefault()

                const currentDigits = amountInput.dataset.rawDigits ?? ''
                const updatedDigits = currentDigits.slice(0, -1)

                amountInput.dataset.rawDigits = updatedDigits
                amountInput.value = formatPesoCurrency(updatedDigits)
            }

            return
        }

        if (/^\d$/.test(event.key)) {
            event.preventDefault()

            const currentDigits = amountInput.dataset.rawDigits ?? ''
            const updatedDigits = currentDigits + event.key

            amountInput.dataset.rawDigits = updatedDigits
            amountInput.value = formatPesoCurrency(updatedDigits)
        }
    })

    amountInput.addEventListener('paste', (event) => {
        event.preventDefault()

        const pastedText = event.clipboardData?.getData('text') ?? ''
        const pastedDigits = pastedText.replace(/[^\d]/g, '')

        if (!pastedDigits) {
            return
        }

        amountInput.dataset.rawDigits = pastedDigits
        amountInput.value = formatPesoCurrency(pastedDigits)
    })

    amountInput.addEventListener('focus', () => {
        const currentDigits = amountInput.dataset.rawDigits ?? ''
        amountInput.value = formatPesoCurrency(currentDigits)
    })
}

/* Category Title Per Transaction Type */
const getAddCategoryTitle = (transactionType) => {
    const titleMap = {
        expense: 'Add Expense Category',
        income: 'Add Income Category',
        savings: 'Add Savings Category'
    }

    return titleMap[transactionType] ?? 'Add Category'
}

/* Transaction Tabs */
const setActiveTransactionTab = (tabs, selectedTab, titleElement, typeInput) => {
    tabs.forEach((tabElement) => {
        tabElement.classList.remove(transactionClasses.activeTab)
    })

    selectedTab.classList.add(transactionClasses.activeTab)

    currentTransactionType = selectedTab.dataset.transactionType ?? 'expense'

    if (titleElement) {
        titleElement.textContent = selectedTab.dataset.transactionTitle ?? 'Expense'
    }

    if (typeInput) {
        typeInput.value = currentTransactionType
    }
}

/* Date Modal Open */
const openDateModal = (modalElement, triggerElement) => {
    modalElement.classList.remove(transactionClasses.hiddenDateModal)

    if (triggerElement) {
        triggerElement.setAttribute('aria-expanded', 'true')
    }

    document.body.style.overflow = 'hidden'
}

/* Date Modal Close */
const closeDateModal = (modalElement, triggerElement) => {
    modalElement.classList.add(transactionClasses.hiddenDateModal)

    if (triggerElement) {
        triggerElement.setAttribute('aria-expanded', 'false')
    }

    document.body.style.overflow = ''
}

/* Category Modal Open */
const openCategoryModal = (modalElement, triggerElement) => {
    modalElement.classList.remove(transactionClasses.hiddenCategoryModal)

    if (triggerElement) {
        triggerElement.setAttribute('aria-expanded', 'true')
    }

    document.body.style.overflow = 'hidden'
}

/* Category Modal Close */
const closeCategoryModal = (modalElement, triggerElement) => {
    modalElement.classList.add(transactionClasses.hiddenCategoryModal)

    if (triggerElement) {
        triggerElement.setAttribute('aria-expanded', 'false')
    }

    document.body.style.overflow = ''
}

/* Account Modal Open */
const openAccountModal = (modalElement, triggerElement) => {
    modalElement.classList.remove(transactionClasses.hiddenAccountModal)

    if (triggerElement) {
        triggerElement.setAttribute('aria-expanded', 'true')
    }

    document.body.style.overflow = 'hidden'
}

/* Account Modal Close */
const closeAccountModal = (modalElement, triggerElement) => {
    modalElement.classList.add(transactionClasses.hiddenAccountModal)

    if (triggerElement) {
        triggerElement.setAttribute('aria-expanded', 'false')
    }

    document.body.style.overflow = ''
}

/* Add Overlay Open */
const openAddOverlay = (overlayElement) => {
    if (!overlayElement) {
        return
    }

    overlayElement.classList.remove(transactionClasses.hiddenAddOptionOverlay)
    document.body.style.overflow = 'hidden'
}

/* Add Overlay Close */
const closeAddOverlay = (overlayElement) => {
    if (!overlayElement) {
        return
    }

    overlayElement.classList.add(transactionClasses.hiddenAddOptionOverlay)
    document.body.style.overflow = ''
}

/* Clear Category Selection */
const clearSelectedCategory = (inputElement, textElement) => {
    if (inputElement) {
        inputElement.value = ''
    }

    if (textElement) {
        textElement.textContent = ''
        textElement.classList.add(transactionClasses.emptyPickerText)
    }
}

/* Clear Account Selection */
const clearSelectedAccount = (inputElement, textElement) => {
    if (inputElement) {
        inputElement.value = ''
    }

    if (textElement) {
        textElement.textContent = ''
        textElement.classList.add(transactionClasses.emptyPickerText)
    }
}

/* Update Selected Category */
const updateSelectedCategory = (selectedItem, categoryItems, inputElement, textElement) => {
    const categoryName = selectedItem.dataset.categoryName ?? ''

    if (inputElement) {
        inputElement.value = categoryName
    }

    if (textElement) {
        textElement.textContent = categoryName
        textElement.classList.remove(transactionClasses.emptyPickerText)
    }

    categoryItems.forEach((itemElement) => {
        itemElement.classList.remove(transactionClasses.selectedCategoryItem)
    })

    selectedItem.classList.add(transactionClasses.selectedCategoryItem)
}

/* Update Selected Account */
const updateSelectedAccount = (selectedItem, accountItems, inputElement, textElement) => {
    const accountName = selectedItem.dataset.accountName ?? ''

    if (inputElement) {
        inputElement.value = accountName
    }

    if (textElement) {
        textElement.textContent = accountName
        textElement.classList.remove(transactionClasses.emptyPickerText)
    }

    accountItems.forEach((itemElement) => {
        itemElement.classList.remove(transactionClasses.selectedAccountItem)
    })

    selectedItem.classList.add(transactionClasses.selectedAccountItem)
}

/* Create Category Button */
const createCategoryButton = (categoryName) => {
    const buttonElement = document.createElement('button')

    buttonElement.type = 'button'
    buttonElement.className = 'sprout-category-modal__item'
    buttonElement.dataset.categoryItem = ''
    buttonElement.dataset.categoryName = categoryName
    buttonElement.textContent = categoryName

    return buttonElement
}

/* Create Account Button */
const createAccountButton = (accountName) => {
    const buttonElement = document.createElement('button')

    buttonElement.type = 'button'
    buttonElement.className = 'sprout-account-modal__item'
    buttonElement.dataset.accountItem = ''
    buttonElement.dataset.accountName = accountName
    buttonElement.textContent = accountName

    return buttonElement
}

/* Render Category Buttons */
const renderCategoryButtons = (
    transactionType,
    gridElement,
    inputElement,
    textElement,
    modalElement,
    triggerElement
) => {
    if (!gridElement) {
        return
    }

    const categories = categoryOptionsByType[transactionType] ?? []
    const currentSelectedCategory = inputElement?.value ?? ''

    gridElement.innerHTML = ''

    categories.forEach((categoryName) => {
        const buttonElement = createCategoryButton(categoryName)

        if (currentSelectedCategory === categoryName) {
            buttonElement.classList.add(transactionClasses.selectedCategoryItem)
        }

        buttonElement.addEventListener('click', () => {
            const categoryItems = gridElement.querySelectorAll(transactionSelectors.categoryItems)

            updateSelectedCategory(
                buttonElement,
                categoryItems,
                inputElement,
                textElement
            )

            closeCategoryModal(modalElement, triggerElement)
        })

        gridElement.appendChild(buttonElement)
    })

    if (currentSelectedCategory && !categories.includes(currentSelectedCategory)) {
        clearSelectedCategory(inputElement, textElement)
    }
}

/* Render Account Buttons */
const renderAccountButtons = (
    transactionType,
    modalElement,
    triggerElement,
    inputElement,
    textElement
) => {
    const gridElement = modalElement?.querySelector('.sprout-account-modal__grid')

    if (!gridElement) {
        return
    }

    const accounts = accountOptionsByType[transactionType] ?? []
    const currentSelectedAccount = inputElement?.value ?? ''

    gridElement.innerHTML = ''

    accounts.forEach((accountName) => {
        const buttonElement = createAccountButton(accountName)

        if (currentSelectedAccount === accountName) {
            buttonElement.classList.add(transactionClasses.selectedAccountItem)
        }

        buttonElement.addEventListener('click', () => {
            const accountItems = gridElement.querySelectorAll(transactionSelectors.accountItems)

            updateSelectedAccount(
                buttonElement,
                accountItems,
                inputElement,
                textElement
            )

            closeAccountModal(modalElement, triggerElement)
        })

        gridElement.appendChild(buttonElement)
    })

    if (currentSelectedAccount && !accounts.includes(currentSelectedAccount)) {
        clearSelectedAccount(inputElement, textElement)
    }
}

/* Initialize Existing Picker Values */
const initializeExistingPickerValues = () => {
    const categoryInputElement = document.querySelector(transactionSelectors.categoryInput)
    const categoryTextElement = document.querySelector(transactionSelectors.categorySelectedText)
    const accountInputElement = document.querySelector(transactionSelectors.accountInput)
    const accountTextElement = document.querySelector(transactionSelectors.accountSelectedText)

    if (categoryInputElement?.value && categoryTextElement) {
        categoryTextElement.textContent = categoryInputElement.value
        categoryTextElement.classList.remove(transactionClasses.emptyPickerText)
    }

    if (accountInputElement?.value && accountTextElement) {
        accountTextElement.textContent = accountInputElement.value
        accountTextElement.classList.remove(transactionClasses.emptyPickerText)
    }
}

/* Initialize Date Modal */
const initializeDateModal = () => {
    const triggerElement = document.querySelector(transactionSelectors.dateTrigger)
    const inputElement = document.querySelector(transactionSelectors.dateInput)
    const modalElement = document.querySelector(transactionSelectors.dateModal)
    const closeButtons = document.querySelectorAll(transactionSelectors.dateCloseButtons)
    const prevButton = document.querySelector(transactionSelectors.datePrevButton)
    const nextButton = document.querySelector(transactionSelectors.dateNextButton)
    const gridElement = document.querySelector(transactionSelectors.dateGrid)
    const todayButton = document.querySelector(transactionSelectors.dateTodayButton)
    const monthSelectElement = document.querySelector(transactionSelectors.dateMonthSelect)
    const yearSelectElement = document.querySelector(transactionSelectors.dateYearSelect)
    const indicatorElement = document.querySelector(transactionSelectors.dateIndicator)

    if (!triggerElement || !inputElement || !modalElement || !gridElement) {
        return
    }

    const renderCurrentCalendar = () => {
        renderDateCalendar(
            currentCalendarDate,
            inputElement,
            modalElement,
            triggerElement,
            gridElement,
            monthSelectElement,
            yearSelectElement,
            indicatorElement
        )
    }

    const openModalHandler = (event) => {
        event.preventDefault()

        const selectedDate = parseInputDate(inputElement.value)

        currentCalendarDate = selectedDate
            ? new Date(selectedDate.getFullYear(), selectedDate.getMonth(), 1)
            : new Date(new Date().getFullYear(), new Date().getMonth(), 1)

        renderCurrentCalendar()
        openDateModal(modalElement, triggerElement)
    }

    triggerElement.addEventListener('click', openModalHandler)

    triggerElement.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault()
            openModalHandler(event)
        }
    })

    closeButtons.forEach((closeButton) => {
        closeButton.addEventListener('click', () => {
            closeDateModal(modalElement, triggerElement)
        })
    })

    if (prevButton) {
        prevButton.addEventListener('click', () => {
            currentCalendarDate = new Date(
                currentCalendarDate.getFullYear(),
                currentCalendarDate.getMonth() - 1,
                1
            )

            renderCurrentCalendar()
        })
    }

    if (nextButton) {
        nextButton.addEventListener('click', () => {
            currentCalendarDate = new Date(
                currentCalendarDate.getFullYear(),
                currentCalendarDate.getMonth() + 1,
                1
            )

            renderCurrentCalendar()
        })
    }

    if (monthSelectElement) {
        monthSelectElement.addEventListener('change', () => {
            currentCalendarDate = new Date(
                currentCalendarDate.getFullYear(),
                Number(monthSelectElement.value),
                1
            )

            renderCurrentCalendar()
        })
    }

    if (yearSelectElement) {
        yearSelectElement.addEventListener('change', () => {
            currentCalendarDate = new Date(
                Number(yearSelectElement.value),
                currentCalendarDate.getMonth(),
                1
            )

            renderCurrentCalendar()
        })
    }

    if (todayButton) {
        todayButton.addEventListener('click', () => {
            const today = new Date()

            inputElement.value = formatDateForInput(today)
            currentCalendarDate = new Date(today.getFullYear(), today.getMonth(), 1)

            renderCurrentCalendar()
            closeDateModal(modalElement, triggerElement)
        })
    }
}

/* Initialize Transaction Tabs */
const initializeTransactionTabs = (
    categoryGridElement,
    categoryInputElement,
    categoryTextElement,
    categoryModalElement,
    categoryTriggerElement,
    accountInputElement,
    accountTextElement,
    accountModalElement,
    accountTriggerElement
) => {
    const tabs = document.querySelectorAll(transactionSelectors.tabs)
    const titleElement = document.querySelector(transactionSelectors.transactionTitle)
    const typeInput = document.querySelector(transactionSelectors.transactionTypeInput)

    if (!tabs.length) {
        return
    }

    const activeTabElement = document.querySelector(`.${transactionClasses.activeTab}`)

    if (activeTabElement) {
        currentTransactionType = activeTabElement.dataset.transactionType ?? 'expense'
    }

    renderCategoryButtons(
        currentTransactionType,
        categoryGridElement,
        categoryInputElement,
        categoryTextElement,
        categoryModalElement,
        categoryTriggerElement
    )

    renderAccountButtons(
        currentTransactionType,
        accountModalElement,
        accountTriggerElement,
        accountInputElement,
        accountTextElement
    )

    tabs.forEach((tabElement) => {
        tabElement.addEventListener('click', () => {
            setActiveTransactionTab(tabs, tabElement, titleElement, typeInput)

            renderCategoryButtons(
                currentTransactionType,
                categoryGridElement,
                categoryInputElement,
                categoryTextElement,
                categoryModalElement,
                categoryTriggerElement
            )

            renderAccountButtons(
                currentTransactionType,
                accountModalElement,
                accountTriggerElement,
                accountInputElement,
                accountTextElement
            )
        })
    })
}

/* Initialize Category Modal */
const initializeCategoryModal = () => {
    const triggerElement = document.querySelector(transactionSelectors.categoryTrigger)
    const inputElement = document.querySelector(transactionSelectors.categoryInput)
    const textElement = document.querySelector(transactionSelectors.categorySelectedText)
    const modalElement = document.querySelector(transactionSelectors.categoryModal)
    const closeButtons = document.querySelectorAll(transactionSelectors.categoryCloseButtons)
    const gridElement = document.querySelector(transactionSelectors.categoryGrid)

    if (!triggerElement || !modalElement || !gridElement) {
        return {
            categoryGridElement: null,
            categoryInputElement: null,
            categoryTextElement: null,
            categoryModalElement: null,
            categoryTriggerElement: null
        }
    }

    const openModalHandler = (event) => {
        event.preventDefault()

        renderCategoryButtons(
            currentTransactionType,
            gridElement,
            inputElement,
            textElement,
            modalElement,
            triggerElement
        )

        openCategoryModal(modalElement, triggerElement)
    }

    triggerElement.addEventListener('click', openModalHandler)

    triggerElement.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault()
            openModalHandler(event)
        }
    })

    closeButtons.forEach((closeButton) => {
        closeButton.addEventListener('click', () => {
            closeCategoryModal(modalElement, triggerElement)
        })
    })

    return {
        categoryGridElement: gridElement,
        categoryInputElement: inputElement,
        categoryTextElement: textElement,
        categoryModalElement: modalElement,
        categoryTriggerElement: triggerElement
    }
}

/* Initialize Account Modal */
const initializeAccountModal = () => {
    const triggerElement = document.querySelector(transactionSelectors.accountTrigger)
    const inputElement = document.querySelector(transactionSelectors.accountInput)
    const textElement = document.querySelector(transactionSelectors.accountSelectedText)
    const modalElement = document.querySelector(transactionSelectors.accountModal)
    const closeButtons = document.querySelectorAll(transactionSelectors.accountCloseButtons)

    if (!triggerElement || !modalElement) {
        return {
            accountInputElement: null,
            accountTextElement: null,
            accountModalElement: null,
            accountTriggerElement: null
        }
    }

    const openModalHandler = (event) => {
        event.preventDefault()

        renderAccountButtons(
            currentTransactionType,
            modalElement,
            triggerElement,
            inputElement,
            textElement
        )

        openAccountModal(modalElement, triggerElement)
    }

    triggerElement.addEventListener('click', openModalHandler)

    triggerElement.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault()
            openModalHandler(event)
        }
    })

    closeButtons.forEach((closeButton) => {
        closeButton.addEventListener('click', () => {
            closeAccountModal(modalElement, triggerElement)
        })
    })

    return {
        accountInputElement: inputElement,
        accountTextElement: textElement,
        accountModalElement: modalElement,
        accountTriggerElement: triggerElement
    }
}

/* Initialize Add Category Overlay */
const initializeAddCategoryOverlay = (
    categoryGridElement,
    categoryInputElement,
    categoryTextElement,
    categoryModalElement,
    categoryTriggerElement
) => {
    const addButton = document.querySelector(transactionSelectors.addCategoryButton)
    const overlayElement = document.querySelector(transactionSelectors.addCategoryOverlay)
    const closeButtons = document.querySelectorAll(transactionSelectors.addCategoryCloseButtons)
    const inputElement = document.querySelector(transactionSelectors.addCategoryInput)
    const saveButton = document.querySelector(transactionSelectors.addCategorySave)
    const titleElement = document.querySelector(transactionSelectors.addCategoryTitle)

    if (!addButton || !overlayElement || !inputElement || !saveButton) {
        return
    }

    const openOverlay = () => {
        if (titleElement) {
            titleElement.textContent = getAddCategoryTitle(currentTransactionType)
        }

        inputElement.value = ''

        closeCategoryModal(categoryModalElement, categoryTriggerElement)
        openAddOverlay(overlayElement)

        window.setTimeout(() => {
            inputElement.focus()
        }, 50)
    }

    const closeOverlay = () => {
        closeAddOverlay(overlayElement)
        openCategoryModal(categoryModalElement, categoryTriggerElement)
    }

    addButton.addEventListener('click', openOverlay)

    closeButtons.forEach((closeButton) => {
        closeButton.addEventListener('click', closeOverlay)
    })

    saveButton.addEventListener('click', () => {
        const newCategoryName = inputElement.value.trim()

        if (!newCategoryName) {
            return
        }

        const currentCategories = categoryOptionsByType[currentTransactionType] ?? []
        const alreadyExists = currentCategories.some(
            (categoryName) => categoryName.toLowerCase() === newCategoryName.toLowerCase()
        )

        if (!alreadyExists) {
            categoryOptionsByType[currentTransactionType].push(newCategoryName)
        }

        categoryInputElement.value = newCategoryName
        categoryTextElement.textContent = newCategoryName
        categoryTextElement.classList.remove(transactionClasses.emptyPickerText)

        closeAddOverlay(overlayElement)
        openCategoryModal(categoryModalElement, categoryTriggerElement)

        renderCategoryButtons(
            currentTransactionType,
            categoryGridElement,
            categoryInputElement,
            categoryTextElement,
            categoryModalElement,
            categoryTriggerElement
        )
    })
}

/* Initialize Add Account Overlay */
const initializeAddAccountOverlay = (
    accountInputElement,
    accountTextElement,
    accountModalElement,
    accountTriggerElement
) => {
    const addButton = document.querySelector(transactionSelectors.addAccountButton)
    const overlayElement = document.querySelector(transactionSelectors.addAccountOverlay)
    const closeButtons = document.querySelectorAll(transactionSelectors.addAccountCloseButtons)
    const inputElement = document.querySelector(transactionSelectors.addAccountInput)
    const saveButton = document.querySelector(transactionSelectors.addAccountSave)

    if (!addButton || !overlayElement || !inputElement || !saveButton) {
        return
    }

    const openOverlay = () => {
        inputElement.value = ''

        closeAccountModal(accountModalElement, accountTriggerElement)
        openAddOverlay(overlayElement)

        window.setTimeout(() => {
            inputElement.focus()
        }, 50)
    }

    const closeOverlay = () => {
        closeAddOverlay(overlayElement)
        openAccountModal(accountModalElement, accountTriggerElement)
    }

    addButton.addEventListener('click', openOverlay)

    closeButtons.forEach((closeButton) => {
        closeButton.addEventListener('click', closeOverlay)
    })

    saveButton.addEventListener('click', () => {
        const newAccountName = inputElement.value.trim()

        if (!newAccountName) {
            return
        }

        const allAccountLists = Object.values(accountOptionsByType)
        const alreadyExists = allAccountLists.some((accountList) =>
            accountList.some((accountName) => accountName.toLowerCase() === newAccountName.toLowerCase())
        )

        if (!alreadyExists) {
            accountOptionsByType.expense.push(newAccountName)
            accountOptionsByType.income.push(newAccountName)
            accountOptionsByType.savings.push(newAccountName)
        }

        accountInputElement.value = newAccountName
        accountTextElement.textContent = newAccountName
        accountTextElement.classList.remove(transactionClasses.emptyPickerText)

        closeAddOverlay(overlayElement)
        openAccountModal(accountModalElement, accountTriggerElement)

        renderAccountButtons(
            currentTransactionType,
            accountModalElement,
            accountTriggerElement,
            accountInputElement,
            accountTextElement
        )
    })
}

/* Initialize Page */
const initializeTransactionCreatePage = () => {
    initializeAmountFormatter()
    initializeDateModal()
    initializeExistingPickerValues()

    const categoryModalData = initializeCategoryModal()
    const accountModalData = initializeAccountModal()

    initializeTransactionTabs(
        categoryModalData.categoryGridElement,
        categoryModalData.categoryInputElement,
        categoryModalData.categoryTextElement,
        categoryModalData.categoryModalElement,
        categoryModalData.categoryTriggerElement,
        accountModalData.accountInputElement,
        accountModalData.accountTextElement,
        accountModalData.accountModalElement,
        accountModalData.accountTriggerElement
    )

    initializeAddCategoryOverlay(
        categoryModalData.categoryGridElement,
        categoryModalData.categoryInputElement,
        categoryModalData.categoryTextElement,
        categoryModalData.categoryModalElement,
        categoryModalData.categoryTriggerElement
    )

    initializeAddAccountOverlay(
        accountModalData.accountInputElement,
        accountModalData.accountTextElement,
        accountModalData.accountModalElement,
        accountModalData.accountTriggerElement
    )
}

document.addEventListener('DOMContentLoaded', initializeTransactionCreatePage)