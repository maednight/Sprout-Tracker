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

                    <!-- Transaction Header -->
                    <header class="sprout-transaction__header">
                        <div class="sprout-transaction__header-side sprout-transaction__header-side--left">
                            <a href="{{ route('dashboard') }}" class="sprout-transaction__back">
                                &lsaquo; Home
                            </a>
                        </div>

                        <div class="sprout-transaction__header-center">
                            <h1 class="sprout-transaction__title">
                                Expense
                            </h1>
                        </div>

                        <div class="sprout-transaction__header-side sprout-transaction__header-side--right"></div>
                    </header>

                    <!-- Transaction Tabs -->
                    <div class="sprout-transaction__tabs">
                        <button type="button" class="sprout-transaction__tab">
                            Income
                        </button>

                        <button type="button" class="sprout-transaction__tab sprout-transaction__tab--active">
                            Expense
                        </button>

                        <button type="button" class="sprout-transaction__tab">
                            Savings
                        </button>
                    </div>

                    <!-- Transaction Form -->
                    <form class="sprout-transaction__form" method="POST" action="#">
                        @csrf

                        <!-- Transaction Details Card -->
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

                            <div class="sprout-transaction__field">
                                <label for="category" class="sprout-transaction__label">Category</label>
                                <input
                                    id="category"
                                    name="category"
                                    type="text"
                                    class="sprout-transaction__input"
                                >
                            </div>

                            <div class="sprout-transaction__field sprout-transaction__field--last">
                                <label for="account" class="sprout-transaction__label">Account</label>
                                <input
                                    id="account"
                                    name="account"
                                    type="text"
                                    class="sprout-transaction__input"
                                >
                            </div>
                        </section>

                        <!-- Transaction Description Card -->
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

                        <!-- Transaction Actions -->
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

</body>
</html>