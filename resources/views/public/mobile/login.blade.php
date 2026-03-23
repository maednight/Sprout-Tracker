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

            <h1 class="sprout-title">
                Get Started with <span>Sprout</span>
            </h1>

            <p class="sprout-subtitle">Grow Your Money, One Entry at a Time</p>

            <form method="POST" action="{{ route('login') }}" class="sprout-form" novalidate>
                @csrf

                @if (session('status'))
                    <div class="sprout-status">{{ session('status') }}</div>
                @endif

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

                <label class="sprout-label" for="password">Password</label>

                <div class="sprout-password-field">
                    <input
                        id="password"
                        name="password"
                        type="password"
                        class="sprout-input sprout-input--password"
                        placeholder="Password"
                        required
                        autocomplete="current-password"
                    />

                    <button
                        type="button"
                        class="sprout-password-toggle"
                        aria-label="Show password"
                        onclick="
                            const input = document.getElementById('password');
                            const icon = this.querySelector('img');
                            const openIcon = '/projectassets/icons/eyeopen.svg';
                            const closeIcon = '/projectassets/icons/eyeclose.svg';

                            if (input.type === 'password') {
                                input.type = 'text';
                                icon.src = openIcon;
                                this.setAttribute('aria-label', 'Hide password');
                            } else {
                                input.type = 'password';
                                icon.src = closeIcon;
                                this.setAttribute('aria-label', 'Show password');
                            }
                        "
                    >
                        <img
                            src="/projectassets/icons/eyeclose.svg"
                            alt=""
                            class="sprout-password-toggle__icon"
                        >
                    </button>
                </div>

                @error('password')
                    <div class="sprout-error">{{ $message }}</div>
                @enderror

                <a href="{{ route('password.request') }}" class="sprout-forgot-link">Forgot Password?</a>

                <button type="submit" class="sprout-btn">Log In</button>

                <p class="sprout-foot">
                    Doesn’t have an account?
                    <a href="{{ route('signup.view') }}" class="sprout-link">Sign Up</a>
                </p>
            </form>

        </div>
    </main>
</div>
