@php
    $savingsScope = $savingsScope ?? 'mobile';
    $totalWorth = $savingsPayload['totalWorth'] ?? 0;
    $categories = collect($savingsPayload['categories'] ?? []);
    $historyItems = collect($savingsPayload['history'] ?? []);
    $accounts = $savingsPayload['accounts'] ?? [];
    $defaultTransferDate = $savingsPayload['defaultTransferDate'] ?? now()->format('Y-m-d');
    $pieGradient = $savingsPayload['pieGradient'] ?? 'conic-gradient(#e9edf2 0deg 360deg)';
    $showTransferModal = $errors->has('source_category_id')
        || $errors->has('amount')
        || $errors->has('transfer_amount')
        || $errors->has('transfer_date')
        || $errors->has('account');
@endphp

<div class="sprout-savings__panel-shell" data-savings-panel>
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

    <div class="sprout-savings__hero">
        <div class="sprout-savings__hero-head">
            <div>
                <p class="sprout-savings__eyebrow">Savings Worth</p>
                <h1 class="sprout-savings__title">&#8369;{{ number_format($totalWorth, 0) }}</h1>
            </div>

            <button
                type="button"
                class="sprout-savings__transfer-trigger"
                data-savings-transfer-open
                @disabled($categories->isEmpty())
            >
                Transfer to Income
            </button>
        </div>

        <div class="sprout-savings__hero-body">
            <div class="sprout-savings__donut-wrap">
                <div
                    class="sprout-savings__donut"
                    style="background: {{ $pieGradient }};"
                    data-savings-donut
                    data-savings-categories='@json($categories->values()->all())'
                >
                    <div class="sprout-savings__donut-hole"></div>
                </div>

                <div class="sprout-savings__pie-popup sprout-savings__pie-popup--hidden" data-savings-pie-popup>
                    <div class="sprout-savings__pie-popup-name" data-savings-pie-popup-name>Category</div>
                    <div class="sprout-savings__pie-popup-amount" data-savings-pie-popup-amount>&#8369;0</div>
                </div>
            </div>

            <div class="sprout-savings__legend-list">
                @forelse ($categories as $category)
                    <div class="sprout-savings__legend-item">
                        <span class="sprout-savings__legend-dot" style="background: {{ $category['color'] }};"></span>
                        <span class="sprout-savings__legend-name">{{ $category['name'] }}</span>
                    </div>
                @empty
                    <div class="sprout-savings__empty">
                        <img src="/projectassets/icons/notes.svg" alt="" class="sprout-savings__empty-icon">
                        <p class="sprout-savings__empty-text">No savings data available.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <section class="sprout-savings__history">
        <div class="sprout-savings__section-title">Savings Activity</div>

        @forelse ($historyItems as $item)
            <article class="sprout-savings__history-card">
                <div class="sprout-savings__history-head">
                    <div class="sprout-savings__history-date">{{ $item['dateLabel'] }}</div>
                    <div class="sprout-savings__history-state sprout-savings__history-state--{{ $item['direction'] }}">
                        {{ $item['direction'] === 'out' ? 'OUT' : 'IN' }} &#8369;{{ number_format($item['amount'], 0) }}
                    </div>
                </div>

                <div class="sprout-savings__history-row">
                    <div class="sprout-savings__history-left">
                        <div class="sprout-savings__history-icon sprout-savings__history-icon--{{ $item['iconTone'] }}">
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
            </article>
        @empty
            <div class="sprout-savings__history-empty">
                No savings activity yet.
            </div>
        @endforelse
    </section>
</section>

<div class="sprout-savings__modal {{ $showTransferModal ? '' : 'sprout-savings__modal--hidden' }}" data-savings-transfer-modal>
    <button type="button" class="sprout-savings__modal-backdrop" data-savings-transfer-close aria-label="Close transfer form"></button>

    <div class="sprout-savings__modal-sheet">
        <div class="sprout-savings__modal-head">
            <div class="sprout-savings__modal-title">Transfer Savings</div>
            <button type="button" class="sprout-savings__modal-close" data-savings-transfer-close aria-label="Close">
                &times;
            </button>
        </div>

        <form action="{{ route('savings.transfer') }}" method="POST" class="sprout-savings__form">
            @csrf

            <label class="sprout-savings__field">
                <span class="sprout-savings__field-label">Savings Category</span>
                <select name="source_category_id" class="sprout-savings__input" required>
                    <option value="">Select category</option>
                    @foreach ($categories as $category)
                        <option
                            value="{{ $category['categoryId'] }}"
                            @selected((string) old('source_category_id') === (string) $category['categoryId'])
                        >
                            {{ $category['name'] }} - &#8369;{{ number_format($category['amount'], 0) }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="sprout-savings__field">
                <span class="sprout-savings__field-label">Amount</span>
                <input
                    type="number"
                    step="0.01"
                    min="0.01"
                    name="amount"
                    class="sprout-savings__input"
                    value="{{ old('amount') }}"
                    required
                >
            </label>

            <label class="sprout-savings__field">
                <span class="sprout-savings__field-label">Transfer Date</span>
                <input
                    type="date"
                    name="transfer_date"
                    class="sprout-savings__input"
                    value="{{ old('transfer_date', $defaultTransferDate) }}"
                    required
                >
            </label>

            <label class="sprout-savings__field">
                <span class="sprout-savings__field-label">Income Account</span>
                <input
                    type="text"
                    name="account"
                    class="sprout-savings__input"
                    list="sproutSavingsAccounts-{{ $savingsScope }}"
                    value="{{ old('account') }}"
                    required
                >
            </label>

            <datalist id="sproutSavingsAccounts-{{ $savingsScope }}">
                @foreach ($accounts as $account)
                    <option value="{{ $account }}"></option>
                @endforeach
            </datalist>

            <label class="sprout-savings__field">
                <span class="sprout-savings__field-label">Description</span>
                <textarea name="description" class="sprout-savings__input sprout-savings__input--textarea">{{ old('description') }}</textarea>
            </label>

            <div class="sprout-savings__form-actions">
                <button type="button" class="sprout-savings__button sprout-savings__button--secondary" data-savings-transfer-close>
                    Cancel
                </button>
                <button type="submit" class="sprout-savings__button sprout-savings__button--primary">
                    Transfer
                </button>
            </div>
        </form>
    </div>
</div>
</div>
