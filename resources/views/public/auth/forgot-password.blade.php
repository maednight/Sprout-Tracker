<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Forgot Password | Sprout Income Expense Tracker</title>
    <link rel="icon" type="image/svg+xml" href="/projectassets/images/logo/sprout-logo.svg">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inknut+Antiqua:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])
</head>
<body class="sprout-font sprout-auth-screen">
    <div class="sprout-shell">
        <div class="sprout-phone sprout-auth">
            <main class="sprout-auth__page">
                <div class="sprout-auth__content">
                    <div class="sprout-logo">
                        <img
                            src="/projectassets/images/logo/sprout-logo.svg"
                            alt="Sprout Logo"
                            class="sprout-logo__img"
                        >
                    </div>

                    <h1 class="sprout-title">Forgot your <span>password</span>?</h1>
                    <p class="sprout-subtitle">We will send a secure reset link to your email.</p>

                    <form method="POST" action="{{ route('password_email') }}" class="sprout-form" novalidate>
                        @csrf

                        @if (session('status'))
                            <div class="sprout-status">{{ session('status') }}</div>
                        @endif

                        <p class="sprout-auth__helper">
                            Enter the Gmail address connected to your Sprout account, and we will send you a formal password reset email.
                        </p>

                        <label class="sprout-label" for="email">Email</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            class="sprout-input"
                            placeholder="Enter Email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="email"
                        />

                        @error('email')
                            <div class="sprout-error">{{ $message }}</div>
                        @enderror

                        <button type="submit" class="sprout-btn">Send Reset Link</button>

                        <p class="sprout-foot">
                            Remembered your password?
                            <a href="{{ route('login_view') }}" class="sprout-link">Back to Login</a>
                        </p>
                    </form>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
