<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Dashboard Page Meta -->
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sprout Dashboard</title>

    <!-- Dashboard CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Dashboard Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inknut+Antiqua:wght@400;600;700&family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <!-- Dashboard Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="sprout-font">
    <!-- Dashboard App Shell -->
    <div class="sprout-appshell">

        <!-- Dashboard Mobile View -->
        <div class="sprout-view sprout-view--mobile">
            @include('public.mobile.dashboard')
        </div>

        <!-- Dashboard Desktop View -->
        <div class="sprout-view sprout-view--desktop">
            @include('public.desktop.dashboard')
        </div>

    </div>
</body>
</html>