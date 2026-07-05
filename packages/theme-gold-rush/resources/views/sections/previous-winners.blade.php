@php
    $heading = data_get($section, 'heading', __('capell-theme-gold-rush::sections.winners.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-gold-rush::sections.winners.summary'));
    $items = data_get($section, 'items', []);
    $label = data_get($section, 'label', __('capell-theme-gold-rush::sections.winners.browse'));
    $url = data_get($section, 'url', data_get($section, 'href'));
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

        <div class="sbs-hall">
            @foreach ($items as $item)
                @php
                    $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                    $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                    $itemTitle = (string) data_get($item, 'title', data_get($item, 'name', ''));
                    [$winnerName, $winnerScore] = str_contains($itemTitle, '—')
                        ? array_map('trim', explode('—', $itemTitle, 2))
                        : [$itemTitle, null];
                @endphp

                <div class="sbs-hall-row">
                    <span
                        class="sbs-hall-rank sbs-split-flap"
                        style="--sbs-rank: {{ $loop->iteration }}"
                    >
                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                    </span>

                    <div>
                        <h3 class="sbs-hall-title">
                            @if (filled($itemImage))
                                <img
                                    src="{{ $itemImage }}"
                                    alt="{{ $itemAlt }}"
                                    loading="lazy"
                                    decoding="async"
                                    class="sbs-hall-thumb"
                                />
                            @endif

                            @if (filled($itemUrl))
                                <a
                                    class="sbs-title-link"
                                    href="{{ $itemUrl }}"
                                >
                                    {{ $winnerName }}
                                </a>
                            @else
                                {{ $winnerName }}
                            @endif
                        </h3>
                        <p class="sbs-hall-summary">
                            {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                        </p>
                    </div>

                    <span class="sbs-hall-score">{{ $winnerScore }}</span>
                </div>
            @endforeach
        </div>
    </div>
</section>
