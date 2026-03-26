const transferRoot = document.querySelector('[data-savings-transfer-page]')

if (transferRoot) {
    const selectors = {
        form: '[data-transfer-form]',
        submit: '[data-transfer-submit]',
        validation: '[data-transfer-validation]',
        transferTypeInput: '[data-transfer-type-input]',
        transferTypeOptions: '[data-transfer-type-option]',
        dateTrigger: '[data-transfer-date-trigger]',
        dateInput: '#transfer_date',
        dateModal: '[data-transfer-date-modal]',
        dateClose: '[data-transfer-date-close]',
        datePrev: '[data-transfer-date-prev]',
        dateNext: '[data-transfer-date-next]',
        dateGrid: '[data-transfer-date-grid]',
        dateToday: '[data-transfer-date-today]',
        dateMonthSelect: '[data-transfer-date-month-select]',
        dateYearSelect: '[data-transfer-date-year-select]',
        dateIndicator: '[data-transfer-date-indicator]',
        amountInput: '#transfer_amount',
        categoryTrigger: '[data-transfer-category-trigger]',
        categoryInput: '[data-transfer-category-input]',
        destinationCategoryInput: '[data-transfer-destination-category-input]',
        categoryText: '[data-transfer-category-text]',
        categoryAmount: '[data-transfer-category-amount]',
        accountAmount: '[data-transfer-account-amount]',
        categoryModal: '[data-transfer-category-modal]',
        categoryModalTitle: '[data-transfer-category-modal-title]',
        categoryClose: '[data-transfer-category-close]',
        categoryItems: '[data-transfer-category-item]',
        accountTrigger: '[data-transfer-account-trigger]',
        accountInput: '[data-transfer-account-input]',
        accountText: '[data-transfer-account-text]',
        accountModal: '[data-transfer-account-modal]',
        accountModalTitle: '[data-transfer-account-modal-title]',
        accountClose: '[data-transfer-account-close]',
        accountGrid: '[data-transfer-account-grid]',
        accountItems: '[data-transfer-account-item]',
        photoTrigger: '[data-transfer-photo-trigger]',
        photoModal: '[data-transfer-photo-modal]',
        photoCloseButtons: '[data-transfer-photo-close]',
        photoCameraButton: '[data-transfer-photo-camera-button]',
        photoGalleryButton: '[data-transfer-photo-gallery-button]',
        photoCameraInput: '[data-transfer-photo-camera-input]',
        photoGalleryInput: '[data-transfer-photo-gallery-input]',
        photoPreviewWrapper: '[data-transfer-photo-preview-wrapper]',
        existingPhotoPathsInput: '[data-transfer-existing-photo-paths]',
        photoPreviewImages: '[data-transfer-photo-preview-image]',
        photoViewer: '[data-transfer-photo-viewer]',
        photoViewerCloseButtons: '[data-transfer-photo-viewer-close]',
        photoViewerImage: '[data-transfer-photo-viewer-image]'
    }

    const classes = {
        hiddenDateModal: 'sprout-date-modal--hidden',
        hiddenCategoryModal: 'sprout-category-modal--hidden',
        hiddenAccountModal: 'sprout-account-modal--hidden',
        selectedCategory: 'sprout-category-modal__item--selected',
        selectedAccount: 'sprout-account-modal__item--selected',
        emptyPickerText: 'sprout-transaction__picker-text--empty',
        hiddenValidation: 'sprout-transaction__validation--hidden',
        hiddenCategoryMeta: 'sprout-savings-transfer__selector-meta--hidden',
        hiddenPhotoModal: 'sprout-photo-modal--hidden',
        hiddenPhotoPreview: 'sprout-transaction__photo-preview-list--hidden',
        hiddenPhotoViewer: 'sprout-photo-viewer--hidden'
    }

    const rootPhone = document.querySelector('.sprout-phone.sprout-savings-transfer')
    const transferTypeLocked = rootPhone?.getAttribute('data-transfer-type-locked') === 'true'
    const transferCategories = (() => {
        try {
            const rawValue = rootPhone?.getAttribute('data-transfer-categories') || '[]'
            return JSON.parse(rawValue)
        } catch (error) {
            return []
        }
    })()
    const initialAccounts = (() => {
        try {
            const rawValue = rootPhone?.getAttribute('data-transfer-account-options') || '[]'
            return JSON.parse(rawValue)
        } catch (error) {
            return []
        }
    })()

    let currentCalendarDate = new Date()
    let accountOptions = [...new Set(initialAccounts)].filter(Boolean)
    let selectedNewPhotoItems = []
    let nextPhotoItemId = 0
    let activeCategoryTrigger = null
    let activeAccountTrigger = null
    let activeCategoryField = 'source'

    const getTransferType = () => {
        return document.querySelector(selectors.transferTypeInput)?.value || rootPhone?.getAttribute('data-transfer-type-value') || 'savings_to_savings'
    }

    const isSavingsToSavings = () => getTransferType() === 'savings_to_savings'
    const isSavingsWithdraw = () => getTransferType() === 'savings_withdraw'

    const getSelectedCategory = (inputSelector = selectors.categoryInput) => {
        const categoryId = String(document.querySelector(inputSelector)?.value || '')
        return transferCategories.find((category) => String(category.categoryId) === categoryId) || null
    }

    const setSelectorValue = (element, value) => {
        if (!element) {
            return
        }

        const resolvedValue = String(value || '').trim()
        element.textContent = resolvedValue || 'Select'
        element.classList.toggle('sprout-savings-transfer__selector-value--empty', resolvedValue === '')
    }

    const setSelectorMeta = (element, amountValue, shouldShow) => {
        if (!element) {
            return
        }

        const numericAmount = Number(amountValue || 0)

        if (shouldShow && numericAmount > 0) {
            element.textContent = `\u20B1${numericAmount.toLocaleString('en-PH')} available`
            element.classList.remove(classes.hiddenCategoryMeta)
            return
        }

        element.textContent = ''
        element.classList.add(classes.hiddenCategoryMeta)
    }

    const syncTransferTypeButtons = () => {
        document.querySelectorAll(selectors.transferTypeOptions).forEach((button) => {
            const isActive = button.getAttribute('data-transfer-type') === getTransferType()
            button.classList.toggle('sprout-savings-transfer__type-option--active', isActive)
            button.setAttribute('aria-pressed', isActive ? 'true' : 'false')
            button.disabled = transferTypeLocked
        })
    }

    const syncTransferFlow = () => {
        const categoryTrigger = document.querySelector(selectors.categoryTrigger)
        const accountTrigger = document.querySelector(selectors.accountTrigger)
        const flow = document.querySelector('[data-transfer-flow]')
        const swap = document.querySelector('[data-transfer-swap]')
        const targetSelector = document.querySelector('[data-transfer-target-selector]')
        const categoryLabel = categoryTrigger?.querySelector('.sprout-savings-transfer__selector-label')
        const accountLabel = accountTrigger?.querySelector('.sprout-savings-transfer__selector-label')
        const categoryText = document.querySelector(selectors.categoryText)
        const accountText = document.querySelector(selectors.accountText)
        const categoryAmount = document.querySelector(selectors.categoryAmount)
        const accountAmount = document.querySelector(selectors.accountAmount)
        const categoryModalTitle = document.querySelector(selectors.categoryModalTitle)
        const accountModalTitle = document.querySelector(selectors.accountModalTitle)
        const selectedCategory = getSelectedCategory(selectors.categoryInput)
        const selectedDestinationCategory = getSelectedCategory(selectors.destinationCategoryInput)
        const selectedAccount = document.querySelector(selectors.accountInput)?.value || ''

        if (categoryLabel) categoryLabel.textContent = 'From'
        setSelectorValue(categoryText, selectedCategory?.name || '')
        setSelectorMeta(categoryAmount, selectedCategory?.amount || 0, Boolean(selectedCategory))
        flow?.classList.toggle('sprout-savings-transfer__flow--withdraw', isSavingsWithdraw())
        swap?.classList.toggle('sprout-savings-transfer__swap--hidden', isSavingsWithdraw())
        targetSelector?.classList.toggle('sprout-savings-transfer__selector--hidden', isSavingsWithdraw())

        if (isSavingsToSavings()) {
            if (accountLabel) accountLabel.textContent = 'To'
            setSelectorValue(accountText, selectedDestinationCategory?.name || '')
            setSelectorMeta(accountAmount, 0, false)
            if (categoryModalTitle) {
                categoryModalTitle.textContent = activeCategoryField === 'destination'
                    ? 'Destination Savings Category'
                    : 'Source Savings Category'
            }
            if (accountModalTitle) accountModalTitle.textContent = 'Savings Category'
        } else if (isSavingsWithdraw()) {
            if (accountLabel) accountLabel.textContent = 'Withdraw'
            setSelectorValue(accountText, '')
            setSelectorMeta(accountAmount, 0, false)
            if (categoryModalTitle) categoryModalTitle.textContent = 'Savings Category'
            if (accountModalTitle) accountModalTitle.textContent = 'Withdraw'
        } else {
            if (accountLabel) accountLabel.textContent = 'To'
            setSelectorValue(accountText, selectedAccount)
            setSelectorMeta(accountAmount, 0, false)
            if (categoryModalTitle) categoryModalTitle.textContent = 'Source Savings Category'
            if (accountModalTitle) accountModalTitle.textContent = 'Income Account'
        }

        syncTransferTypeButtons()
    }

    const formatDateForInput = (date) => {
        const month = String(date.getMonth() + 1).padStart(2, '0')
        const day = String(date.getDate()).padStart(2, '0')
        const year = date.getFullYear()
        return `${month}/${day}/${year}`
    }

    const parseInputDate = (value) => {
        if (!value) return null
        const matchedParts = String(value).match(/^(\d{2})\/(\d{2})\/(\d{4})$/)
        if (!matchedParts) return null
        const parsedDate = new Date(Number(matchedParts[3]), Number(matchedParts[1]) - 1, Number(matchedParts[2]))
        return Number.isNaN(parsedDate.getTime()) ? null : parsedDate
    }

    const formatDateIndicator = (date) => {
        return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
    }

    const formatPesoCurrency = (digits) => {
        if (!digits) {
            return ''
        }

        const number = Number(digits)

        if (Number.isNaN(number)) {
            return ''
        }

        return `\u20B1${number.toLocaleString('en-PH')}.00`
    }

    const parseWholeAmountDigits = (value) => {
        if (!value) {
            return ''
        }

        const cleanedValue = String(value)
            .replace(/\u20B1/g, '')
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

    const isSameDate = (firstDate, secondDate) => {
        return firstDate.getFullYear() === secondDate.getFullYear()
            && firstDate.getMonth() === secondDate.getMonth()
            && firstDate.getDate() === secondDate.getDate()
    }

    const populateYearSelect = (yearSelectElement, selectedYear) => {
        if (!yearSelectElement) return
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

    const showValidation = (message) => {
        const validationElement = document.querySelector(selectors.validation)
        if (!validationElement) return
        validationElement.textContent = message
        validationElement.classList.remove(classes.hiddenValidation)
    }

    const hideValidation = () => {
        const validationElement = document.querySelector(selectors.validation)
        if (!validationElement) return
        validationElement.textContent = ''
        validationElement.classList.add(classes.hiddenValidation)
    }

    const isFormComplete = () => {
        const dateInput = document.querySelector(selectors.dateInput)
        const amountInput = document.querySelector(selectors.amountInput)
        const categoryInput = document.querySelector(selectors.categoryInput)
        const destinationCategoryInput = document.querySelector(selectors.destinationCategoryInput)
        const accountInput = document.querySelector(selectors.accountInput)

        return Boolean(dateInput?.value?.trim())
            && Boolean(parseWholeAmountDigits(amountInput?.value ?? ''))
            && Boolean(categoryInput?.value?.trim())
            && (isSavingsWithdraw()
                ? true
                : isSavingsToSavings()
                ? Boolean(destinationCategoryInput?.value?.trim())
                : Boolean(accountInput?.value?.trim()))
    }

    const syncSubmitState = () => {
        const submitButton = document.querySelector(selectors.submit)
        if (submitButton) {
            submitButton.disabled = !isFormComplete()
        }
    }

    const openModal = (modalElement, triggerElement) => {
        modalElement?.classList.remove(classes.hiddenDateModal, classes.hiddenCategoryModal, classes.hiddenAccountModal)
        triggerElement?.setAttribute('aria-expanded', 'true')
    }

    const closeModal = (modalElement, triggerElement, hiddenClass) => {
        modalElement?.classList.add(hiddenClass)
        triggerElement?.setAttribute('aria-expanded', 'false')
    }

    const openPhotoModal = (modalElement) => {
        if (!modalElement) return
        modalElement.classList.remove(classes.hiddenPhotoModal)
        document.body.style.overflow = 'hidden'
    }

    const closePhotoModal = (modalElement) => {
        if (!modalElement) return
        modalElement.classList.add(classes.hiddenPhotoModal)
        document.body.style.overflow = ''
    }

    const openPhotoViewer = (viewerElement, imageElement, imageSource, imageAlt = 'Photo preview') => {
        if (!viewerElement || !imageElement || !imageSource) return
        imageElement.src = imageSource
        imageElement.alt = imageAlt
        viewerElement.classList.remove(classes.hiddenPhotoViewer)
        document.body.style.overflow = 'hidden'
    }

    const closePhotoViewer = (viewerElement, imageElement) => {
        if (!viewerElement || !imageElement) return
        viewerElement.classList.add(classes.hiddenPhotoViewer)
        imageElement.src = ''
        imageElement.alt = 'Large photo preview'
        document.body.style.overflow = ''
    }

    const createPhotoPreviewItemElement = (imageSource, altText, removeHandler) => {
        const itemElement = document.createElement('div')
        const imageElement = document.createElement('img')
        const removeButton = document.createElement('button')

        itemElement.className = 'sprout-transaction__photo-preview-item'
        imageElement.className = 'sprout-transaction__photo-preview-image'
        imageElement.src = imageSource
        imageElement.alt = altText
        imageElement.setAttribute('data-transfer-photo-preview-image', '')

        removeButton.type = 'button'
        removeButton.className = 'sprout-transaction__photo-remove'
        removeButton.setAttribute('aria-label', `Remove ${altText}`)
        removeButton.textContent = '×'
        removeButton.addEventListener('click', removeHandler)

        itemElement.appendChild(imageElement)
        itemElement.appendChild(removeButton)

        return itemElement
    }

    const syncExistingPhotoPathsInput = (existingPhotoPathsInput, existingPhotoPaths) => {
        if (!existingPhotoPathsInput) return
        existingPhotoPathsInput.value = JSON.stringify(existingPhotoPaths)
    }

    const syncGalleryInputFiles = (galleryInput, photoItems) => {
        if (!galleryInput) return

        const dataTransfer = new DataTransfer()
        photoItems.forEach((photoItem) => {
            dataTransfer.items.add(photoItem.file)
        })
        galleryInput.files = dataTransfer.files
    }

    const bindPhotoPreviewViewerEvents = () => {
        const viewerElement = document.querySelector(selectors.photoViewer)
        const viewerImageElement = document.querySelector(selectors.photoViewerImage)
        const closeButtons = document.querySelectorAll(selectors.photoViewerCloseButtons)
        const previewImages = document.querySelectorAll(selectors.photoPreviewImages)

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

    const renderPhotoPreviews = (previewWrapper, galleryInput, existingPhotoPathsInput, existingPhotoPaths) => {
        if (!previewWrapper) return

        previewWrapper.innerHTML = ''

        const hasExistingPhotos = existingPhotoPaths.length > 0
        const hasNewPhotos = selectedNewPhotoItems.length > 0

        if (!hasExistingPhotos && !hasNewPhotos) {
            previewWrapper.classList.add(classes.hiddenPhotoPreview)
            return
        }

        previewWrapper.classList.remove(classes.hiddenPhotoPreview)

        existingPhotoPaths.forEach((photoPath) => {
            const previewItemElement = createPhotoPreviewItemElement(
                `/storage/${photoPath}`,
                'Receipt preview',
                () => {
                    const updatedExistingPhotoPaths = existingPhotoPaths.filter(
                        (currentPhotoPath) => currentPhotoPath !== photoPath
                    )

                    syncExistingPhotoPathsInput(existingPhotoPathsInput, updatedExistingPhotoPaths)
                    renderPhotoPreviews(previewWrapper, galleryInput, existingPhotoPathsInput, updatedExistingPhotoPaths)
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
                    selectedNewPhotoItems = selectedNewPhotoItems.filter((currentPhotoItem) => currentPhotoItem.id !== photoItem.id)
                    syncGalleryInputFiles(galleryInput, selectedNewPhotoItems)
                    renderPhotoPreviews(previewWrapper, galleryInput, existingPhotoPathsInput, existingPhotoPaths)
                }
            )

            previewWrapper.appendChild(previewItemElement)
        })

        bindPhotoPreviewViewerEvents()
    }

    const appendNewPhotoFiles = (files, galleryInput, previewWrapper, existingPhotoPathsInput, existingPhotoPaths) => {
        if (!files.length) return

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
        renderPhotoPreviews(previewWrapper, galleryInput, existingPhotoPathsInput, existingPhotoPaths)
    }

    const updateSelectedCategoryAmount = (amountValue) => {
        syncTransferFlow()
    }

    const renderAccountButtons = () => {
        const accountGrid = document.querySelector(selectors.accountGrid)
        const selectedAccount = document.querySelector(selectors.accountInput)?.value || ''

        if (!accountGrid) return

        accountGrid.innerHTML = accountOptions.map((accountName) => `
            <button
                type="button"
                class="sprout-account-modal__item ${selectedAccount === accountName ? classes.selectedAccount : ''}"
                data-transfer-account-item
                data-transfer-account-name="${accountName}"
            >
                ${accountName}
            </button>
        `).join('')

        accountGrid.querySelectorAll(selectors.accountItems).forEach((button) => {
            button.addEventListener('click', () => {
                const accountName = button.getAttribute('data-transfer-account-name') || ''
                const accountInput = document.querySelector(selectors.accountInput)
                const accountText = document.querySelector(selectors.accountText)
                const accountModal = document.querySelector(selectors.accountModal)
                const accountTrigger = document.querySelector(selectors.accountTrigger)

                if (accountInput) accountInput.value = accountName
                if (accountText) {
                    accountText.textContent = accountName
                    accountText.classList.remove(classes.emptyPickerText)
                }

                syncTransferFlow()
                syncSubmitState()
                closeModal(accountModal, activeAccountTrigger || accountTrigger, classes.hiddenAccountModal)
                activeAccountTrigger = null
            })
        })
    }

    const renderCalendar = () => {
        const inputElement = document.querySelector(selectors.dateInput)
        const gridElement = document.querySelector(selectors.dateGrid)
        const monthSelectElement = document.querySelector(selectors.dateMonthSelect)
        const yearSelectElement = document.querySelector(selectors.dateYearSelect)
        const indicatorElement = document.querySelector(selectors.dateIndicator)

        if (!inputElement || !gridElement) return

        const selectedDate = parseInputDate(inputElement.value)
        const today = new Date()

        if (indicatorElement) {
            indicatorElement.textContent = formatDateIndicator(currentCalendarDate)
        }

        if (monthSelectElement) {
            monthSelectElement.value = String(currentCalendarDate.getMonth())
        }

        populateYearSelect(yearSelectElement, currentCalendarDate.getFullYear())
        gridElement.innerHTML = ''

        const firstDayOfMonth = new Date(currentCalendarDate.getFullYear(), currentCalendarDate.getMonth(), 1)
        const startOffset = firstDayOfMonth.getDay()
        const firstVisibleDate = new Date(firstDayOfMonth)
        firstVisibleDate.setDate(firstVisibleDate.getDate() - startOffset)

        Array.from({ length: 42 }, (_, index) => {
            const cellDate = new Date(firstVisibleDate)
            cellDate.setDate(firstVisibleDate.getDate() + index)

            const dayButton = document.createElement('button')
            dayButton.type = 'button'
            dayButton.className = 'sprout-date-modal__day'
            dayButton.textContent = String(cellDate.getDate())

            if (cellDate.getMonth() !== currentCalendarDate.getMonth()) {
                dayButton.classList.add('sprout-date-modal__day--muted')
            }

            if (selectedDate && isSameDate(cellDate, selectedDate)) {
                dayButton.classList.add('sprout-date-modal__day--selected')
            }

            if (isSameDate(cellDate, today) && !dayButton.classList.contains('sprout-date-modal__day--selected')) {
                dayButton.classList.add('sprout-date-modal__day--today')
            }

            dayButton.addEventListener('click', () => {
                inputElement.value = formatDateForInput(cellDate)
                syncSubmitState()
                hideValidation()
                closeModal(
                    document.querySelector(selectors.dateModal),
                    document.querySelector(selectors.dateTrigger),
                    classes.hiddenDateModal
                )
            })

            gridElement.appendChild(dayButton)
        })
    }

    const initializeDateModal = () => {
        const triggerElement = document.querySelector(selectors.dateTrigger)
        const inputElement = document.querySelector(selectors.dateInput)
        const modalElement = document.querySelector(selectors.dateModal)
        const prevButton = document.querySelector(selectors.datePrev)
        const nextButton = document.querySelector(selectors.dateNext)
        const todayButton = document.querySelector(selectors.dateToday)
        const monthSelectElement = document.querySelector(selectors.dateMonthSelect)
        const yearSelectElement = document.querySelector(selectors.dateYearSelect)

        if (!triggerElement || !inputElement || !modalElement) return

        const openHandler = (event) => {
            event.preventDefault()
            const selectedDate = parseInputDate(inputElement.value)
            currentCalendarDate = selectedDate
                ? new Date(selectedDate.getFullYear(), selectedDate.getMonth(), 1)
                : new Date(new Date().getFullYear(), new Date().getMonth(), 1)
            renderCalendar()
            openModal(modalElement, triggerElement)
        }

        triggerElement.addEventListener('click', openHandler)
        triggerElement.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                openHandler(event)
            }
        })

        document.querySelectorAll(selectors.dateClose).forEach((button) => {
            button.addEventListener('click', () => closeModal(modalElement, triggerElement, classes.hiddenDateModal))
        })

        prevButton?.addEventListener('click', () => {
            currentCalendarDate = new Date(currentCalendarDate.getFullYear(), currentCalendarDate.getMonth() - 1, 1)
            renderCalendar()
        })

        nextButton?.addEventListener('click', () => {
            currentCalendarDate = new Date(currentCalendarDate.getFullYear(), currentCalendarDate.getMonth() + 1, 1)
            renderCalendar()
        })

        monthSelectElement?.addEventListener('change', () => {
            currentCalendarDate = new Date(currentCalendarDate.getFullYear(), Number(monthSelectElement.value), 1)
            renderCalendar()
        })

        yearSelectElement?.addEventListener('change', () => {
            currentCalendarDate = new Date(Number(yearSelectElement.value), currentCalendarDate.getMonth(), 1)
            renderCalendar()
        })

        todayButton?.addEventListener('click', () => {
            const today = new Date()
            inputElement.value = formatDateForInput(today)
            currentCalendarDate = new Date(today.getFullYear(), today.getMonth(), 1)
            syncSubmitState()
            hideValidation()
            closeModal(modalElement, triggerElement, classes.hiddenDateModal)
        })
    }

    const initializeCategoryModal = () => {
        const triggerElement = document.querySelector(selectors.categoryTrigger)
        const modalElement = document.querySelector(selectors.categoryModal)
        const categoryInput = document.querySelector(selectors.categoryInput)
        const destinationCategoryInput = document.querySelector(selectors.destinationCategoryInput)

        if (!triggerElement || !modalElement || !categoryInput || !destinationCategoryInput) return

        const openHandler = (event) => {
            event.preventDefault()
            activeCategoryField = 'source'
            activeCategoryTrigger = triggerElement
            const selectedCategoryId = String(categoryInput.value || '')
            document.querySelectorAll(selectors.categoryItems).forEach((item) => {
                item.classList.toggle(
                    classes.selectedCategory,
                    String(item.getAttribute('data-transfer-category-id') || '') === selectedCategoryId
                )
            })
            openModal(modalElement, triggerElement)
        }

        triggerElement.addEventListener('click', openHandler)
        triggerElement.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                openHandler(event)
            }
        })

        document.querySelectorAll(selectors.categoryClose).forEach((button) => {
            button.addEventListener('click', () => {
                closeModal(modalElement, activeCategoryTrigger || triggerElement, classes.hiddenCategoryModal)
                activeCategoryTrigger = null
            })
        })

        document.querySelectorAll(selectors.categoryItems).forEach((button) => {
            button.addEventListener('click', () => {
                const categoryId = button.getAttribute('data-transfer-category-id') || ''
                const categoryAmount = button.getAttribute('data-transfer-category-amount') || '0'

                if (activeCategoryField === 'destination') {
                    destinationCategoryInput.value = categoryId
                } else {
                    categoryInput.value = categoryId
                }

                updateSelectedCategoryAmount(categoryAmount)

                document.querySelectorAll(selectors.categoryItems).forEach((item) => {
                    item.classList.toggle(classes.selectedCategory, item === button)
                })

                syncSubmitState()
                hideValidation()
                closeModal(modalElement, activeCategoryTrigger || triggerElement, classes.hiddenCategoryModal)
                activeCategoryTrigger = null
            })
        })
    }

    const initializeAccountModal = () => {
        const triggerElement = document.querySelector(selectors.accountTrigger)
        const modalElement = document.querySelector(selectors.accountModal)
        const categoryModalElement = document.querySelector(selectors.categoryModal)

        if (!triggerElement || !modalElement) return

        const openHandler = (event) => {
            event.preventDefault()
            if (isSavingsWithdraw()) {
                return
            }

            if (isSavingsToSavings()) {
                activeCategoryField = 'destination'
                activeCategoryTrigger = triggerElement
                const selectedDestinationCategoryId = String(document.querySelector(selectors.destinationCategoryInput)?.value || '')
                document.querySelectorAll(selectors.categoryItems).forEach((item) => {
                    item.classList.toggle(
                        classes.selectedCategory,
                        String(item.getAttribute('data-transfer-category-id') || '') === selectedDestinationCategoryId
                    )
                })
                openModal(categoryModalElement, triggerElement)
                syncTransferFlow()
                return
            }
            activeAccountTrigger = triggerElement
            renderAccountButtons()
            openModal(modalElement, triggerElement)
        }

        triggerElement.addEventListener('click', openHandler)
        triggerElement.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                openHandler(event)
            }
        })

        document.querySelectorAll(selectors.accountClose).forEach((button) => {
            button.addEventListener('click', () => {
                closeModal(modalElement, activeAccountTrigger || triggerElement, classes.hiddenAccountModal)
                activeAccountTrigger = null
            })
        })
    }

    const initializeValidation = () => {
        const formElement = document.querySelector(selectors.form)
        const dateInput = document.querySelector(selectors.dateInput)
        const amountInput = document.querySelector(selectors.amountInput)
        const categoryInput = document.querySelector(selectors.categoryInput)
        const destinationCategoryInput = document.querySelector(selectors.destinationCategoryInput)
        const accountInput = document.querySelector(selectors.accountInput)

        ;[dateInput, amountInput, categoryInput, destinationCategoryInput, accountInput].forEach((fieldElement) => {
            fieldElement?.addEventListener('input', () => {
                syncSubmitState()
                if (isFormComplete()) hideValidation()
            })
            fieldElement?.addEventListener('change', () => {
                syncSubmitState()
                if (isFormComplete()) hideValidation()
            })
        })

        formElement?.addEventListener('submit', (event) => {
            if (isFormComplete()) {
                if (amountInput) {
                    amountInput.value = parseWholeAmountDigits(amountInput.value)
                }
                hideValidation()
                return
            }

            event.preventDefault()
            const missingFields = []

            if (!dateInput?.value?.trim()) missingFields.push('date')
            if (!parseWholeAmountDigits(amountInput?.value ?? '')) missingFields.push('amount')
            if (!categoryInput?.value?.trim()) missingFields.push('source savings')
            if (isSavingsWithdraw()) {
                // no target field needed
            } else if (isSavingsToSavings()) {
                if (!destinationCategoryInput?.value?.trim()) missingFields.push('destination savings')
            } else if (!accountInput?.value?.trim()) {
                missingFields.push('income account')
            }

            showValidation(`Please fill in the required field${missingFields.length > 1 ? 's' : ''}: ${missingFields.join(', ')}.`)
        })

        syncSubmitState()
    }

    const initializeTransferType = () => {
        const transferTypeInput = document.querySelector(selectors.transferTypeInput)

        if (!transferTypeInput) {
            return
        }

        document.querySelectorAll(selectors.transferTypeOptions).forEach((button) => {
            button.addEventListener('click', () => {
                if (transferTypeLocked) {
                    return
                }

                transferTypeInput.value = button.getAttribute('data-transfer-type') || 'savings_to_income'
                activeCategoryField = 'source'
                syncTransferFlow()
                syncSubmitState()
                hideValidation()
            })
        })

        syncTransferFlow()
    }

    const initializeAmountInput = () => {
        const amountInput = document.querySelector(selectors.amountInput)

        if (!amountInput) {
            return
        }

        const initialDigits = parseWholeAmountDigits(amountInput.value ?? '')
        amountInput.dataset.rawDigits = initialDigits

        if (initialDigits) {
            amountInput.value = formatPesoCurrency(initialDigits)
        }

        amountInput.addEventListener('keydown', (event) => {
            const allowedNavigationKeys = [
                'Tab',
                'Shift',
                'ArrowLeft',
                'ArrowRight',
                'ArrowUp',
                'ArrowDown',
                'Home',
                'End',
                'Enter'
            ]

            if (allowedNavigationKeys.includes(event.key)) {
                return
            }

            if (event.key === 'Backspace' || event.key === 'Delete') {
                event.preventDefault()
                const currentDigits = amountInput.dataset.rawDigits ?? ''
                const updatedDigits = currentDigits.slice(0, -1)

                amountInput.dataset.rawDigits = updatedDigits
                amountInput.value = formatPesoCurrency(updatedDigits)
                syncSubmitState()
                return
            }

            if (!/^\d$/.test(event.key)) {
                event.preventDefault()
                return
            }

            event.preventDefault()

            const currentDigits = amountInput.dataset.rawDigits ?? ''
            const updatedDigits = `${currentDigits}${event.key}`.replace(/^0+(?=\d)/, '')

            amountInput.dataset.rawDigits = updatedDigits
            amountInput.value = formatPesoCurrency(updatedDigits)
            syncSubmitState()
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
            syncSubmitState()
        })

        amountInput.addEventListener('focus', () => {
            const currentDigits = amountInput.dataset.rawDigits ?? ''
            amountInput.value = formatPesoCurrency(currentDigits)
        })

        amountInput.addEventListener('input', syncSubmitState)
        amountInput.addEventListener('change', syncSubmitState)
    }

    const initializePhotoUpload = () => {
        const triggerElement = document.querySelector(selectors.photoTrigger)
        const modalElement = document.querySelector(selectors.photoModal)
        const closeButtons = document.querySelectorAll(selectors.photoCloseButtons)
        const cameraButton = document.querySelector(selectors.photoCameraButton)
        const galleryButton = document.querySelector(selectors.photoGalleryButton)
        const cameraInput = document.querySelector(selectors.photoCameraInput)
        const galleryInput = document.querySelector(selectors.photoGalleryInput)
        const previewWrapper = document.querySelector(selectors.photoPreviewWrapper)
        const existingPhotoPathsInput = document.querySelector(selectors.existingPhotoPathsInput)

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

        renderPhotoPreviews(previewWrapper, galleryInput, existingPhotoPathsInput, existingPhotoPaths)

        triggerElement.addEventListener('click', () => {
            openPhotoModal(modalElement)
        })

        closeButtons.forEach((closeButton) => {
            closeButton.addEventListener('click', () => {
                closePhotoModal(modalElement)
            })
        })

        cameraButton?.addEventListener('click', () => {
            closePhotoModal(modalElement)
            const isMobileDevice = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent)
            if (isMobileDevice) {
                cameraInput.setAttribute('capture', '')
            } else {
                cameraInput.removeAttribute('capture')
            }
            cameraInput.click()
        })

        galleryButton?.addEventListener('click', () => {
            closePhotoModal(modalElement)
            galleryInput.removeAttribute('capture')
            galleryInput.click()
        })

        cameraInput.addEventListener('change', () => {
            const selectedFiles = [...(cameraInput.files ?? [])]
            appendNewPhotoFiles(selectedFiles, galleryInput, previewWrapper, existingPhotoPathsInput, existingPhotoPaths)
            cameraInput.value = ''
        })

        galleryInput.addEventListener('change', () => {
            const selectedFiles = [...(galleryInput.files ?? [])]
            const existingFileKeys = new Set(
                selectedNewPhotoItems.map((photoItem) => `${photoItem.file.name}-${photoItem.file.size}-${photoItem.file.lastModified}`)
            )

            const deduplicatedFiles = selectedFiles.filter((file) => {
                const fileKey = `${file.name}-${file.size}-${file.lastModified}`
                if (existingFileKeys.has(fileKey)) {
                    return false
                }
                existingFileKeys.add(fileKey)
                return true
            })

            appendNewPhotoFiles(deduplicatedFiles, galleryInput, previewWrapper, existingPhotoPathsInput, existingPhotoPaths)
        })
    }

    renderAccountButtons()
    initializeTransferType()
    initializeAmountInput()
    initializeDateModal()
    initializeCategoryModal()
    initializeAccountModal()
    initializePhotoUpload()
    initializeValidation()
}
