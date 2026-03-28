@php
    $authUserId = auth()->id() ?? 'guest';
    $transferTypeValue = $transferTypeValue ?? 'savings_to_savings';
    $transferTypeLocked = (bool) ($transferTypeLocked ?? false);
    $selectedCategory = $transferCategories->firstWhere('categoryId', (int) $transferCategoryValue);
    $selectedCategoryName = $selectedCategory['name'] ?? '';
    $selectedCategoryAmount = isset($selectedCategory['amount']) ? (float) $selectedCategory['amount'] : null;
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Transfer Savings | Sprout Income Expense Tracker</title>
    <link rel="icon" type="image/svg+xml" href="/projectassets/images/logo/sprout-logo.svg">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inknut+Antiqua:wght@400;600;700&family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="sprout-font sprout-savings-transfer-screen">
<div class="sprout-shell">
    <div
        class="sprout-phone sprout-savings-transfer-page sprout-savings-transfer"
        data-auth-user-id="{{ $authUserId }}"
        data-transfer-categories='@json($transferCategories->values()->all())'
        data-transfer-account-options='@json($accountOptions)'
        data-transfer-type-value="{{ $transferTypeValue }}"
        data-transfer-type-locked="{{ $transferTypeLocked ? 'true' : 'false' }}"
    >
        <main class="sprout-transaction__page">
            <div
                class="sprout-transaction__content"
                data-savings-transfer-page
            >
                <header class="sprout-transaction__header">
                    <div class="sprout-transaction__header-side sprout-transaction__header-side--left">
                        <a href="{{ $transferCancelUrl ?? route('savings_index') }}" class="sprout-transaction__back">
                            &lsaquo; Savings
                        </a>
                    </div>

                    <div class="sprout-transaction__header-center">
                        <h1 class="sprout-transaction__title">{{ $transferPageTitle ?? 'Transfer' }}</h1>
                    </div>

                    <div class="sprout-transaction__header-side sprout-transaction__header-side--right"></div>
                </header>

                @if ($errors->any())
                    <div style="margin-bottom: 16px; padding: 12px 14px; border-radius: 12px; background: #FFECEC; color: #D12C2C; font-size: 13px;">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <div class="sprout-transaction__validation sprout-transaction__validation--hidden" data-transfer-validation></div>

                <form
                    class="sprout-transaction__form"
                    method="POST"
                    action="{{ $transferFormAction ?? route('savings_transfer') }}"
                    data-transfer-form
                    enctype="multipart/form-data"
                    novalidate
                >
                    @csrf
                    @if (($transferFormMethod ?? 'POST') !== 'POST')
                        @method($transferFormMethod)
                    @endif

                    <input
                        type="file"
                        name="receipt_photo_camera"
                        accept="image/*"
                        capture
                        class="sprout-transaction__file-input"
                        data-transfer-photo-camera-input
                    >

                    <input
                        type="file"
                        name="receipt_photos[]"
                        accept="image/*"
                        multiple
                        class="sprout-transaction__file-input"
                        data-transfer-photo-gallery-input
                    >

                    <input
                        type="hidden"
                        name="existing_receipt_photo_paths"
                        value='@json($transferExistingPhotoPaths ?? [])'
                        data-transfer-existing-photo-paths
                    >

                    <section class="sprout-savings-transfer__card">
                        <div class="sprout-savings-transfer__type-picker" role="radiogroup" aria-label="Transfer type">
                            <button
                                type="button"
                                class="sprout-savings-transfer__type-option {{ $transferTypeValue === 'savings_to_savings' ? 'sprout-savings-transfer__type-option--active' : '' }}"
                                data-transfer-type-option
                                data-transfer-type="savings_to_savings"
                                aria-pressed="{{ $transferTypeValue === 'savings_to_savings' ? 'true' : 'false' }}"
                                {{ $transferTypeLocked ? 'disabled' : '' }}
                            >
                                Savings to Savings
                            </button>

                            <button
                                type="button"
                                class="sprout-savings-transfer__type-option {{ $transferTypeValue === 'savings_withdraw' ? 'sprout-savings-transfer__type-option--active' : '' }}"
                                data-transfer-type-option
                                data-transfer-type="savings_withdraw"
                                aria-pressed="{{ $transferTypeValue === 'savings_withdraw' ? 'true' : 'false' }}"
                                {{ $transferTypeLocked ? 'disabled' : '' }}
                            >
                                Withdraw
                            </button>
                        </div>

                        <div class="sprout-savings-transfer__flow {{ $transferTypeValue === 'savings_withdraw' ? 'sprout-savings-transfer__flow--withdraw' : '' }}" data-transfer-flow>
                            <div
                                class="sprout-savings-transfer__selector"
                                data-transfer-category-trigger
                                aria-expanded="false"
                                aria-controls="sproutTransferCategoryModal"
                                role="button"
                                tabindex="0"
                            >
                                <div class="sprout-savings-transfer__selector-label">From</div>
                                <div class="sprout-savings-transfer__selector-value {{ $selectedCategoryName ? '' : 'sprout-savings-transfer__selector-value--empty' }}" data-transfer-category-text>
                                    {{ $selectedCategoryName ?: 'Select' }}
                                </div>
                                <div class="sprout-savings-transfer__selector-meta {{ $selectedCategoryAmount !== null ? '' : 'sprout-savings-transfer__selector-meta--hidden' }}" data-transfer-category-amount>
                                    @if ($selectedCategoryAmount !== null)
                                        &#8369;{{ number_format($selectedCategoryAmount, 0) }} available
                                    @endif
                                </div>
                            </div>

                            <div class="sprout-savings-transfer__swap {{ $transferTypeValue === 'savings_withdraw' ? 'sprout-savings-transfer__swap--hidden' : '' }}" data-transfer-swap>
                                <img src="/projectassets/icons/transfer.svg" alt="" class="sprout-savings-transfer__swap-icon">
                            </div>

                            <div
                                class="sprout-savings-transfer__selector {{ $transferTypeValue === 'savings_withdraw' ? 'sprout-savings-transfer__selector--hidden' : '' }}"
                                data-transfer-account-trigger
                                aria-expanded="false"
                                aria-controls="sproutTransferAccountModal"
                                role="button"
                                tabindex="0"
                                data-transfer-target-selector
                            >
                                <div class="sprout-savings-transfer__selector-label">To</div>
                                <div class="sprout-savings-transfer__selector-value {{ $transferAccountValue ? '' : 'sprout-savings-transfer__selector-value--empty' }}" data-transfer-account-text>
                                    {{ $transferAccountValue ?: 'Select' }}
                                </div>
                                <div class="sprout-savings-transfer__selector-meta sprout-savings-transfer__selector-meta--hidden" data-transfer-account-amount></div>
                            </div>
                        </div>

                        <input
                            id="transfer_type"
                            name="transfer_type"
                            type="hidden"
                            value="{{ $transferTypeValue }}"
                            data-transfer-type-input
                        >

                        <input
                            id="source_category_id"
                            name="source_category_id"
                            type="hidden"
                            value="{{ $transferCategoryValue }}"
                            data-transfer-category-input
                        >

                        <input
                            id="destination_category_id"
                            name="destination_category_id"
                            type="hidden"
                            value="{{ $transferDestinationCategoryValue }}"
                            data-transfer-destination-category-input
                        >

                        <input
                            id="account"
                            name="account"
                            type="hidden"
                            value="{{ $transferAccountValue }}"
                            data-transfer-account-input
                        >

                        <div class="sprout-savings-transfer__rows">
                            <div class="sprout-savings-transfer__row">
                                <label for="transfer_amount" class="sprout-savings-transfer__row-label">Amount</label>
                                <div class="sprout-savings-transfer__row-control sprout-savings-transfer__row-control--input">
                                    <input
                                        id="transfer_amount"
                                        name="amount"
                                        type="text"
                                        class="sprout-savings-transfer__amount-input"
                                        value="{{ $transferAmountValue }}"
                                        placeholder="&#8369;0.00"
                                    >
                                </div>
                            </div>

                            <div
                                class="sprout-savings-transfer__row sprout-savings-transfer__row--date"
                                data-transfer-date-trigger
                                aria-expanded="false"
                                aria-controls="sproutTransferDateModal"
                                role="button"
                                tabindex="0"
                            >
                                <label for="transfer_date" class="sprout-savings-transfer__row-label">Date</label>
                                <div class="sprout-savings-transfer__row-pill sprout-savings-transfer__row-pill--date">
                                    <input
                                        id="transfer_date"
                                        name="transfer_date"
                                        type="text"
                                        class="sprout-savings-transfer__date-input"
                                        value="{{ $transferDateValue }}"
                                        placeholder="MM/DD/YYYY"
                                        readonly
                                        autocomplete="off"
                                    >
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="sprout-savings-transfer__note-card sprout-transaction__card sprout-transaction__card--description">
                        <div class="sprout-transaction__description-head">
                            <label for="description" class="sprout-transaction__label">Description</label>

                            <button
                                type="button"
                                class="sprout-transaction__camera"
                                data-transfer-photo-trigger
                            >
                                <img
                                    src="/projectassets/icons/camera.svg"
                                    alt="Camera"
                                    class="sprout-transaction__camera-icon"
                                >
                            </button>
                        </div>

                        <div
                            class="sprout-transaction__photo-preview-list {{ count($transferExistingPhotoPaths ?? []) ? '' : 'sprout-transaction__photo-preview-list--hidden' }}"
                            data-transfer-photo-preview-wrapper
                        >
                            @foreach (($transferExistingPhotoPaths ?? []) as $existingPhotoPath)
                                <div
                                    class="sprout-transaction__photo-preview-item"
                                    data-transfer-existing-photo-item
                                    data-photo-path="{{ $existingPhotoPath }}"
                                >
                                    <img
                                        src="{{ asset('storage/' . $existingPhotoPath) }}"
                                        alt="Receipt preview"
                                        class="sprout-transaction__photo-preview-image"
                                        data-transfer-photo-preview-image
                                    >

                                    <button
                                        type="button"
                                        class="sprout-transaction__photo-remove"
                                        aria-label="Remove selected image"
                                        data-transfer-photo-remove-existing
                                        data-photo-path="{{ $existingPhotoPath }}"
                                    >
                                        ×
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        <textarea
                            id="description"
                            name="description"
                            class="sprout-savings-transfer__note-input"
                        >{{ $transferDescriptionValue }}</textarea>
                    </section>

                    <div class="sprout-transaction__actions">
                        <button
                            type="submit"
                            class="sprout-savings-transfer__button"
                            data-transfer-submit
                        >
                            {{ $transferSubmitLabel ?? 'Confirm' }}
                        </button>
                    </div>
                </form>
            </div>
        </main>

        @include('public.shared.nav-mobile')
    </div>
