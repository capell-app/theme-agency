@php
    $heading = data_get($section, 'heading', __('capell-theme-gold-rush::sections.winners.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-gold-rush::sections.winners.summary'));
    // Payload cap (Wave 2 §0.3): tables ship at most 100 rows.
    $items = collect(data_get($section, 'items', []))->take(100)->values();
    $label = data_get($section, 'label', __('capell-theme-gold-rush::sections.winners.browse'));
    $url = data_get($section, 'url', data_get($section, 'href'));

    $rows = $items->map(function (mixed $item): array {
        $itemTitle = (string) data_get($item, 'title', data_get($item, 'name', ''));
        [$winnerName, $winnerScore] = str_contains($itemTitle, '—')
            ? array_map('trim', explode('—', $itemTitle, 2))
            : [$itemTitle, ''];

        return [
            $winnerName,
            data_get($item, 'summary', data_get($item, 'description', '')),
            $winnerScore,
        ];
    })->all();
@endphp

<section
    id="previous-winners"
    class="sbs-section sbs-section-field"
>
    <div class="sbs-section-inner">
        <div class="sbs-heading-row">
            <div>
                <p class="sbs-kicker">
                    {{ __('capell-theme-gold-rush::sections.winners.kicker') }}
                </p>
                <h2>{{ $heading }}</h2>
                <p class="sbs-lede">{{ $summary }}</p>
            </div>

            @if (filled($url))
                <a
                    class="sbs-link-arrow"
                    href="{{ $url }}"
                >
                    {{ $label }}
                </a>
            @endif
        </div>

        <x-capell-theme-foundation::display.responsive-table-to-cards
            :headers="[
                __('capell-theme-gold-rush::sections.winners.column_winner'),
                __('capell-theme-gold-rush::sections.winners.column_notes'),
                __('capell-theme-gold-rush::sections.winners.column_score'),
            ]"
            :rows="$rows"
            class="sbs-winners-ledger"
        />
    </div>
</section>
