<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Transactions - Sprout</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Inknut+Antiqua:wght@400;600;700&family=Inter:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet"
  >

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  @vite(['resources/css/app.css', 'resources/css/pages/transactions.css', 'resources/js/app.js'])
</head>
<body class="sprout-font">
  <div class="sprout-appshell">
    <div class="sprout-view sprout-view--mobile">
      @include('public.mobile.transactions')
    </div>

    <div class="sprout-view sprout-view--desktop">
      @include('public.desktop.transactions')
    </div>
  </div>
</body>
</html>
