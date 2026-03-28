<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Create New Password - Sprout</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inknut+Antiqua:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])
</head>
<body class="sprout-font">
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

                    <h1 class="sprout-title">Create new <span>password</span></h1>
                    <p class="sprout-subtitle">Choose a strong new password for your Sprout account.</p>

                    <form method="POST" action="{{ route('password_update') }}" class="sprout-form" novalidate>
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

                        <label class="sprout-label" for="email">Email</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            class="sprout-input"
                            placeholder="Enter Email"
                            value="{{ old('email', $email) }}"
                            required
                            autocomplete="email"
                        />

                        @error('email')
                            <div class="sprout-error">{{ $message }}</div>
                        @enderror

                        <label class="sprout-label" for="password">New Password</label>
                        <div class="sprout-password-field">
                            <input
                                id="password"
                                name="password"
                                type="password"
                                class="sprout-input sprout-input--password"
                                placeholder="Create Password"
                                required
                                autocomplete="new-password"
                            />
                        </div>

                        @error('password')
                            <div class="sprout-error">{{ $message }}</div>
                        @enderror

                        <label class="sprout-label" for="password_confirmation">Confirm New Password</label>
                        <div class="sprout-password-field">
                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                class="sprout-input sprout-input--password"
                                placeholder="Confirm Password"
                                required
                                autocomplete="new-password"
                            />
                        </div>

                        @error('password_confirmation')
                            <div class="sprout-error">{{ $message }}</div>
                        @enderror

                        <button type="submit" class="sprout-btn">Save New Password</button>

                        <p class="sprout-foot">
                            Back to
                            <a href="{{ route('login_view') }}" class="sprout-link">Login</a>
                        </p>
                    </form>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
