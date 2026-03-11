@php
$isEditMode = isset($transaction) && $transaction;

$pageTitle = $isEditMode ? 'Edit Transaction - Sprout' : 'Add Transaction - Sprout';
$formAction = $isEditMode ? route('transaction.update', $transaction) : route('transaction.store');

$transactionTypeValue = old('transaction_type', $transaction->type ?? 'expense');
$transactionDateValue = old('transaction_date', $isEditMode ? $transaction->occurred_at->format('m/d/Y') : '');
$amountValue = old('amount', $isEditMode ? number_format((float) $transaction->amount, 2, '.', ',') : '');
$categoryValue = old('category', $isEditMode ? $transaction->category?->name : '');
$accountValue = old('account', $isEditMode ? $transaction->account?->name : '');
$descriptionValue = old('description', $isEditMode ? $transaction->description : '');

$storedTransactionPhotoPaths = [];

if ($isEditMode) {
    $storedTransactionPhotoPaths = is_array($transaction->receipt_photo_paths ?? null)
        ? $transaction->receipt_photo_paths
        : [];

    if (empty($storedTransactionPhotoPaths) && !empty($transaction->receipt_photo_path)) {
        $storedTransactionPhotoPaths = [$transaction->receipt_photo_path];
    }
}

$oldExistingPhotoPaths = old('existing_receipt_photo_paths');

if (is_string($oldExistingPhotoPaths) && $oldExistingPhotoPaths !== '') {
    $decodedOldPhotoPaths = json_decode($oldExistingPhotoPaths, true);

    $existingPhotoPaths = is_array($decodedOldPhotoPaths)
        ? array_values(array_filter($decodedOldPhotoPaths))
        : $storedTransactionPhotoPaths;
} else {
    $existingPhotoPaths = $storedTransactionPhotoPaths;
}

$pageHeaderTitle = ucfirst($transactionTypeValue);
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>{{ $pageTitle }}</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link
href="https://fonts.googleapis.com/css2?family=Inknut+Antiqua:wght@400;600;700&family=Inter:wght@300;400;500;600;700;800&display=swap"
rel="stylesheet"
>

@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="sprout-font">

