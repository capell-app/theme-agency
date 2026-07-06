{{--
    Mirrors the base card-shuffle-grid view's inlined deterministic-hash
    order (see that file's comment for why the Action is not called
    directly from an inline PHP block).
--}}
@php
    $heading = data_get($section, 'heading', __('capell-theme-wild-card::sections.shuffle.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-wild-card::sections.shuffle.summary'));
    $pageSeed = (string) data_get($section, 'pageSeed', data_get($section, 'slug', 'wild-card-shuffle'));
    $items = collect(data_get($section, 'items', []))->take(20)->values();
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
    class="exd-section exd-section-raised"
>
    <div class="exd-section-inner">
        <p class="exd-kicker">
            {{ __('capell-theme-wild-card::sections.shuffle.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="exd-lede">{{ $summary }}</p>

        <ol
            class="exd-shuffle-deck-list"
            data-card-shuffle-grid
            data-shuffle-seed="{{ $pageSeed }}"
        >
            @foreach ($shuffled as $entry)
                @php
                    $item = $entry['item'];
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                    $itemTitle = data_get($item, 'title', data_get($item, 'name', ''));
                @endphp

                <li
                    class="exd-shuffle-deck-row"
                    style="--exd-shuffle-position: {{ $entry['position'] }}"
                >
                    <span
                        class="exd-shuffle-deck-index"
                        aria-hidden="true"
                    >
                        {{ str_pad((string) ($entry['position'] + 1), 2, '0', STR_PAD_LEFT) }}
                    </span>
                    <span class="exd-shuffle-deck-title">
                        @if (filled($itemUrl))
                            <a
                                class="exd-title-link"
                                href="{{ $itemUrl }}"
                            >
                                {{ $itemTitle }}
                            </a>
                        @else
                            {{ $itemTitle }}
                        @endif
                    </span>
                    <span class="exd-shuffle-deck-meta">
                        {{ data_get($item, 'meta', '') }}
                    </span>
                </li>
            @endforeach
        </ol>
    </div>
</section>