</div>

<div
    class="sprout-photo-modal sprout-photo-modal--hidden"
    data-transfer-photo-modal
>
    <button
        type="button"
        class="sprout-photo-modal__backdrop"
        data-transfer-photo-close
        aria-label="Close photo options"
    ></button>

    <div
        class="sprout-photo-modal__sheet"
        role="dialog"
        aria-modal="true"
        aria-labelledby="sproutTransferPhotoModalTitle"
    >
        <div class="sprout-photo-modal__header">
            <h2
                id="sproutTransferPhotoModalTitle"
                class="sprout-photo-modal__title"
            >
                Add Photo
            </h2>

            <button
                type="button"
                class="sprout-photo-modal__close"
                data-transfer-photo-close
                aria-label="Close photo options"
            >
                &times;
            </button>
        </div>

        <div class="sprout-photo-modal__actions">
            <button
                type="button"
                class="sprout-photo-modal__button sprout-photo-modal__button--primary"
                data-transfer-photo-camera-button
            >
                Open Camera
            </button>

            <button
                type="button"
                class="sprout-photo-modal__button sprout-photo-modal__button--secondary"
                data-transfer-photo-gallery-button
            >
                Upload Photo
            </button>
        </div>
    </div>
</div>

<div
    class="sprout-photo-viewer sprout-photo-viewer--hidden"
    data-transfer-photo-viewer
