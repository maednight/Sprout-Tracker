<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard | Sprout Income Expense Tracker</title>
    <link rel="icon" type="image/svg+xml" href="/projectassets/images/logo/sprout-logo.svg">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inknut+Antiqua:wght@400;600;700&family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="sprout-font">
    <div class="sprout-appshell">
        <div class="sprout-view sprout-view--mobile">
            <div class="sprout-phone sprout-app sprout-app--mobile">
                <div
                    data-dashboard-app
                    data-dashboard='@json($dashboardPayload ?? ["transactionGroups" => [], "initialDisplayDate" => now()->format("Y-m-d")])'
                    data-csrf-token="{{ csrf_token() }}"
                    data-flash-success='@json(session("success"))'
                    data-flash-success-type='@json(session("success_type"))'
                ></div>

                @include('public.shared.nav-mobile')
            </div>
        </div>
        <div class="sprout-view sprout-view--desktop">
            <div class="sprout-dashboard-desktop">
                @include('public.shared.nav-desktop')

                <main class="sprout-dashboard-desktop__content">
                    <div
                        class="sprout-dashboard-desktop__panel"
                        data-dashboard-app
                        data-dashboard='@json($dashboardPayload ?? ["transactionGroups" => [], "initialDisplayDate" => now()->format("Y-m-d")])'
                        data-csrf-token="{{ csrf_token() }}"
                        data-flash-success='@json(session("success"))'
                        data-flash-success-type='@json(session("success_type"))'
                    ></div>
                </main>
            </div>
        </div>

    </div>
</body>
</html>
