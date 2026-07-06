@php
    $heading = data_get($section, 'heading', __('capell-theme-wild-card::sections.depth.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-wild-card::sections.depth.summary'));
    $items = collect(data_get($section, 'items', []))->take(50);
@endphp

<section
    id="infinite-scroll-depth-pressure"
    class="exd-section exd-section-raised"
>
    <div class="exd-section-inner">
        <p class="exd-kicker">
            {{ __('capell-theme-wild-card::sections.depth.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="exd-lede">{{ $summary }}</p>

        <div
            class="exd-depth-rows"
            data-infinite-scroll-depth-pressure
        >
            @foreach ($items as $item)
                @php
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                    $itemTitle = data_get($item, 'title', data_get($item, 'name', ''));
                @endphp

                <div class="exd-depth-row">
                    <span
                        class="exd-meta"
                        >{{ data_get($item, 'meta', '') }}</span
                    >
                    <h3>
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
                    </h3>
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
