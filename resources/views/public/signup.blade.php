<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sign Up | Sprout Income Expense Tracker</title>
    <link rel="icon" type="image/svg+xml" href="/projectassets/images/logo/sprout-logo.svg">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inknut+Antiqua:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])
</head>
<body class="sprout-font">
    <div class="sprout-shell">
        <div class="sprout-view sprout-view--mobile">
            @include('public.mobile.signup')
        </div>

        <div class="sprout-view sprout-view--desktop">
            @include('public.desktop.signup')
        </div>
    </div>
</body>
</html>
