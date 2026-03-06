const transactionSelectors = {
    tabs: '[data-transaction-tab]',
    transactionTitle: '[data-transaction-title]',
    transactionTypeInput: '[data-transaction-type-input]',
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
    accountItems: '[data-account-item]'
}

/** Format Peso Currency */
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

/** Initialize Amount Formatter */
const initializeAmountFormatter = () => {
    const amountInput = document.querySelector(transactionSelectors.amountInput)

    if (!amountInput) {
        return
    }

    amountInput.dataset.rawDigits = ''

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

const transactionClasses = {
    activeTab: 'sprout-transaction__tab--active',
    hiddenCategoryModal: 'sprout-category-modal--hidden',
    selectedCategoryItem: 'sprout-category-modal__item--selected',
    hiddenAccountModal: 'sprout-account-modal--hidden',
    selectedAccountItem: 'sprout-account-modal__item--selected',
    emptyPickerText: 'sprout-transaction__picker-text--empty'
}

/** Category Options Per Transaction Type */
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

/** Account Options Per Transaction Type */
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

/** Current Transaction Type */
let currentTransactionType = 'expense'

/** Transaction Tabs */
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

/** Category Modal Open */
const openCategoryModal = (modalElement, triggerElement) => {
    modalElement.classList.remove(transactionClasses.hiddenCategoryModal)

    if (triggerElement) {
        triggerElement.setAttribute('aria-expanded', 'true')
    }

    document.body.style.overflow = 'hidden'
}

/** Category Modal Close */
const closeCategoryModal = (modalElement, triggerElement) => {
    modalElement.classList.add(transactionClasses.hiddenCategoryModal)

    if (triggerElement) {
        triggerElement.setAttribute('aria-expanded', 'false')
    }

    document.body.style.overflow = ''
}

/** Account Modal Open */
const openAccountModal = (modalElement, triggerElement) => {
    modalElement.classList.remove(transactionClasses.hiddenAccountModal)

    if (triggerElement) {
        triggerElement.setAttribute('aria-expanded', 'true')
    }

    document.body.style.overflow = 'hidden'
}

/** Account Modal Close */
const closeAccountModal = (modalElement, triggerElement) => {
    modalElement.classList.add(transactionClasses.hiddenAccountModal)

    if (triggerElement) {
        triggerElement.setAttribute('aria-expanded', 'false')
    }

    document.body.style.overflow = ''
}

/** Clear Category Selection */
const clearSelectedCategory = (inputElement, textElement) => {
    if (inputElement) {
        inputElement.value = ''
    }

    if (textElement) {
        textElement.textContent = ''
        textElement.classList.add(transactionClasses.emptyPickerText)
    }
}

/** Clear Account Selection */
const clearSelectedAccount = (inputElement, textElement) => {
    if (inputElement) {
        inputElement.value = ''
    }

    if (textElement) {
        textElement.textContent = ''
        textElement.classList.add(transactionClasses.emptyPickerText)
    }
}

/** Update Selected Category */
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

/** Update Selected Account */
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

/** Create Category Button */
const createCategoryButton = (categoryName) => {
    const buttonElement = document.createElement('button')

    buttonElement.type = 'button'
    buttonElement.className = 'sprout-category-modal__item'
    buttonElement.dataset.categoryItem = ''
    buttonElement.dataset.categoryName = categoryName
    buttonElement.textContent = categoryName

    return buttonElement
}

/** Create Account Button */
const createAccountButton = (accountName) => {
    const buttonElement = document.createElement('button')

    buttonElement.type = 'button'
    buttonElement.className = 'sprout-account-modal__item'
    buttonElement.dataset.accountItem = ''
    buttonElement.dataset.accountName = accountName
    buttonElement.textContent = accountName

    return buttonElement
}

/** Render Category Buttons */
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

/** Render Account Buttons */
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

/** Initialize Transaction Tabs */
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

/** Initialize Category Modal */
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

/** Initialize Account Modal */
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

/** Initialize Page */
const initializeTransactionCreatePage = () => {
    initializeAmountFormatter()

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
}

document.addEventListener('DOMContentLoaded', initializeTransactionCreatePage)