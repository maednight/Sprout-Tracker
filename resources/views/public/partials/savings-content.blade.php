@php
    $savingsScope = $savingsScope ?? 'mobile';
    $totalWorth = $savingsPayload['totalWorth'] ?? 0;
    $categories = collect($savingsPayload['categories'] ?? []);
    $historyItems = collect($savingsPayload['history'] ?? []);
    $accounts = $savingsPayload['accounts'] ?? [];
    $defaultTransferDate = $savingsPayload['defaultTransferDate'] ?? now()->format('Y-m-d');
    $scope = $savingsPayload['scope'] ?? 'month';
    $anchorDate = $savingsPayload['anchorDate'] ?? now()->format('Y-m-d');
    $periodLabel = $savingsPayload['periodLabel'] ?? now()->format('F Y');
    $previousPeriodUrl = $savingsPayload['previousPeriodUrl'] ?? route('savings.index');
    $nextPeriodUrl = $savingsPayload['nextPeriodUrl'] ?? route('savings.index');
    $scopeUrls = $savingsPayload['scopeUrls'] ?? [];
@endphp

<div class="sprout-savings__panel-shell" data-savings-panel data-savings-index-url="{{ route('savings.index') }}" data-savings-scope="{{ $scope }}" data-savings-anchor="{{ $anchorDate }}">
<input type="hidden" value="{{ csrf_token() }}" data-savings-csrf-token>
<button type="button" class="sprout-savings__backdrop sprout-savings__backdrop--hidden" data-savings-backdrop aria-label="Close savings period picker"></button>
<section class="sprout-savings__panel">
    @if (session('savings_success'))
        <div class="sprout-savings__alert sprout-savings__alert--success">
            {{ session('savings_success') }}
        </div>
    @endif

    @if ($errors->has('transfer_amount'))
        <div class="sprout-savings__alert sprout-savings__alert--error">
            {{ $errors->first('transfer_amount') }}
        </div>
    @endif

    <div class="sprout-savings__controls">
        <div class="sprout-savings__period">
            <a href="{{ $previousPeriodUrl }}" class="sprout-savings__period-arrow" aria-label="Previous period">
                &lsaquo;
            </a>
            <button type="button" class="sprout-savings__period-label" data-savings-period-trigger>
                {{ $periodLabel }}
            </button>
            <a href="{{ $nextPeriodUrl }}" class="sprout-savings__period-arrow" aria-label="Next period">
                &rsaquo;
            </a>
        </div>

        <section class="sprout-savings__period-panel sprout-savings__period-panel--hidden" data-savings-period-panel>
            <div class="sprout-savings__scope-tabs">
                <button type="button" class="sprout-savings__scope-tab {{ $scope === 'week' ? 'sprout-savings__scope-tab--active' : '' }}" data-savings-period-view="week">Week</button>
                <button type="button" class="sprout-savings__scope-tab {{ $scope === 'month' ? 'sprout-savings__scope-tab--active' : '' }}" data-savings-period-view="month">Month</button>
                <button type="button" class="sprout-savings__scope-tab {{ $scope === 'year' ? 'sprout-savings__scope-tab--active' : '' }}" data-savings-period-view="year">Year</button>
            </div>

            <div class="sprout-savings__picker-view" data-savings-picker-view="week">
                <div class="sprout-savings__picker-head">
                    <button type="button" class="sprout-savings__picker-arrow" data-savings-week-shift="-1" aria-label="Previous month">&lsaquo;</button>
                    <div class="sprout-savings__picker-value" data-savings-week-label></div>
                    <button type="button" class="sprout-savings__picker-arrow" data-savings-week-shift="1" aria-label="Next month">&rsaquo;</button>
                </div>

                <div class="sprout-savings__picker-weekdays">
                    <span>Mon</span>
                    <span>Tue</span>
                    <span>Wed</span>
                    <span>Thu</span>
                    <span>Fri</span>
                    <span>Sat</span>
                    <span>Sun</span>
                </div>

                <div class="sprout-savings__week-grid" data-savings-week-grid></div>
            </div>

            <div class="sprout-savings__picker-view sprout-savings__picker-view--hidden" data-savings-picker-view="month">
                <div class="sprout-savings__picker-head">
                    <button type="button" class="sprout-savings__picker-arrow" data-savings-month-year-shift="-1" aria-label="Previous year">&lsaquo;</button>
                    <div class="sprout-savings__picker-value" data-savings-month-year></div>
                    <button type="button" class="sprout-savings__picker-arrow" data-savings-month-year-shift="1" aria-label="Next year">&rsaquo;</button>
                </div>

                <div class="sprout-savings__month-grid" data-savings-month-grid></div>
            </div>

            <div class="sprout-savings__picker-view sprout-savings__picker-view--hidden" data-savings-picker-view="year">
                <div class="sprout-savings__picker-head">
                    <button type="button" class="sprout-savings__picker-arrow" data-savings-display-year-shift="-1" aria-label="Previous year">&lsaquo;</button>
                    <div class="sprout-savings__picker-value" data-savings-display-year></div>
                    <button type="button" class="sprout-savings__picker-arrow" data-savings-display-year-shift="1" aria-label="Next year">&rsaquo;</button>
                </div>

                <div class="sprout-savings__month-grid" data-savings-year-grid></div>
            </div>
        </section>
    </div>

    <div class="sprout-savings__hero">
        <div class="sprout-savings__hero-head">
            <div class="sprout-savings__hero-slot sprout-savings__hero-slot--left">
                <button type="button" class="sprout-savings__worth-toggle-pill" data-savings-worth-toggle aria-label="Hide savings worth" aria-pressed="false">
                    <img src="/projectassets/icons/eyeopen.svg" alt="" class="sprout-savings__worth-toggle-icon" data-savings-worth-icon>
                </button>
            </div>

            <div class="sprout-savings__worth-block">
                <p class="sprout-savings__eyebrow">Savings Worth</p>
                <div class="sprout-savings__worth-row">
                    <h1 class="sprout-savings__title" data-savings-worth-value>&#8369;{{ number_format($totalWorth, 0) }}</h1>
                </div>
            </div>

            <div class="sprout-savings__hero-slot sprout-savings__hero-slot--right">
                <div class="sprout-savings__activity-filter-wrap">
                    <button type="button" class="sprout-savings__activity-filter-button" data-savings-sort-trigger aria-label="Sort savings activity">
                        <img src="/projectassets/icons/filtericon.svg" alt="">
                    </button>

                    <div class="sprout-savings__activity-filter-menu sprout-savings__activity-filter-menu--hidden" data-savings-sort-menu>
                        <button type="button" class="sprout-savings__activity-filter-option sprout-savings__activity-filter-option--active" data-savings-sort="newest">
                            <img src="/projectassets/icons/filtericon.svg" alt="">
                            <span>Newest to oldest</span>
                        </button>
                        <button type="button" class="sprout-savings__activity-filter-option" data-savings-sort="oldest">
                            <img src="/projectassets/icons/filtericon.svg" alt="">
                            <span>Oldest to newest</span>
                        </button>
                        <button type="button" class="sprout-savings__activity-filter-option" data-savings-sort="highest">
                            <img src="/projectassets/icons/filtericon.svg" alt="">
                            <span>Price: highest to lowest</span>
                        </button>
                        <button type="button" class="sprout-savings__activity-filter-option" data-savings-sort="lowest">
                            <img src="/projectassets/icons/filtericon.svg" alt="">
                            <span>Price: lowest to highest</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <section class="sprout-savings__history">
        <div class="sprout-savings__section-title">Savings Activity</div>

        @forelse ($historyItems as $item)
            <button
                type="button"
                class="sprout-savings__history-card sprout-savings__history-card--button"
                data-savings-history-item='@json($item)'
            >
                <div class="sprout-savings__history-head">
                    <div class="sprout-savings__history-date">{{ $item['dateLabel'] }}</div>
                    <div class="sprout-savings__history-state sprout-savings__history-state--{{ $item['direction'] }}">
                        {{ $item['direction'] === 'out' ? 'OUT' : 'IN' }} &#8369;{{ number_format($item['amount'], 0) }}
                    </div>
                </div>

                <div class="sprout-savings__history-row">
                    <div class="sprout-savings__history-left">
                        <div class="sprout-savings__history-icon" style="background: {{ $item['categoryColor'] ?? '#2d9af0' }};">
                            <img src="{{ $item['iconPath'] }}" alt="{{ $item['category'] }}" class="sprout-savings__history-icon-image">
                        </div>

                        <div class="sprout-savings__history-copy">
                            <div class="sprout-savings__history-category">{{ $item['category'] }}</div>

                            @if (!empty($item['description']))
                                <div class="sprout-savings__history-description">Desc: {{ $item['description'] }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="sprout-savings__history-right">
                        <div class="sprout-savings__history-amount sprout-savings__history-amount--{{ $item['direction'] }}">
                            {{ $item['direction'] === 'out' ? '-' : '+' }}&#8369;{{ number_format($item['amount'], 0) }}
                        </div>
                        <div class="sprout-savings__history-time">{{ $item['time'] }}</div>
                    </div>
                </div>
            </button>
        @empty
            <div class="sprout-savings__history-empty">
                No savings activity yet.
            </div>
        @endforelse
    </section>
</section>

<a
    href="{{ route('savings.transfer.create', ['date' => $defaultTransferDate]) }}"
    class="sprout-savings__fab"
    aria-label="Transfer savings to income"
    @if($categories->isEmpty()) aria-disabled="true" @endif
>
    <img src="/projectassets/icons/transfer.svg" alt="" class="sprout-savings__fab-icon">
</a>

<div class="sprout-savings__action-modal sprout-savings__action-modal--hidden" data-savings-action-modal>
    <button type="button" class="sprout-savings__action-backdrop" data-savings-action-close aria-label="Close activity actions"></button>

    <div class="sprout-savings__action-sheet">
        <div class="sprout-savings__action-title">Activity Options</div>
        <div class="sprout-savings__action-subtitle" data-savings-action-subtitle></div>

        <button type="button" class="sprout-savings__action-button" data-savings-action-view>
            View Activity
        </button>

        <button type="button" class="sprout-savings__action-button" data-savings-action-edit>
            Edit Activity
        </button>

        <form method="POST" data-savings-action-delete-form>
            @csrf
            @method('DELETE')

            <button type="button" class="sprout-savings__action-button sprout-savings__action-button--delete" data-savings-action-delete-button>
                Delete Activity
            </button>
        </form>

        <button type="button" class="sprout-savings__action-button sprout-savings__action-button--cancel" data-savings-action-close>
            Cancel
        </button>
    </div>
</div>

<div class="sprout-savings__delete-modal sprout-savings__delete-modal--hidden" data-savings-delete-modal>
    <button type="button" class="sprout-savings__delete-backdrop" data-savings-delete-cancel aria-label="Close delete confirmation"></button>

    <div class="sprout-savings__delete-sheet">
        <div class="sprout-savings__delete-title" data-savings-delete-title>Delete Activity</div>
        <p class="sprout-savings__delete-message" data-savings-delete-message>
            Are you sure you want to delete this activity?
        </p>

        <form method="POST" class="sprout-savings__delete-actions" data-savings-delete-form>
            @csrf
            @method('DELETE')

            <button type="button" class="sprout-savings__delete-button sprout-savings__delete-button--secondary" data-savings-delete-cancel>
                Cancel
            </button>

            <button type="submit" class="sprout-savings__delete-button sprout-savings__delete-button--primary" data-savings-delete-confirm>
                Delete
            </button>
        </form>
    </div>
</div>

<div class="sprout-savings__detail-modal sprout-savings__detail-modal--hidden" data-savings-detail-modal>
    <button type="button" class="sprout-savings__detail-backdrop" data-savings-detail-close aria-label="Close savings activity details"></button>

    <div class="sprout-savings__detail-sheet">
        <div class="sprout-savings__detail-title">Activity Details</div>

        <div class="sprout-savings__detail-card">
            <div class="sprout-savings__detail-row">
                <span class="sprout-savings__detail-label">Category</span>
                <span class="sprout-savings__detail-value" data-savings-detail-category></span>
            </div>
            <div class="sprout-savings__detail-row">
                <span class="sprout-savings__detail-label">Type</span>
                <span class="sprout-savings__detail-value" data-savings-detail-type></span>
            </div>
            <div class="sprout-savings__detail-row">
                <span class="sprout-savings__detail-label">Date</span>
                <span class="sprout-savings__detail-value" data-savings-detail-date></span>
            </div>
            <div class="sprout-savings__detail-row">
                <span class="sprout-savings__detail-label">Time</span>
                <span class="sprout-savings__detail-value" data-savings-detail-time></span>
            </div>
            <div class="sprout-savings__detail-row">
                <span class="sprout-savings__detail-label">Amount</span>
                <span class="sprout-savings__detail-value sprout-savings__detail-value--amount" data-savings-detail-amount></span>
            </div>
            <div class="sprout-savings__detail-row sprout-savings__detail-row--hidden" data-savings-detail-account-row>
                <span class="sprout-savings__detail-label">Account</span>
                <span class="sprout-savings__detail-value" data-savings-detail-account></span>
            </div>
            <div class="sprout-savings__detail-description sprout-savings__detail-description--hidden" data-savings-detail-description-row>
                <div class="sprout-savings__detail-description-label">Description</div>
                <div class="sprout-savings__detail-description-value" data-savings-detail-description></div>
            </div>
            <div class="sprout-savings__detail-photos sprout-savings__detail-photos--hidden" data-savings-detail-photos-row>
                <div class="sprout-savings__detail-description-label">Receipt Photos</div>
                <div class="sprout-savings__detail-photos-grid" data-savings-detail-photos></div>
            </div>
        </div>
    </div>
</div>
</div>