>
    <button
        type="button"
        class="sprout-photo-viewer__backdrop"
        data-transfer-photo-viewer-close
        aria-label="Close photo viewer"
    ></button>

    <div
        class="sprout-photo-viewer__content"
        role="dialog"
        aria-modal="true"
        aria-labelledby="sproutTransferPhotoViewerTitle"
    >
        <div class="sprout-photo-viewer__header">
            <h2
                id="sproutTransferPhotoViewerTitle"
                class="sprout-photo-viewer__title"
            >
                Photo Preview
            </h2>

            <button
                type="button"
                class="sprout-photo-viewer__close"
                data-transfer-photo-viewer-close
                aria-label="Close photo viewer"
            ></button>
        </div>

        <div class="sprout-photo-viewer__body">
            <img
                src=""
                alt="Large photo preview"
                class="sprout-photo-viewer__image"
                data-transfer-photo-viewer-image
            >
        </div>
    </div>
</div>

<div class="sprout-date-modal sprout-date-modal--hidden" data-transfer-date-modal>
    <button type="button" class="sprout-date-modal__backdrop" data-transfer-date-close aria-label="Close date modal"></button>

    <div class="sprout-date-modal__sheet" id="sproutTransferDateModal" role="dialog" aria-modal="true" aria-labelledby="sproutTransferDateModalTitle">
        <div class="sprout-date-modal__header">
            <h2 id="sproutTransferDateModalTitle" class="sprout-date-modal__title">Select Date</h2>

            <button type="button" class="sprout-date-modal__close" data-transfer-date-close aria-label="Close date modal">
                &times;
            </button>
        </div>

        <div class="sprout-date-modal__topbar">
            <div class="sprout-date-modal__indicator" data-transfer-date-indicator>January 2026</div>

            <button type="button" class="sprout-date-modal__today-link" data-transfer-date-today>
                Today
            </button>
        </div>

        <div class="sprout-date-modal__nav">
            <button type="button" class="sprout-date-modal__nav-button" data-transfer-date-prev aria-label="Previous month">&lsaquo;</button>

            <div class="sprout-date-modal__controls">
                <select class="sprout-date-modal__select" data-transfer-date-month-select aria-label="Select month">
                    <option value="0">January</option>
                    <option value="1">February</option>
                    <option value="2">March</option>
                    <option value="3">April</option>
                    <option value="4">May</option>
                    <option value="5">June</option>
                    <option value="6">July</option>
                    <option value="7">August</option>
                    <option value="8">September</option>
                    <option value="9">October</option>
                    <option value="10">November</option>
                    <option value="11">December</option>
                </select>

                <select class="sprout-date-modal__select" data-transfer-date-year-select aria-label="Select year"></select>
            </div>

            <button type="button" class="sprout-date-modal__nav-button" data-transfer-date-next aria-label="Next month">&rsaquo;</button>
        </div>

        <div class="sprout-date-modal__weekdays">
            <span>Sun</span>
            <span>Mon</span>
            <span>Tue</span>
            <span>Wed</span>
            <span>Thu</span>
            <span>Fri</span>
            <span>Sat</span>
        </div>

        <div class="sprout-date-modal__grid" data-transfer-date-grid></div>
    </div>