<div class="sprout-shell">
<div class="sprout-phone sprout-transaction">

  <main class="sprout-transaction__page">
      <div class="sprout-transaction__content">

          <header class="sprout-transaction__header">
              <div class="sprout-transaction__header-side sprout-transaction__header-side--left">
                  <a href="{{ route('dashboard') }}" class="sprout-transaction__back">
                      &lsaquo; Home
                  </a>
              </div>

              <div class="sprout-transaction__header-center">
                  <h1 class="sprout-transaction__title" data-transaction-title>
                      {{ $pageHeaderTitle }}
                  </h1>
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

          @if (session('success'))
              <div style="margin-bottom: 16px; padding: 12px 14px; border-radius: 12px; background: #EFFFF2; color: #00B050; font-size: 13px;">
                  {{ session('success') }}
              </div>
          @endif

          <div class="sprout-transaction__tabs">
              <button
                  type="button"
                  class="sprout-transaction__tab {{ $transactionTypeValue === 'income' ? 'sprout-transaction__tab--active' : '' }}"
                  data-transaction-tab
                  data-transaction-type="income"
                  data-transaction-title="Income"
              >
                  Income
              </button>

              <button
                  type="button"
                  class="sprout-transaction__tab {{ $transactionTypeValue === 'expense' ? 'sprout-transaction__tab--active' : '' }}"
                  data-transaction-tab
                  data-transaction-type="expense"
                  data-transaction-title="Expense"
              >
                  Expense
              </button>

              <button
                  type="button"
                  class="sprout-transaction__tab {{ $transactionTypeValue === 'savings' ? 'sprout-transaction__tab--active' : '' }}"
                  data-transaction-tab
                  data-transaction-type="savings"
                  data-transaction-title="Savings"
              >
                  Savings
              </button>
          </div>

          <form
              class="sprout-transaction__form"
              method="POST"
              action="{{ $formAction }}"
              enctype="multipart/form-data"
          >
              @csrf
              @if ($isEditMode)
                  @method('PUT')
              @endif

              <input
                  type="hidden"
                  name="transaction_type"
                  value="{{ $transactionTypeValue }}"
                  data-transaction-type-input
              >

              <input
                  type="file"
                  accept="image/*"
                  capture="environment"
                  class="sprout-transaction__file-input"
                  data-photo-camera-input
              >

              <input
                  type="file"
                  name="receipt_photos[]"
                  accept="image/*"
                  multiple
                  class="sprout-transaction__file-input"
                  data-photo-gallery-input
              >

              <input
                  type="hidden"
                  name="existing_receipt_photo_paths"
                  value='@json($existingPhotoPaths)'
                  data-existing-photo-paths
              >

              <section class="sprout-transaction__card">
                  <div
                      class="sprout-transaction__field sprout-transaction__field--picker"
                      data-date-trigger
                      aria-expanded="false"
                      aria-controls="sproutDateModal"
                      role="button"
                      tabindex="0"
                  >
                      <label for="transaction_date" class="sprout-transaction__label">Date</label>

                      <input
                          id="transaction_date"
                          name="transaction_date"
                          type="text"
                          class="sprout-transaction__input sprout-transaction__input--picker"
                          value="{{ $transactionDateValue }}"
                          placeholder="MM/DD/YYYY"
                          readonly
                          autocomplete="off"
                      >
                  </div>

                  <div class="sprout-transaction__field">
                      <label for="amount" class="sprout-transaction__label">Amount</label>
                      <input
                          id="amount"
                          name="amount"
                          type="text"
                          class="sprout-transaction__input"
                          value="{{ $amountValue }}"
                      >
                  </div>

                  <div
                      class="sprout-transaction__field sprout-transaction__field--picker"
                      data-category-trigger
                      aria-expanded="false"
                      aria-controls="sproutCategoryModal"
                      role="button"
                      tabindex="0"
                  >
                      <label for="category" class="sprout-transaction__label">Category</label>

                      <div class="sprout-transaction__picker-trigger">
                          <span
                              class="sprout-transaction__picker-text {{ $categoryValue ? '' : 'sprout-transaction__picker-text--empty' }}"
                              data-category-selected-text
                          >{{ $categoryValue }}</span>
                      </div>

                      <input
                          id="category"
                          name="category"
                          type="hidden"
                          value="{{ $categoryValue }}"
                          data-category-input
                      >
                  </div>

                  <div
                      class="sprout-transaction__field sprout-transaction__field--picker sprout-transaction__field--last"
                      data-account-trigger
                      aria-expanded="false"
                      aria-controls="sproutAccountModal"
                      role="button"
                      tabindex="0"
                  >
                      <label for="account" class="sprout-transaction__label">Account</label>

                      <div class="sprout-transaction__picker-trigger">
                          <span
                              class="sprout-transaction__picker-text {{ $accountValue ? '' : 'sprout-transaction__picker-text--empty' }}"
                              data-account-selected-text
                          >{{ $accountValue }}</span>
                      </div>

                      <input
                          id="account"
                          name="account"
                          type="hidden"
                          value="{{ $accountValue }}"
                          data-account-input
                      >
                  </div>
              </section>

              <section class="sprout-transaction__card sprout-transaction__card--description">
                  <div class="sprout-transaction__description-head">
                      <label for="description" class="sprout-transaction__label">Description</label>

                      <button
                          type="button"
                          class="sprout-transaction__camera"
                          data-photo-trigger
                      >
                          <img
                              src="/projectassets/icons/camera.svg"
                              alt="Camera"
                              class="sprout-transaction__camera-icon"
                          >
                      </button>
                  </div>

                  <div
                      class="sprout-transaction__photo-preview-list {{ count($existingPhotoPaths) ? '' : 'sprout-transaction__photo-preview-list--hidden' }}"
                      data-photo-preview-wrapper
                  >
                      @foreach ($existingPhotoPaths as $existingPhotoPath)
                          <div
                              class="sprout-transaction__photo-preview-item"
                              data-existing-photo-item
                              data-photo-path="{{ $existingPhotoPath }}"
                          >
                              <img
                                  src="{{ asset('storage/' . $existingPhotoPath) }}"
                                  alt="Receipt preview"
                                  class="sprout-transaction__photo-preview-image"
                              >

                              <button
                                  type="button"
                                  class="sprout-transaction__photo-remove"
                                  aria-label="Remove selected image"
                                  data-photo-remove-existing
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
                      class="sprout-transaction__textarea"
                  >{{ $descriptionValue }}</textarea>
              </section>

              <div class="sprout-transaction__actions">
                  <button type="submit" class="sprout-transaction__button sprout-transaction__button--primary">
                      {{ $isEditMode ? 'Update' : 'Save' }}
                  </button>

                  <button type="button" class="sprout-transaction__button sprout-transaction__button--secondary">
                      Continue
                  </button>
              </div>
          </form>

      </div>
  </main>

  @include('public.partials.nav-mobile')
</div>
</div>

