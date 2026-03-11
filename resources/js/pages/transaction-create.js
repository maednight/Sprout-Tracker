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
    addAccountSave: '[data-add-account-save]',

    photoTrigger: '[data-photo-trigger]',
    photoModal: '[data-photo-modal]',
    photoCloseButtons: '[data-photo-close]',
    photoCameraButton: '[data-photo-camera-button]',
    photoGalleryButton: '[data-photo-gallery-button]',
    photoCameraInput: '[data-photo-camera-input]',
    photoGalleryInput: '[data-photo-gallery-input]',
    photoPreviewWrapper: '[data-photo-preview-wrapper]',
    photoRemoveExistingButton: '[data-photo-remove-existing]',
    existingPhotoPathInput: '[data-existing-photo-path]',
    photoViewer: '[data-photo-viewer]',
    photoViewerCloseButtons: '[data-photo-viewer-close]',
    photoViewerImage: '[data-photo-viewer-image]',
    photoPreviewImages: '[data-photo-preview-image]'
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
    emptyPickerText: 'sprout-transaction__picker-text--empty',
    hiddenPhotoModal: 'sprout-photo-modal--hidden',
    hiddenPhotoPreview: 'sprout-transaction__photo-preview-list--hidden',
    hiddenPhotoViewer: 'sprout-photo-viewer--hidden'
}