</div>

<div class="sprout-category-modal sprout-category-modal--hidden" data-transfer-category-modal>
    <button type="button" class="sprout-category-modal__backdrop" data-transfer-category-close aria-label="Close category modal"></button>

    <div class="sprout-category-modal__sheet" id="sproutTransferCategoryModal" role="dialog" aria-modal="true" aria-labelledby="sproutTransferCategoryModalTitle">
        <div class="sprout-category-modal__header">
            <h2 id="sproutTransferCategoryModalTitle" class="sprout-category-modal__title" data-transfer-category-modal-title>Savings Category</h2>

            <button type="button" class="sprout-category-modal__close" data-transfer-category-close aria-label="Close category modal">
                &times;
            </button>
        </div>

        <div class="sprout-category-modal__body">
            <div class="sprout-category-modal__grid">
                @foreach ($transferCategories as $category)
                    <button
                        type="button"
                        class="sprout-category-modal__item {{ (string) $transferCategoryValue === (string) $category['categoryId'] ? 'sprout-category-modal__item--selected' : '' }}"
                        data-transfer-category-item
                        data-transfer-category-id="{{ $category['categoryId'] }}"
                        data-transfer-category-name="{{ $category['name'] }}"
                        data-transfer-category-amount="{{ $category['amount'] }}"
                    >
                        <span class="sprout-savings-transfer__option-name">{{ $category['name'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="sprout-account-modal sprout-account-modal--hidden" data-transfer-account-modal>
    <button type="button" class="sprout-account-modal__backdrop" data-transfer-account-close aria-label="Close account modal"></button>

    <div class="sprout-account-modal__sheet" id="sproutTransferAccountModal" role="dialog" aria-modal="true" aria-labelledby="sproutTransferAccountModalTitle">
        <div class="sprout-account-modal__header">
            <h2 id="sproutTransferAccountModalTitle" class="sprout-account-modal__title" data-transfer-account-modal-title>Account</h2>

            <button type="button" class="sprout-account-modal__close" data-transfer-account-close aria-label="Close account modal">
                &times;
            </button>
        </div>

        <div class="sprout-account-modal__body">
            <div class="sprout-account-modal__grid" data-transfer-account-grid>
                @foreach (collect($accountOptions)->unique()->values() as $accountOption)
                    <button
                        type="button"
                        class="sprout-account-modal__item {{ $transferAccountValue === $accountOption ? 'sprout-account-modal__item--selected' : '' }}"
                        data-transfer-account-item
                        data-transfer-account-name="{{ $accountOption }}"
                    >
                        {{ $accountOption }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>
</div>
</body>
</html>
