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

                <label class="sprout-label" for="password">Password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    class="sprout-input"
                    placeholder="Create Password"
                    required
                    autocomplete="new-password"
                />

                <label class="sprout-label" for="password_confirmation">Confirm Password</label>
                <input
                    id="password_confirmation"
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
    </main>
</div>