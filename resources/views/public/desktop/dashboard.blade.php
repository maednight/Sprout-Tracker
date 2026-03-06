<div class="sprout-app sprout-app--desktop">

    {{-- Left Sidebar --}}
    @include('public.partials.nav-desktop')

    <main class="sprout-main">

        <header class="sprout-main__header">
            <h1 class="sprout-main__title">
                Dashboard
            </h1>

            <button class="sprout-pill">
                This Month
            </button>
        </header>


        <section class="sprout-main__grid">

            <div class="sprout-card sprout-card--xl">

                <div class="sprout-card__head">
                    <div class="sprout-card__title">
                        Calendar
                    </div>
                </div>

                <div class="sprout-card__body">

                    <div class="sprout-placeholder">
                        Calendar UI will go here
                    </div>

                </div>

            </div>


            <div class="sprout-card">

                <div class="sprout-card__head">
                    <div class="sprout-card__title">
                        Summary
                    </div>
                </div>

                <div class="sprout-card__body">

                    <div class="sprout-placeholder">
                        Income / Expense / Balance
                    </div>

                </div>

            </div>


            <div class="sprout-card sprout-card--wide">

                <div class="sprout-card__head">
                    <div class="sprout-card__title">
                        Recent Transactions
                    </div>
                </div>

                <div class="sprout-card__body">

                    <div class="sprout-placeholder">
                        Transactions list here
                    </div>

                </div>

            </div>

        </section>

    </main>

</div>