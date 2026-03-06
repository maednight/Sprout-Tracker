<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Add Transaction - Sprout</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inknut+Antiqua:wght@400;600;700&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

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
                              Expense
                          </h1>
                      </div>

                      <div class="sprout-transaction__header-side sprout-transaction__header-side--right"></div>
                  </header>

                  <div class="sprout-transaction__tabs">
                      <button
                          type="button"
                          class="sprout-transaction__tab"
                          data-transaction-tab
                          data-transaction-type="income"
                          data-transaction-title="Income"
                      >
                          Income
                      </button>

                      <button
                          type="button"
                          class="sprout-transaction__tab sprout-transaction__tab--active"
                          data-transaction-tab
                          data-transaction-type="expense"
                          data-transaction-title="Expense"
                      >
                          Expense
                      </button>

                      <button
                          type="button"
                          class="sprout-transaction__tab"
                          data-transaction-tab
                          data-transaction-type="savings"
                          data-transaction-title="Savings"
                      >
                          Savings
                      </button>
                  </div>

                  <form class="sprout-transaction__form" method="POST" action="#">
                      @csrf

                      <input
                          type="hidden"
                          name="transaction_type"
                          value="expense"
                          data-transaction-type-input
                      >

                      <section class="sprout-transaction__card">
                          <div class="sprout-transaction__field">
                              <label for="transaction_date" class="sprout-transaction__label">Date</label>
                              <input
                                  id="transaction_date"
                                  name="transaction_date"
                                  type="text"
                                  class="sprout-transaction__input"
                              >
                          </div>

                          <div class="sprout-transaction__field">
                              <label for="amount" class="sprout-transaction__label">Amount</label>
                              <input
                                  id="amount"
                                  name="amount"
                                  type="text"
                                  class="sprout-transaction__input"
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
                                      class="sprout-transaction__picker-text sprout-transaction__picker-text--empty"
                                      data-category-selected-text
                                  ></span>
                              </div>

                              <input
                                  id="category"
                                  name="category"
                                  type="hidden"
                                  value=""
                                  data-category-input
                              >
                          </div>

                          <!-- Account Picker Field -->
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
                                      class="sprout-transaction__picker-text sprout-transaction__picker-text--empty"
                                      data-account-selected-text
                                  ></span>
                              </div>

                              <input
                                  id="account"
                                  name="account"
                                  type="hidden"
                                  value=""
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
                                  aria-label="Add receipt image"
                              >
                                  <img
                                      src="/projectassets/icons/camera.svg"
                                      alt="Camera"
                                      class="sprout-transaction__camera-icon"
                                  >
                              </button>
                          </div>

                          <textarea
                              id="description"
                              name="description"
                              class="sprout-transaction__textarea"
                          ></textarea>
                      </section>

                      <div class="sprout-transaction__actions">
                          <button type="submit" class="sprout-transaction__button sprout-transaction__button--primary">
                              Save
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
              <!-- Category Modal Grid -->
              <div class="sprout-category-modal__grid" data-category-grid>
                  <button type="button" class="sprout-category-modal__item" data-category-item data-category-name="Food">
                      Food
                  </button>

                  <button type="button" class="sprout-category-modal__item" data-category-item data-category-name="Transportation">
                      Transportation
                  </button>

                  <button type="button" class="sprout-category-modal__item" data-category-item data-category-name="Pets">
                      Pets
                  </button>

                  <button type="button" class="sprout-category-modal__item" data-category-item data-category-name="Culture">
                      Culture
                  </button>

                  <button type="button" class="sprout-category-modal__item" data-category-item data-category-name="Household">
                      Household
                  </button>

                  <button type="button" class="sprout-category-modal__item" data-category-item data-category-name="Apparel">
                      Apparel
                  </button>

                  <button type="button" class="sprout-category-modal__item" data-category-item data-category-name="Beauty">
                      Beauty
                  </button>

                  <button type="button" class="sprout-category-modal__item" data-category-item data-category-name="Health">
                      Health
                  </button>

                  <button type="button" class="sprout-category-modal__item" data-category-item data-category-name="Education">
                      Education
                  </button>

                  <button type="button" class="sprout-category-modal__item" data-category-item data-category-name="Work">
                      Work
                  </button>

                  <button type="button" class="sprout-category-modal__item" data-category-item data-category-name="Gift">
                      Gift
                  </button>

                  <button type="button" class="sprout-category-modal__item" data-category-item data-category-name="Others">
                      Others
                  </button>
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

  <!-- Account Modal -->
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
              <button
                  type="button"
                  class="sprout-account-modal__item"
                  data-account-item
                  data-account-name="Cash"
              >
                  Cash
              </button>

              <button
                  type="button"
                  class="sprout-account-modal__item"
                  data-account-item
                  data-account-name="Bank"
              >
                  Bank
              </button>

              <button
                  type="button"
                  class="sprout-account-modal__item"
                  data-account-item
                  data-account-name="Card"
              >
                  Card
              </button>
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

</body>
</html>