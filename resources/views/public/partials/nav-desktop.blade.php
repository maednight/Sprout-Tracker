@php
    $items = [
        ['label' => 'Home', 'route' => 'dashboard', 'icon' => '/projectassets/icons/home.svg'],
        ['label' => 'Transaction', 'route' => 'transaction.index', 'icon' => '/projectassets/icons/transactions.svg'],
        ['label' => 'Budget', 'route' => 'budget.index', 'icon' => '/projectassets/icons/budget.svg'],
        ['label' => 'Savings', 'route' => 'savings.index', 'icon' => '/projectassets/icons/savings.svg'],
        ['label' => 'Settings', 'route' => 'settings.index', 'icon' => '/projectassets/icons/settings.svg'],
    ];

    $active = request()->route()?->getName();
@endphp

<aside class="sprout-sidebar" aria-label="Sidebar navigation">
    <div class="sprout-sidebar__brand">
        <img src="/projectassets/images/logo/sprout-logo.svg" alt="Sprout" class="sprout-sidebar__logo">
        <div class="sprout-sidebar__brandtext">
            <div class="sprout-sidebar__name">Sprout</div>
            <div class="sprout-sidebar__tag">Income & Expense Tracker</div>
        </div>
    </div>

    <nav class="sprout-sidebar__nav">
        @foreach ($items as $item)
            @php $isActive = $active === $item['route']; @endphp

            <a
                href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
                class="sprout-sideitem {{ $isActive ? 'is-active' : '' }}"
            >
                <span class="sprout-sideitem__icon" aria-hidden="true">
                    <img src="{{ $item['icon'] }}" alt="" />
                </span>

                <span class="sprout-sideitem__label">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="sprout-sidebar__footer">
        <div class="sprout-sidebar__hint">Tip: Use filters to view “This Month” quickly.</div>
    </div>
</aside>
