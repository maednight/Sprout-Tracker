<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Sprout Income Expense Tracker</title>
    <link rel="icon" type="image/svg+xml" href="/projectassets/images/logo/sprout-logo.svg">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin> 
    <link href="https://fonts.googleapis.com/css2?family=Inknut+Antiqua:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- VITE ASSET LOADER -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body class="sprout-font sprout-loading">

        <!-- APP WRAPPER (Desktop background container) -->
        <!-- Centers the mobile screen on desktop -->

    <div class="sprout-shell">

        <!-- MOBILE SCREEN CONTAINER -->
        <!-- Simulates a mobile app layout on desktop -->

        <div class="sprout-phone">

            <!-- LOADING PAGE -->
            <!-- Main loading splash screen -->

            <main class="loading-page">

                <!-- LOADING CONTENT -->
                <!-- Logo + Title + Loading Indicator -->

                <div class="loading-container">

                    <!-- Application Logo -->
                    <img
                        src="/projectassets/images/logo/sprout-logo.svg"
                        alt="Sprout Logo"
                        class="loading-logo"
                    >
                    <!-- Application Name -->
                    <h1 class="loading-title">
                        SPROUT
                    </h1>

                    <!-- Loading Indicator Bar -->
                    <div class="loading-bar"></div>

                </div>

            </main>

        </div>

    </div>

      <script>
          setTimeout(() => window.location.href = "{{ route('dashboard') }}", 1000)
      </script>
</body>

</html>
