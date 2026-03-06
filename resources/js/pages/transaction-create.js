const transactionSelectors = {
    tabs: '[data-transaction-tab]',
    transactionTitle: '[data-transaction-title]',
    transactionTypeInput: '[data-transaction-type-input]',
    categoryTrigger: '[data-category-trigger]',
    categoryInput: '[data-category-input]',
    categorySelectedText: '[data-category-selected-text]',
    categoryModal: '[data-category-modal]',
    categoryCloseButtons: '[data-category-close]',
    categoryItems: '[data-category-item]'
}

const transactionClasses = {
    activeTab: 'sprout-transaction__tab--active',
    hiddenModal: 'sprout-category-modal--hidden',
    selectedCategoryItem: 'sprout-category-modal__item--selected',
    emptyPickerText: 'sprout-transaction__picker-text--empty'
}

const setActiveTransactionTab = (tabs, selectedTab, titleElement, typeInput) => {
    tabs.forEach((tabElement) => {
        tabElement.classList.remove(transactionClasses.activeTab)
    })

    selectedTab.classList.add(transactionClasses.activeTab)

    if (titleElement) {
        titleElement.textContent = selectedTab.dataset.transactionTitle ?? 'Expense'
    }

    if (typeInput) {
        typeInput.value = selectedTab.dataset.transactionType ?? 'expense'
    }
}

const openCategoryModal = (modalElement, triggerElement) => {
    modalElement.classList.remove(transactionClasses.hiddenModal)

    if (triggerElement) {
        triggerElement.setAttribute('aria-expanded', 'true')
    }

    document.body.style.overflow = 'hidden'
}

const closeCategoryModal = (modalElement, triggerElement) => {
    modalElement.classList.add(transactionClasses.hiddenModal)

    if (triggerElement) {
        triggerElement.setAttribute('aria-expanded', 'false')
    }

    document.body.style.overflow = ''
}

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

const initializeTransactionTabs = () => {
    const tabs = document.querySelectorAll(transactionSelectors.tabs)
    const titleElement = document.querySelector(transactionSelectors.transactionTitle)
    const typeInput = document.querySelector(transactionSelectors.transactionTypeInput)

    if (!tabs.length) {
        return
    }

    tabs.forEach((tabElement) => {
        tabElement.addEventListener('click', () => {
            setActiveTransactionTab(tabs, tabElement, titleElement, typeInput)
        })
    })
}

const initializeCategoryModal = () => {
    const triggerElement = document.querySelector(transactionSelectors.categoryTrigger)
    const inputElement = document.querySelector(transactionSelectors.categoryInput)
    const textElement = document.querySelector(transactionSelectors.categorySelectedText)
    const modalElement = document.querySelector(transactionSelectors.categoryModal)
    const closeButtons = document.querySelectorAll(transactionSelectors.categoryCloseButtons)
    const categoryItems = document.querySelectorAll(transactionSelectors.categoryItems)

    if (!triggerElement || !modalElement) {
        return
    }

    const openModalHandler = (event) => {
        event.preventDefault()
        openCategoryModal(modalElement, triggerElement)
    }

    triggerElement.addEventListener('click', openModalHandler)

    triggerElement.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault()
            openCategoryModal(modalElement, triggerElement)
        }
    })

    closeButtons.forEach((closeButton) => {
        closeButton.addEventListener('click', () => {
            closeCategoryModal(modalElement, triggerElement)
        })
    })

    categoryItems.forEach((itemElement) => {
        itemElement.addEventListener('click', () => {
            updateSelectedCategory(itemElement, categoryItems, inputElement, textElement)
            closeCategoryModal(modalElement, triggerElement)
        })
    })
}

const initializeTransactionCreatePage = () => {
    initializeTransactionTabs()
    initializeCategoryModal()
}

document.addEventListener('DOMContentLoaded', initializeTransactionCreatePage)