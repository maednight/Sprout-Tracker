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

<nav class="sprout-nav sprout-nav--mobile" aria-label="Bottom navigation">
@foreach ($items as $item)
    @php $isActive = $active === $item['route']; @endphp

    <a
        href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
        class="sprout-nav__item {{ $isActive ? 'is-active' : '' }}"
    >
        <span class="sprout-nav__icon" aria-hidden="true">
            <img src="{{ $item['icon'] }}" alt="" />
        </span>

        <span class="sprout-nav__label">{{ $item['label'] }}</span>
    </a>
@endforeach
</nav>
