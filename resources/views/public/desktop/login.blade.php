<div class="sprout-desktop">
    <div class="sprout-desktop__wrap">
        <div class="sprout-desktop__card">

            <div class="sprout-desktop__left">
                <div class="sprout-left__content">
                    <h1 class="sprout-title sprout-title--desktop">
                        Get Started with
                        <span class="sprout-word">
                            <img
                                src="/projectassets/images/logo/sprout-logo.svg"
                                alt="Sprout Logo"
                                class="sprout-word__logo"
                            >
                            <span class="sprout-word__text">Sprout</span>
                        </span>
                    </h1>

                    <p class="sprout-subtitle sprout-subtitle--desktop">
                        Grow Your Money, One Entry at a Time
                    </p>

                    <p class="sprout-desktop__hint">
                        Track income • Track expenses • View summaries
                    </p>
                </div>
            </div>

            <div class="sprout-desktop__right">
                <form method="POST" action="{{ route('login') }}" class="sprout-form sprout-form--desktop" novalidate>
                    @csrf

                    <label class="sprout-label" for="email_desktop">Email</label>
                    <input
                        id="email_desktop"
                        name="email"
                        type="email"
                        class="sprout-input"
                        placeholder="Enter Email"
                        value="{{ old('email') }}"
                        required
                        autocomplete="email"
                    />

                    <label class="sprout-label" for="password_desktop">Password</label>
                    <input
                        id="password_desktop"
                        name="password"
                        type="password"
                        class="sprout-input"
                        placeholder="Password"
                        required
                        autocomplete="current-password"
                    />

                    @if ($errors->any())
                        <div class="sprout-error">{{ $errors->first() }}</div>
                    @endif

                    <button type="submit" class="sprout-btn">Log In</button>

                    <p class="sprout-foot">
                        Doesn’t have an account?
                        <a href="{{ route('signup') }}" class="sprout-link">Sign Up</a>
                    </p>
                </form>
            </div>

        </div>
    </div>
</div>