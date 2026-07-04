@php
    $heading = data_get($section, 'heading', __('capell-theme-motion-archive::sections.winner_list.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-motion-archive::sections.winner_list.summary'));
    $items = data_get($section, 'items', []);
@endphp

<section
    id="winner-list"
    class="mva-section"
>
    <div class="mva-section-inner">
        <p class="mva-kicker">
            {{ __('capell-theme-motion-archive::sections.winner_list.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="mva-lede">{{ $summary }}</p>

        @if (is_iterable($items) && collect($items)->isNotEmpty())
            <div
                class="mva-winners"
                style="margin-top: 2rem"
            >
                @foreach ($items as $item)
                    @php
                        $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                        $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                        $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                        $tier = data_get($item, 'tier', str_contains((string) data_get($item, 'meta', ''), 'Silver') ? 'silver' : 'gold');
                        $score = data_get($item, 'score');
                    @endphp

                    <article class="mva-winner">
                        <div class="mva-winner-still">
                            @if (filled($itemImage))
                                <img
                                    src="{{ $itemImage }}"
                                    alt="{{ $itemAlt }}"
                                    loading="lazy"
                                    decoding="async"
                                    class="mva-winner-still-image"
                                />
                            @endif
                        </div>
                        <div class="mva-winner-body">
                            <div class="mva-score-row">
                                <span
                                    class="mva-badge {{ $tier === 'silver' ? 'mva-badge-silver' : 'mva-badge-gold' }}"
                                >
                                    {{ data_get($item, 'meta', '') }}
                                </span>
                            </div>
                            <h3>
                                @if (filled($itemUrl))
                                    <a
                                        class="mva-winner-title-link"
                                        href="{{ $itemUrl }}"
                                    >
                                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                                    </a>
                                @else
                                    {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                                @endif
                            </h3>
                            <p>
                                {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                            </p>
                            <p class="mva-winner-credit">
                                {{ data_get($item, 'care_note', data_get($item, 'credit', '')) }}
                            </p>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div
                class="mva-archive-empty"
                style="margin-top: 2rem"
            >
                <span
                    class="mva-archive-empty-reel"
                    aria-hidden="true"
                ></span>
                <p class="mva-archive-empty-title">
                    {{ __('capell-theme-motion-archive::sections.winner_list.empty_title') }}
                </p>
                <p class="mva-archive-empty-body">
                    {{ __('capell-theme-motion-archive::sections.winner_list.empty') }}
                </p>
            </div>
        @endif
    </div>
</section>