/* Storage Keys */
const transactionStorageKeys = {
    categories: 'sprout_custom_categories',
    accounts: 'sprout_custom_accounts'
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

/* Default Category Options */
const defaultCategoryOptionsByType = {
    expense: [...categoryOptionsByType.expense],
    income: [...categoryOptionsByType.income],
    savings: [...categoryOptionsByType.savings]
}

/* Default Account Options */
const defaultAccountOptionsByType = {
    expense: [...accountOptionsByType.expense],
    income: [...accountOptionsByType.income],
    savings: [...accountOptionsByType.savings]
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

/* Parse Whole Amount Digits */
const parseWholeAmountDigits = (value) => {
    if (!value) {
        return ''
    }

    const cleanedValue = String(value)
        .replace(/₱/g, '')
        .replace(/,/g, '')
        .trim()

    if (!cleanedValue) {
        return ''
    }

    const numericValue = Number(cleanedValue)

    if (!Number.isNaN(numericValue)) {
        return String(Math.trunc(numericValue))
    }

    return cleanedValue.replace(/[^\d]/g, '')
}

/* Read Storage JSON */
const readStorageJson = (storageKey, fallbackValue) => {
    try {
        const storedValue = window.localStorage.getItem(storageKey)

        if (!storedValue) {
            return fallbackValue
        }

        const parsedValue = JSON.parse(storedValue)

        return parsedValue ?? fallbackValue
    } catch (error) {
        return fallbackValue
    }
}

/* Write Storage JSON */
const writeStorageJson = (storageKey, value) => {
    try {
        window.localStorage.setItem(storageKey, JSON.stringify(value))
    } catch (error) {
        console.error(`Unable to save ${storageKey}:`, error)
    }
}

/* Is Default Category */
const isDefaultCategory = (transactionType, categoryName) => {
    const defaultCategories = defaultCategoryOptionsByType[transactionType] ?? []

    return defaultCategories.includes(categoryName)
}

/* Is Default Account */
const isDefaultAccount = (transactionType, accountName) => {
    const defaultAccounts = defaultAccountOptionsByType[transactionType] ?? []

    return defaultAccounts.includes(accountName)
}

/* Load Stored Categories */
const loadStoredCategories = () => {
    const storedCategories = readStorageJson(transactionStorageKeys.categories, {})

    Object.keys(categoryOptionsByType).forEach((transactionType) => {
        const defaultCategories = defaultCategoryOptionsByType[transactionType] ?? []
        const customCategories = Array.isArray(storedCategories[transactionType])
            ? storedCategories[transactionType]
            : []

        categoryOptionsByType[transactionType] = [
            ...defaultCategories,
            ...customCategories.filter((categoryName) => !defaultCategories.includes(categoryName))
        ]
    })
}

/* Save Stored Categories */
const saveStoredCategories = () => {
    const customCategoriesByType = {}

    Object.keys(categoryOptionsByType).forEach((transactionType) => {
        const defaultCategories = defaultCategoryOptionsByType[transactionType] ?? []
        const currentCategories = categoryOptionsByType[transactionType] ?? []

        customCategoriesByType[transactionType] = currentCategories.filter(
            (categoryName) => !defaultCategories.includes(categoryName)
        )
    })

    writeStorageJson(transactionStorageKeys.categories, customCategoriesByType)
}

/* Load Stored Accounts */
const loadStoredAccounts = () => {
    const storedAccounts = readStorageJson(transactionStorageKeys.accounts, [])

    const uniqueStoredAccounts = Array.isArray(storedAccounts)
        ? [...new Set(storedAccounts)]
        : []

    Object.keys(accountOptionsByType).forEach((transactionType) => {
        const defaultAccounts = defaultAccountOptionsByType[transactionType] ?? []

        accountOptionsByType[transactionType] = [
            ...defaultAccounts,
            ...uniqueStoredAccounts.filter((accountName) => !defaultAccounts.includes(accountName))
        ]
    })
}

/* Save Stored Accounts */
const saveStoredAccounts = () => {
    const mergedAccounts = new Set()

    Object.keys(accountOptionsByType).forEach((transactionType) => {
        const defaultAccounts = defaultAccountOptionsByType[transactionType] ?? []
        const currentAccounts = accountOptionsByType[transactionType] ?? []

        currentAccounts.forEach((accountName) => {
            if (!defaultAccounts.includes(accountName)) {
                mergedAccounts.add(accountName)
            }
        })
    })

    writeStorageJson(transactionStorageKeys.accounts, [...mergedAccounts])
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

    const initialDigits = parseWholeAmountDigits(amountInput.value ?? '')

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
        const pastedDigits = parseWholeAmountDigits(pastedText)

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

/* Photo Modal Open */
const openPhotoModal = (modalElement) => {
    if (!modalElement) {
        return
    }

    modalElement.classList.remove(transactionClasses.hiddenPhotoModal)
    document.body.style.overflow = 'hidden'
}

/* Photo Modal Close */
const closePhotoModal = (modalElement) => {
    if (!modalElement) {
        return
    }

    modalElement.classList.add(transactionClasses.hiddenPhotoModal)
    document.body.style.overflow = ''
}

/* Photo Viewer Open */
const openPhotoViewer = (viewerElement, imageElement, imageSource, imageAlt = 'Photo preview') => {
    if (!viewerElement || !imageElement || !imageSource) {
        return
    }

    imageElement.src = imageSource
    imageElement.alt = imageAlt
    viewerElement.classList.remove(transactionClasses.hiddenPhotoViewer)
    document.body.style.overflow = 'hidden'
}

/* Photo Viewer Close */
const closePhotoViewer = (viewerElement, imageElement) => {
    if (!viewerElement || !imageElement) {
        return
    }

    viewerElement.classList.add(transactionClasses.hiddenPhotoViewer)
    imageElement.src = ''
    imageElement.alt = 'Large photo preview'
    document.body.style.overflow = ''
}

/* Bind Photo Preview Viewer Events */
const bindPhotoPreviewViewerEvents = () => {
    const viewerElement = document.querySelector(transactionSelectors.photoViewer)
    const viewerImageElement = document.querySelector(transactionSelectors.photoViewerImage)
    const closeButtons = document.querySelectorAll(transactionSelectors.photoViewerCloseButtons)
    const previewImages = document.querySelectorAll(transactionSelectors.photoPreviewImages)

    if (!viewerElement || !viewerImageElement || !previewImages.length) {
        return
    }

    previewImages.forEach((previewImage) => {
        previewImage.style.cursor = 'pointer'

        previewImage.addEventListener('click', () => {
            openPhotoViewer(
                viewerElement,
                viewerImageElement,
                previewImage.getAttribute('src'),
                previewImage.getAttribute('alt') || 'Photo preview'
            )
        })
    })

    closeButtons.forEach((closeButton) => {
        closeButton.addEventListener('click', () => {
            closePhotoViewer(viewerElement, viewerImageElement)
        })
    })
}

/* Clear Selected Category */
const clearSelectedCategory = (inputElement, textElement) => {
    if (inputElement) {
        inputElement.value = ''
    }

    if (textElement) {
        textElement.textContent = ''
        textElement.classList.add(transactionClasses.emptyPickerText)
    }
}

/* Clear Selected Account */
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
const createCategoryButton = (transactionType, categoryName) => {
    const buttonElement = document.createElement('button')
    const labelElement = document.createElement('span')

    buttonElement.type = 'button'
    buttonElement.className = 'sprout-category-modal__item'
    buttonElement.dataset.categoryItem = ''
    buttonElement.dataset.categoryName = categoryName

    labelElement.textContent = categoryName
    buttonElement.appendChild(labelElement)

    if (!isDefaultCategory(transactionType, categoryName)) {
        const removeButton = document.createElement('button')

        removeButton.type = 'button'
        removeButton.className = 'sprout-category-modal__remove'
        removeButton.textContent = '-'
        removeButton.setAttribute('aria-label', `Remove ${categoryName}`)

        removeButton.addEventListener('click', (event) => {
            event.preventDefault()
            event.stopPropagation()

            categoryOptionsByType[transactionType] = (categoryOptionsByType[transactionType] ?? []).filter(
                (itemName) => itemName !== categoryName
            )

            saveStoredCategories()

            const categoryInputElement = document.querySelector(transactionSelectors.categoryInput)
            const categoryTextElement = document.querySelector(transactionSelectors.categorySelectedText)
            const categoryModalElement = document.querySelector(transactionSelectors.categoryModal)
            const categoryTriggerElement = document.querySelector(transactionSelectors.categoryTrigger)
            const categoryGridElement = document.querySelector(transactionSelectors.categoryGrid)

            if (categoryInputElement?.value === categoryName) {
                clearSelectedCategory(categoryInputElement, categoryTextElement)
            }

            renderCategoryButtons(
                transactionType,
                categoryGridElement,
                categoryInputElement,
                categoryTextElement,
                categoryModalElement,
                categoryTriggerElement
            )
        })

        buttonElement.appendChild(removeButton)
    }

    return buttonElement
}

/* Create Account Button */
const createAccountButton = (transactionType, accountName) => {
    const buttonElement = document.createElement('button')
    const labelElement = document.createElement('span')

    buttonElement.type = 'button'
    buttonElement.className = 'sprout-account-modal__item'
    buttonElement.dataset.accountItem = ''
    buttonElement.dataset.accountName = accountName

    labelElement.textContent = accountName
    buttonElement.appendChild(labelElement)

    if (!isDefaultAccount(transactionType, accountName)) {
        const removeButton = document.createElement('button')

        removeButton.type = 'button'
        removeButton.className = 'sprout-account-modal__remove'
        removeButton.textContent = '-'
        removeButton.setAttribute('aria-label', `Remove ${accountName}`)

        removeButton.addEventListener('click', (event) => {
            event.preventDefault()
            event.stopPropagation()

            Object.keys(accountOptionsByType).forEach((typeKey) => {
                accountOptionsByType[typeKey] = (accountOptionsByType[typeKey] ?? []).filter(
                    (itemName) => itemName !== accountName
                )
            })

            saveStoredAccounts()

            const accountInputElement = document.querySelector(transactionSelectors.accountInput)
            const accountTextElement = document.querySelector(transactionSelectors.accountSelectedText)
            const accountModalElement = document.querySelector(transactionSelectors.accountModal)
            const accountTriggerElement = document.querySelector(transactionSelectors.accountTrigger)

            if (accountInputElement?.value === accountName) {
                clearSelectedAccount(accountInputElement, accountTextElement)
            }

            renderAccountButtons(
                transactionType,
                accountModalElement,
                accountTriggerElement,
                accountInputElement,
                accountTextElement
            )
        })

        buttonElement.appendChild(removeButton)
    }

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
        const buttonElement = createCategoryButton(transactionType, categoryName)

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
        const buttonElement = createAccountButton(transactionType, accountName)

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

/* Photo File State */
let selectedNewPhotoItems = []
let nextPhotoItemId = 0

/* Create Photo Preview Item */
const createPhotoPreviewItemElement = (imageSource, altText, removeHandler) => {
    const itemElement = document.createElement('div')
    const imageElement = document.createElement('img')
    const removeButton = document.createElement('button')

    itemElement.className = 'sprout-transaction__photo-preview-item'

    imageElement.className = 'sprout-transaction__photo-preview-image'
    imageElement.src = imageSource
    imageElement.alt = altText
    imageElement.setAttribute('data-photo-preview-image', '')

    removeButton.type = 'button'
    removeButton.className = 'sprout-transaction__photo-remove'
    removeButton.setAttribute('aria-label', `Remove ${altText}`)
    removeButton.textContent = '×'

    removeButton.addEventListener('click', removeHandler)

    itemElement.appendChild(imageElement)
    itemElement.appendChild(removeButton)

    return itemElement
}

/* Sync Existing Photo Paths Input */
const syncExistingPhotoPathsInput = (existingPhotoPathsInput, existingPhotoPaths) => {
    if (!existingPhotoPathsInput) {
        return
    }

    existingPhotoPathsInput.value = JSON.stringify(existingPhotoPaths)
}

/* Sync Gallery Input Files */
const syncGalleryInputFiles = (galleryInput, photoItems) => {
    if (!galleryInput) {
        return
    }

    const dataTransfer = new DataTransfer()

    photoItems.forEach((photoItem) => {
        dataTransfer.items.add(photoItem.file)
    })

    galleryInput.files = dataTransfer.files
}

/* Render Photo Previews */
const renderPhotoPreviews = (
    previewWrapper,
    galleryInput,
    existingPhotoPathsInput,
    existingPhotoPaths
) => {
    if (!previewWrapper) {
        return
    }

    previewWrapper.innerHTML = ''

    const hasExistingPhotos = existingPhotoPaths.length > 0
    const hasNewPhotos = selectedNewPhotoItems.length > 0

    if (!hasExistingPhotos && !hasNewPhotos) {
        previewWrapper.classList.add(transactionClasses.hiddenPhotoPreview)
        return
    }

    previewWrapper.classList.remove(transactionClasses.hiddenPhotoPreview)

    existingPhotoPaths.forEach((photoPath) => {
        const previewItemElement = createPhotoPreviewItemElement(
            `/storage/${photoPath}`,
            'Receipt preview',
            () => {
                const updatedExistingPhotoPaths = existingPhotoPaths.filter(
                    (currentPhotoPath) => currentPhotoPath !== photoPath
                )

                syncExistingPhotoPathsInput(existingPhotoPathsInput, updatedExistingPhotoPaths)
                renderPhotoPreviews(
                    previewWrapper,
                    galleryInput,
                    existingPhotoPathsInput,
                    updatedExistingPhotoPaths
                )
            }
        )

        previewWrapper.appendChild(previewItemElement)
    })

    selectedNewPhotoItems.forEach((photoItem) => {
        const previewItemElement = createPhotoPreviewItemElement(
            photoItem.previewUrl,
            photoItem.file.name,
            () => {
                URL.revokeObjectURL(photoItem.previewUrl)

                selectedNewPhotoItems = selectedNewPhotoItems.filter(
                    (currentPhotoItem) => currentPhotoItem.id !== photoItem.id
                )

                syncGalleryInputFiles(galleryInput, selectedNewPhotoItems)
                renderPhotoPreviews(
                    previewWrapper,
                    galleryInput,
                    existingPhotoPathsInput,
                    existingPhotoPaths
                )
            }
        )

        previewWrapper.appendChild(previewItemElement)
    })
}

/* Append New Photo Files */
const appendNewPhotoFiles = (
    files,
    galleryInput,
    previewWrapper,
    existingPhotoPathsInput,
    existingPhotoPaths
) => {
    if (!files.length) {
        return
    }

    const newPhotoItems = files.map((file) => ({
        id: nextPhotoItemId++,
        file,
        previewUrl: URL.createObjectURL(file)
    }))

    selectedNewPhotoItems = [
        ...selectedNewPhotoItems,
        ...newPhotoItems
    ]

    syncGalleryInputFiles(galleryInput, selectedNewPhotoItems)
    renderPhotoPreviews(
        previewWrapper,
        galleryInput,
        existingPhotoPathsInput,
        existingPhotoPaths
    )
}

/* Initialize Photo Upload */
const initializePhotoUpload = () => {
    const triggerElement = document.querySelector(transactionSelectors.photoTrigger)
    const modalElement = document.querySelector(transactionSelectors.photoModal)
    const closeButtons = document.querySelectorAll(transactionSelectors.photoCloseButtons)
    const cameraButton = document.querySelector(transactionSelectors.photoCameraButton)
    const galleryButton = document.querySelector(transactionSelectors.photoGalleryButton)
    const cameraInput = document.querySelector(transactionSelectors.photoCameraInput)
    const galleryInput = document.querySelector(transactionSelectors.photoGalleryInput)
    const previewWrapper = document.querySelector(transactionSelectors.photoPreviewWrapper)
    const existingPhotoPathsInput = document.querySelector('[data-existing-photo-paths]')

    if (!triggerElement || !modalElement || !cameraInput || !galleryInput || !previewWrapper) {
        return
    }

    let existingPhotoPaths = []

    try {
        const rawExistingPhotoPaths = existingPhotoPathsInput?.value ?? '[]'
        const parsedExistingPhotoPaths = JSON.parse(rawExistingPhotoPaths)

        existingPhotoPaths = Array.isArray(parsedExistingPhotoPaths)
            ? parsedExistingPhotoPaths
            : []
    } catch (error) {
        existingPhotoPaths = []
    }

    renderPhotoPreviews(
        previewWrapper,
        galleryInput,
        existingPhotoPathsInput,
        existingPhotoPaths
    )

    triggerElement.addEventListener('click', () => {
        openPhotoModal(modalElement)
    })

    closeButtons.forEach((closeButton) => {
        closeButton.addEventListener('click', () => {
            closePhotoModal(modalElement)
        })
    })

    if (cameraButton) {
        cameraButton.addEventListener('click', () => {
            closePhotoModal(modalElement)

            const isMobileDevice = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent)

            if (isMobileDevice) {
                cameraInput.setAttribute('capture', '')
            } else {
                cameraInput.removeAttribute('capture')
            }

            cameraInput.click()
        })
    }

    if (galleryButton) {
        galleryButton.addEventListener('click', () => {
            closePhotoModal(modalElement)

            galleryInput.removeAttribute('capture')

            galleryInput.click()
        })
    }

    cameraInput.addEventListener('change', () => {
        const selectedFiles = [...(cameraInput.files ?? [])]

        appendNewPhotoFiles(
            selectedFiles,
            galleryInput,
            previewWrapper,
            existingPhotoPathsInput,
            existingPhotoPaths
        )

        cameraInput.value = ''
    })

    galleryInput.addEventListener('change', () => {
        const selectedFiles = [...(galleryInput.files ?? [])]

        const existingFileKeys = new Set(
            selectedNewPhotoItems.map((photoItem) => {
                const file = photoItem.file
                return `${file.name}-${file.size}-${file.lastModified}`
            })
        )

        const deduplicatedFiles = selectedFiles.filter((file) => {
            const fileKey = `${file.name}-${file.size}-${file.lastModified}`

            if (existingFileKeys.has(fileKey)) {
                return false
            }

            existingFileKeys.add(fileKey)
            return true
        })

        appendNewPhotoFiles(
            deduplicatedFiles,
            galleryInput,
            previewWrapper,
            existingPhotoPathsInput,
            existingPhotoPaths
        )
    })
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
            saveStoredCategories()
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
            saveStoredAccounts()
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
    loadStoredCategories()
    loadStoredAccounts()

    initializeAmountFormatter()
    initializeDateModal()
    initializeExistingPickerValues()
    initializePhotoUpload()
    bindPhotoPreviewViewerEvents()

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