<!DOCTYPE html>
<html lang="en">
<head>
<!-- Budget Head -->
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Budget - Sprout</title>

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link
    href="https://fonts.googleapis.com/css2?family=Inknut+Antiqua:wght@400;600;700&family=Inter:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet"
>

<!-- Vite Assets -->
@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="sprout-font">
<!-- Budget App Shell -->
<div class="sprout-shell">
    <div class="sprout-phone sprout-budget">

        <!-- Budget Main -->
        <main class="sprout-budget__page">
            <div class="sprout-budget__content">

                <!-- Budget Header -->
                <header class="sprout-budget__header">
                    <a
                        href="{{ route('budget.index', ['month' => $previousMonthValue]) }}"
                        class="sprout-budget__month-arrow"
                        aria-label="Previous month"
                    >
                        ‹
                    </a>

                    <h1 class="sprout-budget__month-title">
                        {{ $displayMonthLabel }}
                    </h1>

                    <a
                        href="{{ route('budget.index', ['month' => $nextMonthValue]) }}"
                        class="sprout-budget__month-arrow"
                        aria-label="Next month"
                    >
                        ›
                    </a>
                </header>

                @if (!$budget)
                    <!-- Budget Empty State -->
                    <section class="sprout-budget__empty-state">
                        <p class="sprout-budget__empty-text">
                            No Budget was set.
                        </p>

                        <a
                            href="{{ route('budget.create', ['month' => $selectedMonthValue]) }}"
                            class="sprout-budget__empty-link"
                        >
                            Set Now
                        </a>
                    </section>
                @else
                    <!-- Budget Filled State -->
                    <section class="sprout-budget__filled-state">
                        <div class="sprout-budget__placeholder-card">
                            Budget summary will appear here.
                        </div>
                    </section>
                @endif

            </div>
        </main>

        <!-- Budget Bottom Navigation -->
        @include('public.partials.nav-mobile')

    </div>
</div>
</body>
</html>