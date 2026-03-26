<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Settings - Sprout</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inknut+Antiqua:wght@400;600;700&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="sprout-font">
@php
use Illuminate\Support\Str;

$mobileNavItems = [
    ['label' => 'Home', 'route' => 'dashboard', 'icon' => '/projectassets/icons/home.svg'],
    ['label' => 'Transaction', 'route' => 'transaction.index', 'icon' => '/projectassets/icons/transactions.svg'],
    ['label' => 'Budget', 'route' => 'budget.index', 'icon' => '/projectassets/icons/budget.svg'],
    ['label' => 'Savings', 'route' => 'savings.index', 'icon' => '/projectassets/icons/savings.svg'],
    ['label' => 'Settings', 'route' => 'settings.index', 'icon' => '/projectassets/icons/settings.svg'],
];

$mobileActive = request()->route()?->getName();

$desktopNavItems = [
    ['label' => 'Home', 'route' => 'dashboard', 'icon' => '/projectassets/icons/home.svg'],
    ['label' => 'Transaction', 'route' => 'transaction.index', 'icon' => '/projectassets/icons/transactions.svg'],
    ['label' => 'Budget', 'route' => 'budget.index', 'icon' => '/projectassets/icons/budget.svg'],
    ['label' => 'Savings', 'route' => 'savings.index', 'icon' => '/projectassets/icons/savings.svg'],
    ['label' => 'Settings', 'route' => 'settings.index', 'icon' => '/projectassets/icons/settings.svg'],
];

$desktopActive = request()->route()?->getName();

$profilePhotoUrl = $user->profile_photo_path
    ? asset('storage/' . $user->profile_photo_path)
    : null;

$emailSectionHasError = $errors->has('email') || $errors->has('current_password_for_email');
$passwordSectionHasError = $errors->has('current_password') || $errors->has('new_password') || $errors->has('new_password_confirmation');
@endphp