<div
class="sprout-date-modal sprout-date-modal--hidden"
data-date-modal
>
<button
  type="button"
  class="sprout-date-modal__backdrop"
  data-date-close
  aria-label="Close date modal"
></button>

<div
  class="sprout-date-modal__sheet"
  id="sproutDateModal"
  role="dialog"
  aria-modal="true"
  aria-labelledby="sproutDateModalTitle"
>
  <div class="sprout-date-modal__header">
      <h2
          id="sproutDateModalTitle"
          class="sprout-date-modal__title"
      >
          Select Date
      </h2>

      <button
          type="button"
          class="sprout-date-modal__close"
          data-date-close
          aria-label="Close date modal"
      >
          ×
      </button>
  </div>

  <div class="sprout-date-modal__topbar">
      <div
          class="sprout-date-modal__indicator"
          data-date-indicator
      >
          January 2026
      </div>

      <button
          type="button"
          class="sprout-date-modal__today-link"
          data-date-today
      >
          Today
      </button>
  </div>

  <div class="sprout-date-modal__nav">
      <button
          type="button"
          class="sprout-date-modal__nav-button"
          data-date-prev
          aria-label="Previous month"
      >
          ‹
      </button>

      <div class="sprout-date-modal__controls">
          <select
              class="sprout-date-modal__select"
              data-date-month-select
              aria-label="Select month"
          >
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

          <select
              class="sprout-date-modal__select"
              data-date-year-select
              aria-label="Select year"
          ></select>
      </div>

      <button
          type="button"
          class="sprout-date-modal__nav-button"
          data-date-next
          aria-label="Next month"
      >
          ›
      </button>
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

  <div class="sprout-date-modal__grid" data-date-grid></div>
</div>
</div>

<div
class="sprout-category-modal sprout-category-modal--hidden"
data-category-modal
>
<button
  type="button"
  class="sprout-category-modal__backdrop"
  data-category-close
  aria-label="Close category modal"
></button>

<div
  class="sprout-category-modal__sheet"
  id="sproutCategoryModal"
  role="dialog"
  aria-modal="true"
  aria-labelledby="sproutCategoryModalTitle"
>
  <div class="sprout-category-modal__header">
      <h2
          id="sproutCategoryModalTitle"
          class="sprout-category-modal__title"
      >
          Category
      </h2>

      <button
          type="button"
          class="sprout-category-modal__close"
          data-category-close
          aria-label="Close category modal"
      >
          ×
      </button>
  </div>

  <div class="sprout-category-modal__body">
      <div class="sprout-category-modal__grid" data-category-grid>
          <button type="button" class="sprout-category-modal__item" data-category-item data-category-name="Food">Food</button>
          <button type="button" class="sprout-category-modal__item" data-category-item data-category-name="Transportation">Transportation</button>
          <button type="button" class="sprout-category-modal__item" data-category-item data-category-name="Pets">Pets</button>
          <button type="button" class="sprout-category-modal__item" data-category-item data-category-name="Culture">Culture</button>
          <button type="button" class="sprout-category-modal__item" data-category-item data-category-name="Household">Household</button>
          <button type="button" class="sprout-category-modal__item" data-category-item data-category-name="Apparel">Apparel</button>
          <button type="button" class="sprout-category-modal__item" data-category-item data-category-name="Beauty">Beauty</button>
          <button type="button" class="sprout-category-modal__item" data-category-item data-category-name="Health">Health</button>
          <button type="button" class="sprout-category-modal__item" data-category-item data-category-name="Education">Education</button>
          <button type="button" class="sprout-category-modal__item" data-category-item data-category-name="Work">Work</button>
          <button type="button" class="sprout-category-modal__item" data-category-item data-category-name="Gift">Gift</button>
          <button type="button" class="sprout-category-modal__item" data-category-item data-category-name="Others">Others</button>
      </div>

      <div class="sprout-category-modal__actions">
          <button
              type="button"
              class="sprout-category-modal__add-button"
          >
              Add
          </button>
      </div>
  </div>
</div>
</div>

<div
class="sprout-account-modal sprout-account-modal--hidden"
data-account-modal
>
<button
  type="button"
  class="sprout-account-modal__backdrop"
  data-account-close
  aria-label="Close account modal"
></button>

<div
  class="sprout-account-modal__sheet"
  id="sproutAccountModal"
  role="dialog"
  aria-modal="true"
  aria-labelledby="sproutAccountModalTitle"
