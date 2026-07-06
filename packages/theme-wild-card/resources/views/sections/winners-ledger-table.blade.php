@php
    $heading = data_get($section, 'heading', __('capell-theme-wild-card::sections.winners.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-wild-card::sections.winners.summary'));
    $rows = collect(data_get($section, 'items', data_get($section, 'rows', [])))->take(100);
    $headers = [
        __('capell-theme-wild-card::sections.winners.column_project'),
        __('capell-theme-wild-card::sections.winners.column_studio'),
        __('capell-theme-wild-card::sections.winners.column_category'),
        __('capell-theme-wild-card::sections.winners.column_cycle'),
    ];
    $tableRows = $rows->map(static fn (array $row): array => [
        data_get($row, 'title', data_get($row, 'name', '')),
        data_get($row, 'studio', data_get($row, 'meta', '')),
        data_get($row, 'category', data_get($row, 'medium', '')),
        data_get($row, 'cycle', data_get($row, 'year', '')),
    ])->all();
    $label = data_get($section, 'label');
    $url = data_get($section, 'url');
@endphp

<section
    id="winners-ledger-table"
    class="exd-section exd-section-paper"
>
    <div class="exd-section-inner">
        <div class="exd-heading-row">
            <div>
                <p class="exd-kicker">
                    {{ __('capell-theme-wild-card::sections.winners.kicker') }}
                </p>
                <h2>{{ $heading }}</h2>
                <p class="exd-lede">{{ $summary }}</p>
            </div>
            @if (filled($label) && filled($url))
                <a
                    class="exd-button exd-button-secondary"
                    href="{{ $url }}"
                >
                    {{ $label }}
                </a>
            @endif
        </div>

        <x-capell-theme-foundation::display.responsive-table-to-cards
            class="exd-winners-ledger"
            :headers="$headers"
            :rows="$tableRows"
        />
    </div>
</section>