<div class="sprout-settings-page">
    <div class="sprout-settings-page__mobile">
        <main class="sprout-settings-mobile">
            @if (session('settings_success'))
                <div class="sprout-settings-mobile__alert sprout-settings-mobile__alert--success">
                    {{ session('settings_success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="sprout-settings-mobile__alert sprout-settings-mobile__alert--error">
                    {{ $errors->first() }}
                </div>
            @endif

            <section class="sprout-settings-mobile__card sprout-settings-mobile__profile-card">
                <details class="sprout-settings-mobile__photo-menu">
                    <summary class="sprout-settings-mobile__avatar-wrapper">
                        @if ($profilePhotoUrl)
                            <img
                                src="{{ $profilePhotoUrl }}"
                                alt="{{ $user->name }}"
                                class="sprout-settings-mobile__avatar-image"
                            >
                        @else
                            <div class="sprout-settings-mobile__avatar">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif

                        <span class="sprout-settings-mobile__avatar-badge">Edit</span>
                    </summary>

                    <div class="sprout-settings-mobile__photo-actions">
                        <form
                            action="{{ route('settings.photo.update') }}"
                            method="POST"
                            enctype="multipart/form-data"
                            class="sprout-settings-mobile__photo-form"
                        >
                            @csrf

                            <input
                                id="profile_photo"
                                name="profile_photo"
                                type="file"
                                accept="image/*"
                                class="sprout-settings-mobile__file-input"
                                onchange="this.form.submit()"
                            >

                            <label for="profile_photo" class="sprout-settings-mobile__photo-action">
                                Edit photo
                            </label>
                        </form>

                        @if ($profilePhotoUrl)
                            <form
                                action="{{ route('settings.photo.destroy') }}"
                                method="POST"
                                class="sprout-settings-mobile__photo-delete-form"
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="sprout-settings-mobile__photo-delete"
                                >
                                    Delete photo
                                </button>
                            </form>
                        @endif
                    </div>
                </details>

                @error('profile_photo')
                    <p class="sprout-settings-mobile__error sprout-settings-mobile__error--center">
                        {{ $message }}
                    </p>
                @enderror

                <div class="sprout-settings-mobile__profile-text">
                    <div class="sprout-settings-mobile__name-header">
                        <h2 class="sprout-settings-mobile__name">
                            {{ Str::limit($user->name, 26, '...') }}
                        </h2>

                        <button
                            type="button"
                            class="sprout-settings-mobile__edit-trigger"
                            onclick="document.getElementById('edit-name-form-mobile').classList.toggle('show')"
                            aria-label="Edit name"
                        >
                            ✎
                        </button>
                    </div>

                    <form
                        id="edit-name-form-mobile"
                        action="{{ route('settings.name.update') }}"
                        method="POST"
                        class="sprout-settings-mobile__edit-form {{ $errors->has('name') ? 'show' : '' }}"
                    >
                        @csrf
                        @method('PUT')

                        <input
                            type="text"
                            name="name"
                            value="{{ old('name', $user->name) }}"
                            class="sprout-settings-mobile__input"
                            placeholder="Enter your name"
                        >

                        @error('name')
                            <p class="sprout-settings-mobile__error">{{ $message }}</p>
                        @enderror

                        <button
                            type="submit"
                            class="sprout-settings-mobile__button sprout-settings-mobile__button--primary"
                        >
                            Save Name
                        </button>
                    </form>

                    <p class="sprout-settings-mobile__email">{{ $user->email }}</p>
                </div>
            </section>

            <section class="sprout-settings-mobile__card">
                <div class="sprout-settings-mobile__stats-row">
                    <div class="sprout-settings-mobile__stat-box">
                        <p class="sprout-settings-mobile__stat-value">{{ $stats['current_streak'] }}</p>
                        <p class="sprout-settings-mobile__stat-label">Streak</p>
                    </div>

                    <div class="sprout-settings-mobile__stat-box">
                        <p class="sprout-settings-mobile__stat-value">{{ $stats['total_days'] }}</p>
                        <p class="sprout-settings-mobile__stat-label">Days</p>
                    </div>

                    <div class="sprout-settings-mobile__stat-box">
                        <p class="sprout-settings-mobile__stat-value">{{ $stats['transactions'] }}</p>
                        <p class="sprout-settings-mobile__stat-label">Transactions</p>
                    </div>
                </div>
            </section>

            <section class="sprout-settings-mobile__card sprout-settings-mobile__menu-card">
                <details
                    class="sprout-settings-mobile__menu-item"
                    data-settings-details="email"
                    {{ $emailSectionHasError ? 'open' : '' }}
                >
                    <summary class="sprout-settings-mobile__menu-summary">
                        <span>Change Email</span>
                        <span class="sprout-settings-mobile__menu-arrow">›</span>
                    </summary>

                    <form
                        action="{{ route('settings.email.update') }}"
                        method="POST"
                        class="sprout-settings-mobile__form sprout-settings-mobile__menu-form"
                    >
                        @csrf
                        @method('PUT')

                        <div class="sprout-settings-mobile__field">
                            <label for="email" class="sprout-settings-mobile__field-label">New Email</label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email', $user->email) }}"
                                class="sprout-settings-mobile__input"
                                placeholder="Enter your new email"
                            >
                            @error('email')
                                <p class="sprout-settings-mobile__error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sprout-settings-mobile__field">
                            <label for="current_password_for_email" class="sprout-settings-mobile__field-label">Current Password</label>

                            <div class="sprout-settings-mobile__password-wrap">
                                <input
                                    id="current_password_for_email"
                                    name="current_password_for_email"
                                    type="password"
                                    class="sprout-settings-mobile__input sprout-settings-mobile__input--password"
                                    placeholder="Enter current password"
                                >

                                <button
                                    type="button"
                                    class="sprout-settings-mobile__password-toggle"
                                    data-password-toggle
                                    aria-label="Show password"
                                >
                                    <img
                                        src="{{ asset('projectassets/icons/eyeclose.svg') }}"
                                        alt="Hidden password"
                                        class="sprout-settings-mobile__password-toggle-icon"
                                    >
                                </button>
                            </div>

                            @error('current_password_for_email')
                                <p class="sprout-settings-mobile__error">{{ $message }}</p>
                            @enderror
                        </div>

                        <button
                            type="submit"
                            class="sprout-settings-mobile__button sprout-settings-mobile__button--primary"
                        >
                            Save Email
                        </button>
                    </form>
                </details>

                <details
                    class="sprout-settings-mobile__menu-item"
                    data-settings-details="password"
                    {{ $passwordSectionHasError ? 'open' : '' }}
                >
                    <summary class="sprout-settings-mobile__menu-summary">
                        <span>Change Password</span>
                        <span class="sprout-settings-mobile__menu-arrow">›</span>
                    </summary>

                    <form
                        action="{{ route('settings.password.update') }}"
                        method="POST"
                        class="sprout-settings-mobile__form sprout-settings-mobile__menu-form"
                    >
                        @csrf
                        @method('PUT')

                        <div class="sprout-settings-mobile__field">
                            <label for="current_password" class="sprout-settings-mobile__field-label">Current Password</label>

                            <div class="sprout-settings-mobile__password-wrap">
                                <input
                                    id="current_password"
                                    name="current_password"
                                    type="password"
                                    class="sprout-settings-mobile__input sprout-settings-mobile__input--password"
                                    placeholder="Enter current password"
                                >

                                <button
                                    type="button"
                                    class="sprout-settings-mobile__password-toggle"
                                    data-password-toggle
                                    aria-label="Show password"
                                >
                                    <img
                                        src="{{ asset('projectassets/icons/eyeclose.svg') }}"
                                        alt="Hidden password"
                                        class="sprout-settings-mobile__password-toggle-icon"
                                    >
                                </button>
                            </div>

                            @error('current_password')
                                <p class="sprout-settings-mobile__error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sprout-settings-mobile__field">
                            <label for="new_password" class="sprout-settings-mobile__field-label">New Password</label>

                            <div class="sprout-settings-mobile__password-wrap">
                                <input
                                    id="new_password"
                                    name="new_password"
                                    type="password"
                                    class="sprout-settings-mobile__input sprout-settings-mobile__input--password"
                                    placeholder="Enter new password"
                                >

                                <button
                                    type="button"
                                    class="sprout-settings-mobile__password-toggle"
                                    data-password-toggle
                                    aria-label="Show password"
                                >
                                    <img
                                        src="{{ asset('projectassets/icons/eyeclose.svg') }}"
                                        alt="Hidden password"
                                        class="sprout-settings-mobile__password-toggle-icon"
                                    >
                                </button>
                            </div>

                            @error('new_password')
                                <p class="sprout-settings-mobile__error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sprout-settings-mobile__field">
                            <label for="new_password_confirmation" class="sprout-settings-mobile__field-label">Confirm New Password</label>

                            <div class="sprout-settings-mobile__password-wrap">
                                <input
                                    id="new_password_confirmation"
                                    name="new_password_confirmation"
                                    type="password"
                                    class="sprout-settings-mobile__input sprout-settings-mobile__input--password"
                                    placeholder="Confirm new password"
                                >

                                <button
                                    type="button"
                                    class="sprout-settings-mobile__password-toggle"
                                    data-password-toggle
                                    aria-label="Show password"
                                >
                                    <img
                                        src="{{ asset('projectassets/icons/eyeclose.svg') }}"
                                        alt="Hidden password"
                                        class="sprout-settings-mobile__password-toggle-icon"
                                    >
                                </button>
                            </div>

                            @error('new_password_confirmation')
                                <p class="sprout-settings-mobile__error">{{ $message }}</p>
                            @enderror
                        </div>

                        <button
                            type="submit"
                            class="sprout-settings-mobile__button sprout-settings-mobile__button--primary"
                        >
                            Save Password
                        </button>
                    </form>
                </details>
            </section>

            <section class="sprout-settings-mobile__bottom">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button
                        type="submit"
                        class="sprout-settings-mobile__button sprout-settings-mobile__button--danger"
                    >
                        Sign Out
                    </button>
                </form>
            </section>
        </main>

        <nav class="sprout-nav sprout-nav--mobile" aria-label="Bottom navigation">
            @foreach ($mobileNavItems as $item)
                @php $isActive = $mobileActive === $item['route']; @endphp

                <a
                    href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
                    class="sprout-nav__item {{ $isActive ? 'is-active' : '' }}"
                >
                    <span class="sprout-nav__icon" aria-hidden="true">
                        <img src="{{ $item['icon'] }}" alt="">
                    </span>

                    <span class="sprout-nav__label">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </div>

    <div class="sprout-settings-page__desktop">
        <div class="sprout-settings-desktop">
            <aside class="sprout-sidebar" aria-label="Sidebar navigation">
                <div class="sprout-sidebar__brand">
                    <img src="/projectassets/images/logo/sprout-logo.svg" alt="Sprout" class="sprout-sidebar__logo">
                    <div class="sprout-sidebar__brandtext">
                        <div class="sprout-sidebar__name">Sprout</div>
                        <div class="sprout-sidebar__tag">Income & Expense Tracker</div>
                    </div>
                </div>

                <nav class="sprout-sidebar__nav">
                    @foreach ($desktopNavItems as $item)
                        @php $isActive = $desktopActive === $item['route']; @endphp

                        <a
                            href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
                            class="sprout-sideitem {{ $isActive ? 'is-active' : '' }}"
                        >
                            <span class="sprout-sideitem__icon" aria-hidden="true">
                                <img src="{{ $item['icon'] }}" alt="">
                            </span>

                            <span class="sprout-sideitem__label">{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </nav>

                <div class="sprout-sidebar__footer">
                    <div class="sprout-sidebar__hint">Your account settings live here.</div>
                </div>
            </aside>

            <main class="sprout-settings-desktop__content">
                @if (session('settings_success'))
                    <div class="sprout-settings-mobile__alert sprout-settings-mobile__alert--success sprout-settings-desktop__alert">
                        {{ session('settings_success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="sprout-settings-mobile__alert sprout-settings-mobile__alert--error sprout-settings-desktop__alert">
                        {{ $errors->first() }}
                    </div>
                @endif

                <section class="sprout-settings-desktop__profile-card">
                    <details class="sprout-settings-mobile__photo-menu">
                        <summary class="sprout-settings-mobile__avatar-wrapper sprout-settings-mobile__avatar-wrapper--desktop">
                            @if ($profilePhotoUrl)
                                <img
                                    src="{{ $profilePhotoUrl }}"
                                    alt="{{ $user->name }}"
                                    class="sprout-settings-mobile__avatar-image sprout-settings-mobile__avatar-image--desktop"
                                >
                            @else
                                <div class="sprout-settings-mobile__avatar sprout-settings-mobile__avatar--desktop">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            @endif

                            <span class="sprout-settings-mobile__avatar-badge">Edit</span>
                        </summary>

                        <div class="sprout-settings-mobile__photo-actions">
                            <form
                                action="{{ route('settings.photo.update') }}"
                                method="POST"
                                enctype="multipart/form-data"
                                class="sprout-settings-mobile__photo-form"
                            >
                                @csrf

                                <input
                                    id="desktop_profile_photo"
                                    name="profile_photo"
                                    type="file"
                                    accept="image/*"
                                    class="sprout-settings-mobile__file-input"
                                    onchange="this.form.submit()"
                                >

                                <label for="desktop_profile_photo" class="sprout-settings-mobile__photo-action">
                                    Edit photo
                                </label>
                            </form>

                            @if ($profilePhotoUrl)
                                <form
                                    action="{{ route('settings.photo.destroy') }}"
                                    method="POST"
                                    class="sprout-settings-mobile__photo-delete-form"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="sprout-settings-mobile__photo-delete"
                                    >
                                        Delete photo
                                    </button>
                                </form>
                            @endif
                        </div>
                    </details>

                    @error('profile_photo')
                        <p class="sprout-settings-mobile__error sprout-settings-mobile__error--center">
                            {{ $message }}
                        </p>
                    @enderror

                    <div class="sprout-settings-desktop__profile-text">
                        <div class="sprout-settings-mobile__name-header">
                            <h2 class="sprout-settings-desktop__name">
                                {{ Str::limit($user->name, 32, '...') }}
                            </h2>

                            <button
                                type="button"
                                class="sprout-settings-mobile__edit-trigger"
                                onclick="document.getElementById('edit-name-form-desktop').classList.toggle('show')"
                                aria-label="Edit name"
                            >
                                ✎
                            </button>
                        </div>

                        <form
                            id="edit-name-form-desktop"
                            action="{{ route('settings.name.update') }}"
                            method="POST"
                            class="sprout-settings-mobile__edit-form sprout-settings-mobile__inline-form--desktop {{ $errors->has('name') ? 'show' : '' }}"
                        >
                            @csrf
                            @method('PUT')

                            <input
                                type="text"
                                name="name"
                                value="{{ old('name', $user->name) }}"
                                class="sprout-settings-mobile__input"
                                placeholder="Enter your name"
                            >

                            @error('name')
                                <p class="sprout-settings-mobile__error">{{ $message }}</p>
                            @enderror

                            <button
                                type="submit"
                                class="sprout-settings-mobile__button sprout-settings-mobile__button--primary"
                            >
                                Save Name
                            </button>
                        </form>

                        <p class="sprout-settings-desktop__email">{{ $user->email }}</p>
                    </div>
                </section>

                <section class="sprout-settings-desktop__stats-row">
                    <div class="sprout-settings-mobile__stat-box">
                        <p class="sprout-settings-mobile__stat-value">{{ $stats['current_streak'] }}</p>
                        <p class="sprout-settings-mobile__stat-label">Streak</p>
                    </div>

                    <div class="sprout-settings-mobile__stat-box">
                        <p class="sprout-settings-mobile__stat-value">{{ $stats['total_days'] }}</p>
                        <p class="sprout-settings-mobile__stat-label">Days</p>
                    </div>

                    <div class="sprout-settings-mobile__stat-box">
                        <p class="sprout-settings-mobile__stat-value">{{ $stats['transactions'] }}</p>
                        <p class="sprout-settings-mobile__stat-label">Transactions</p>
                    </div>
                </section>

                <section class="sprout-settings-desktop__security-grid">
                    <div class="sprout-settings-mobile__card sprout-settings-mobile__menu-card">
                        <details
                            class="sprout-settings-mobile__menu-item"
                            data-settings-details="desktop-email"
                            {{ $emailSectionHasError ? 'open' : '' }}
                        >
                            <summary class="sprout-settings-mobile__menu-summary">
                                <span>Change Email</span>
                                <span class="sprout-settings-mobile__menu-arrow">›</span>
                            </summary>

                            <form
                                action="{{ route('settings.email.update') }}"
                                method="POST"
                                class="sprout-settings-mobile__form sprout-settings-mobile__menu-form"
                            >
                                @csrf
                                @method('PUT')

                                <div class="sprout-settings-mobile__field">
                                    <label for="desktop_email" class="sprout-settings-mobile__field-label">New Email</label>
                                    <input
                                        id="desktop_email"
                                        name="email"
                                        type="email"
                                        value="{{ old('email', $user->email) }}"
                                        class="sprout-settings-mobile__input"
                                        placeholder="Enter your new email"
                                    >
                                    @error('email')
                                        <p class="sprout-settings-mobile__error">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="sprout-settings-mobile__field">
                                    <label for="desktop_current_password_for_email" class="sprout-settings-mobile__field-label">Current Password</label>

                                    <div class="sprout-settings-mobile__password-wrap">
                                        <input
                                            id="desktop_current_password_for_email"
                                            name="current_password_for_email"
                                            type="password"
                                            class="sprout-settings-mobile__input sprout-settings-mobile__input--password"
                                            placeholder="Enter current password"
                                        >

                                        <button
                                            type="button"
                                            class="sprout-settings-mobile__password-toggle"
                                            data-password-toggle
                                            aria-label="Show password"
                                        >
                                            <img
                                                src="{{ asset('projectassets/icons/eyeclose.svg') }}"
                                                alt="Hidden password"
                                                class="sprout-settings-mobile__password-toggle-icon"
                                            >
                                        </button>
                                    </div>

                                    @error('current_password_for_email')
                                        <p class="sprout-settings-mobile__error">{{ $message }}</p>
                                    @enderror
                                </div>

                                <button
                                    type="submit"
                                    class="sprout-settings-mobile__button sprout-settings-mobile__button--primary"
                                >
                                    Save Email
                                </button>
                            </form>
                        </details>
                    </div>

                    <div class="sprout-settings-mobile__card sprout-settings-mobile__menu-card">
                        <details
                            class="sprout-settings-mobile__menu-item"
                            data-settings-details="desktop-password"
                            {{ $passwordSectionHasError ? 'open' : '' }}
                        >
                            <summary class="sprout-settings-mobile__menu-summary">
                                <span>Change Password</span>
                                <span class="sprout-settings-mobile__menu-arrow">›</span>
                            </summary>

                            <form
                                action="{{ route('settings.password.update') }}"
                                method="POST"
                                class="sprout-settings-mobile__form sprout-settings-mobile__menu-form"
                            >
                                @csrf
                                @method('PUT')

                                <div class="sprout-settings-mobile__field">
                                    <label for="desktop_current_password" class="sprout-settings-mobile__field-label">Current Password</label>

                                    <div class="sprout-settings-mobile__password-wrap">
                                        <input
                                            id="desktop_current_password"
                                            name="current_password"
                                            type="password"
                                            class="sprout-settings-mobile__input sprout-settings-mobile__input--password"
                                            placeholder="Enter current password"
                                        >

                                        <button
                                            type="button"
                                            class="sprout-settings-mobile__password-toggle"
                                            data-password-toggle
                                            aria-label="Show password"
                                        >
                                            <img
                                                src="{{ asset('projectassets/icons/eyeclose.svg') }}"
                                                alt="Hidden password"
                                                class="sprout-settings-mobile__password-toggle-icon"
                                            >
                                        </button>
                                    </div>

                                    @error('current_password')
                                        <p class="sprout-settings-mobile__error">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="sprout-settings-mobile__field">
                                    <label for="desktop_new_password" class="sprout-settings-mobile__field-label">New Password</label>

                                    <div class="sprout-settings-mobile__password-wrap">
                                        <input
                                            id="desktop_new_password"
                                            name="new_password"
                                            type="password"
                                            class="sprout-settings-mobile__input sprout-settings-mobile__input--password"
                                            placeholder="Enter new password"
                                        >

                                        <button
                                            type="button"
                                            class="sprout-settings-mobile__password-toggle"
                                            data-password-toggle
                                            aria-label="Show password"
                                        >
                                            <img
                                                src="{{ asset('projectassets/icons/eyeclose.svg') }}"
                                                alt="Hidden password"
                                                class="sprout-settings-mobile__password-toggle-icon"
                                            >
                                        </button>
                                    </div>

                                    @error('new_password')
                                        <p class="sprout-settings-mobile__error">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="sprout-settings-mobile__field">
                                    <label for="desktop_new_password_confirmation" class="sprout-settings-mobile__field-label">Confirm New Password</label>

                                    <div class="sprout-settings-mobile__password-wrap">
                                        <input
                                            id="desktop_new_password_confirmation"
                                            name="new_password_confirmation"
                                            type="password"
                                            class="sprout-settings-mobile__input sprout-settings-mobile__input--password"
                                            placeholder="Confirm new password"
                                        >

                                        <button
                                            type="button"
                                            class="sprout-settings-mobile__password-toggle"
                                            data-password-toggle
                                            aria-label="Show password"
                                        >
                                            <img
                                                src="{{ asset('projectassets/icons/eyeclose.svg') }}"
                                                alt="Hidden password"
                                                class="sprout-settings-mobile__password-toggle-icon"
                                            >
                                        </button>
                                    </div>

                                    @error('new_password_confirmation')
                                        <p class="sprout-settings-mobile__error">{{ $message }}</p>
                                    @enderror
                                </div>

                                <button
                                    type="submit"
                                    class="sprout-settings-mobile__button sprout-settings-mobile__button--primary"
                                >
                                    Save Password
                                </button>
                            </form>
                        </details>
                    </div>
                </section>

                <section class="sprout-settings-desktop__logout">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button
                            type="submit"
                            class="sprout-settings-mobile__button sprout-settings-mobile__button--danger sprout-settings-desktop__logout-button"
                        >
                            Sign Out
                        </button>
                    </form>
                </section>
            </main>
        </div>
    </div>
</div>

</body>
</html>