>
  <div class="sprout-account-modal__header">
      <h2
          id="sproutAccountModalTitle"
          class="sprout-account-modal__title"
      >
          Account
      </h2>

      <button
          type="button"
          class="sprout-account-modal__close"
          data-account-close
          aria-label="Close account modal"
      >
          ×
      </button>
  </div>

  <div class="sprout-account-modal__body">
      <div class="sprout-account-modal__grid">
          <button type="button" class="sprout-account-modal__item" data-account-item data-account-name="Cash">Cash</button>
          <button type="button" class="sprout-account-modal__item" data-account-item data-account-name="Bank">Bank</button>
          <button type="button" class="sprout-account-modal__item" data-account-item data-account-name="Card">Card</button>
      </div>

      <div class="sprout-account-modal__actions">
          <button
              type="button"
              class="sprout-account-modal__add-button"
          >
              Add
          </button>
      </div>
  </div>
</div>
</div>

<div
class="sprout-add-option-overlay sprout-add-option-overlay--hidden"
data-add-category-overlay
>
<button
  type="button"
  class="sprout-add-option-overlay__backdrop"
  data-add-category-close
  aria-label="Close add category overlay"
></button>

<div
  class="sprout-add-option-overlay__sheet"
  role="dialog"
  aria-modal="true"
  aria-labelledby="sproutAddCategoryTitle"
>
  <div class="sprout-add-option-overlay__header">
      <button
          type="button"
          class="sprout-add-option-overlay__back"
          data-add-category-close
          aria-label="Go back"
      >
          ‹
      </button>

      <h2
          id="sproutAddCategoryTitle"
          class="sprout-add-option-overlay__title"
          data-add-category-title
      >
          Add Expense Category
      </h2>

      <div class="sprout-add-option-overlay__spacer" aria-hidden="true"></div>
  </div>

  <div class="sprout-add-option-overlay__body">
      <div class="sprout-add-option-overlay__field">
          <label for="custom_category_name" class="sprout-add-option-overlay__label">
              Category Name
          </label>

          <input
              id="custom_category_name"
              type="text"
              class="sprout-add-option-overlay__input"
              data-add-category-input
          >
      </div>

      <button
          type="button"
          class="sprout-add-option-overlay__save"
          data-add-category-save
      >
          Save
      </button>
  </div>
</div>
</div>

<div
class="sprout-add-option-overlay sprout-add-option-overlay--hidden"
data-add-account-overlay
>
<button
  type="button"
  class="sprout-add-option-overlay__backdrop"
  data-add-account-close
  aria-label="Close add account overlay"
></button>

<div
  class="sprout-add-option-overlay__sheet"
  role="dialog"
  aria-modal="true"
  aria-labelledby="sproutAddAccountTitle"
>
  <div class="sprout-add-option-overlay__header">
      <button
          type="button"
          class="sprout-add-option-overlay__back"
          data-add-account-close
          aria-label="Go back"
      >
          ‹
      </button>

      <h2
          id="sproutAddAccountTitle"
          class="sprout-add-option-overlay__title"
      >
          Add Account
      </h2>

      <div class="sprout-add-option-overlay__spacer" aria-hidden="true"></div>
  </div>

  <div class="sprout-add-option-overlay__body">
      <div class="sprout-add-option-overlay__field">
          <label for="custom_account_name" class="sprout-add-option-overlay__label">
              Account Name
          </label>

          <input
              id="custom_account_name"
              type="text"
              class="sprout-add-option-overlay__input"
              data-add-account-input
          >
      </div>

      <button
          type="button"
          class="sprout-add-option-overlay__save"
          data-add-account-save
      >
          Save
      </button>
  </div>
</div>
</div>

<div
class="sprout-photo-modal sprout-photo-modal--hidden"
data-photo-modal
>
<button
  type="button"
  class="sprout-photo-modal__backdrop"
  data-photo-close
  aria-label="Close photo options"
></button>

<div
  class="sprout-photo-modal__sheet"
  role="dialog"
  aria-modal="true"
  aria-labelledby="sproutPhotoModalTitle"
>
  <div class="sprout-photo-modal__header">
      <h2
          id="sproutPhotoModalTitle"
          class="sprout-photo-modal__title"
      >
          Add Photo
      </h2>

      <button
          type="button"
          class="sprout-photo-modal__close"
          data-photo-close
          aria-label="Close photo options"
      >
          ×
      </button>
  </div>

    <div class="sprout-photo-modal__actions">
      <button
          type="button"
          class="sprout-photo-modal__button sprout-photo-modal__button--primary"
          data-photo-camera-button
      >
          Open Camera
      </button>

      <button
          type="button"
          class="sprout-photo-modal__button sprout-photo-modal__button--secondary"
          data-photo-gallery-button
      >
          Upload Photo
      </button>
  </div>
</div>
</div>

</body>
</html>