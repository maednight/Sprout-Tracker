<div class="sprout-desktop">
    <div class="sprout-desktop__wrap">
        <div class="sprout-desktop__card">

            <div class="sprout-desktop__left">
                <div class="sprout-left__content">
                    <h1 class="sprout-title sprout-title--desktop">
                        Create your
                        <span class="sprout-word">
                            <img
                                src="/projectassets/images/logo/sprout-logo.svg"
                                alt="Sprout Logo"
                                class="sprout-word__logo"
                            >
                            <span class="sprout-word__text">Sprout</span>
                        </span>
                        account
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
                <form method="POST" action="{{ route('signup') }}" class="sprout-form sprout-form--desktop sprout-form--signup" novalidate>
                    @csrf

                    <label class="sprout-label" for="name_desktop">Full Name</label>
                    <input
                        id="name_desktop"
                        name="name"
                        type="text"
                        class="sprout-input"
                        placeholder="Enter Full Name"
                        value="{{ old('name') }}"
                        required
                        autocomplete="name"
                    />

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
                        placeholder="Create Password"
                        required
                        autocomplete="new-password"
                    />

                    <label class="sprout-label" for="password_confirmation_desktop">Confirm Password</label>
                    <input
                        id="password_confirmation_desktop"
                        name="password_confirmation"
                        type="password"
                        class="sprout-input"
                        placeholder="Confirm Password"
                        required
                        autocomplete="new-password"
                    />

                    @if ($errors->any())
                        <div class="sprout-error">{{ $errors->first() }}</div>
                    @endif

                    <button type="submit" class="sprout-btn">Sign Up</button>

                    <p class="sprout-foot">
                        Already have an account?
                        <a href="{{ route('login.view') }}" class="sprout-link">Log In</a>
                    </p>
                </form>
            </div>

        </div>
    </div>
</div>