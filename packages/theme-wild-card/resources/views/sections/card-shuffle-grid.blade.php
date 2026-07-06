{{--
    Section 0.1 determinism guardrail: html-cache serves identical HTML to
    every visitor, so card order is derived deterministically from a
    per-item seed (id/title) combined with the page-level seed via crc32 —
    never Math.random() or an unseeded shuffle. The inline PHP-block
    static-call policy bans Action::run() inside a Blade view, so the same
    deterministic-hash algorithm implemented by
    Capell\ThemeStudio\WildCard\Actions\ComputeDeterministicShuffleOrderAction
    (tested directly in ComputeDeterministicShuffleOrderActionTest) is
    inlined here using only whitelisted helpers; the --compact variant
    repeats it identically.
--}}
@php
    $heading = data_get($section, 'heading', __('capell-theme-wild-card::sections.shuffle.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-wild-card::sections.shuffle.summary'));
    $pageSeed = (string) data_get($section, 'pageSeed', data_get($section, 'slug', 'wild-card-shuffle'));
    $items = collect(data_get($section, 'items', [
        ['title' => __('capell-theme-wild-card::sections.shuffle.entry_title'), 'summary' => __('capell-theme-wild-card::sections.shuffle.entry_summary'), 'meta' => __('capell-theme-wild-card::sections.shuffle.entry_meta')],
    ]))->take(20)->values();
    $shuffled = $items
        ->map(fn (array $item, int $index): array => [
            'item' => $item,
            'hash' => crc32($pageSeed . '::' . (string) (data_get($item, 'id') ?? data_get($item, 'title') ?? data_get($item, 'name') ?? $index) . '::' . $index),
            'originalIndex' => $index,
        ])
        ->sort(fn (array $left, array $right): int => $left['hash'] <=> $right['hash'] ?: $left['originalIndex'] <=> $right['originalIndex'])
        ->values()
        ->map(fn (array $entry, int $position): array => ['item' => $entry['item'], 'position' => $position]);
@endphp

<section
    id="card-shuffle-grid"
    class="exd-section"
>
    <div class="exd-section-inner">
        <p class="exd-kicker">
            {{ __('capell-theme-wild-card::sections.shuffle.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="exd-lede">{{ $summary }}</p>

        <div
            class="exd-shuffle-grid"
            data-card-shuffle-grid
            data-shuffle-seed="{{ $pageSeed }}"
        >
            @foreach ($shuffled as $entry)
                @php
                    $item = $entry['item'];
                    $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                    $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                    $itemTitle = data_get($item, 'title', data_get($item, 'name', ''));
                @endphp

                @if (filled($itemUrl))
                    <a
                        class="exd-card exd-shuffle-card"
                        style="--exd-shuffle-position: {{ $entry['position'] }}"
                        href="{{ $itemUrl }}"
                    >
                        <span
                            class="exd-shuffle-deck-index"
                            aria-hidden="true"
                        >
                            {{ str_pad((string) ($entry['position'] + 1), 2, '0', STR_PAD_LEFT) }}
                        </span>

                        @if (filled($itemImage))
                            <img
                                src="{{ $itemImage }}"
                                alt="{{ $itemAlt }}"
                                width="800"
                                height="600"
                                loading="lazy"
                                decoding="async"
                                class="exd-media"
                            />
                        @else
                            <div
                                class="exd-media exd-media-empty"
                                aria-hidden="true"
                            ></div>
                        @endif

                        <div class="exd-card-body">
                            <p class="exd-meta">
                                {{ data_get($item, 'meta', '') }}
                            </p>
                            <h3>{{ $itemTitle }}</h3>
                            <p>{{ data_get($item, 'summary', '') }}</p>
                        </div>
                    </a>
                @else
                    <article
                        class="exd-card exd-shuffle-card"
                        style="--exd-shuffle-position: {{ $entry['position'] }}"
                    >
                        <span
                            class="exd-shuffle-deck-index"
                            aria-hidden="true"
                        >
                            {{ str_pad((string) ($entry['position'] + 1), 2, '0', STR_PAD_LEFT) }}
                        </span>

                        @if (filled($itemImage))
                            <img
                                src="{{ $itemImage }}"
                                alt="{{ $itemAlt }}"
                                width="800"
                                height="600"
                                loading="lazy"
                                decoding="async"
                                class="exd-media"
                            />
                        @else
                            <div
                                class="exd-media exd-media-empty"
                                aria-hidden="true"
                            ></div>
                        @endif

                        <div class="exd-card-body">
                            <p class="exd-meta">
                                {{ data_get($item, 'meta', '') }}
                            </p>
                            <h3>{{ $itemTitle }}</h3>
                            <p>{{ data_get($item, 'summary', '') }}</p>
                        </div>
                    </article>
                @endif
            @endforeach
        </div>
    </div>
</section>
