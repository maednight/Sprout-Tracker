<div class="sprout-phone sprout-auth">
    <main class="sprout-auth__page">
        <div class="sprout-auth__content">

            <!-- ✅ Real Logo Asset -->
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
                    <a href="#" class="sprout-link">Sign Up</a>
                </p>
            </form>

        </div>
    </main>
</div>