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
                Create your <span>Sprout</span> account
            </h1>

            <p class="sprout-subtitle">Start tracking your money today</p>

            <form method="POST" action="{{ route('signup') }}" class="sprout-form sprout-form--signup" novalidate>
                @csrf

                <label class="sprout-label" for="name">Full Name</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    class="sprout-input"
                    placeholder="Enter Full Name"
                    value="{{ old('name') }}"
                    required
                    autocomplete="name"
                />

                @error('name')
                    <div class="sprout-error">{{ $message }}</div>
                @enderror

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
                        placeholder="Create Password"
                        required
                        autocomplete="new-password"
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

                <label class="sprout-label" for="password_confirmation">Confirm Password</label>

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

                    <button
                        type="button"
                        class="sprout-password-toggle"
                        aria-label="Show password"
                        onclick="
                            const input = document.getElementById('password_confirmation');
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

                @error('password_confirmation')
                    <div class="sprout-error">{{ $message }}</div>
                @enderror

                <button type="submit" class="sprout-btn">Sign Up</button>

                <p class="sprout-foot">
                    Already have an account?
                    <a href="{{ route('login.view') }}" class="sprout-link">Log In</a>
                </p>
            </form>

        </div>
    </main>
</div>