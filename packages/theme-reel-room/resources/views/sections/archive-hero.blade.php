@php
    $heading = data_get($section, 'heading', __('capell-theme-reel-room::sections.archive_hero.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-reel-room::sections.archive_hero.summary'));
    $mediaUrl = data_get($section, 'image', data_get($section, 'imageUrl'));
    $mediaAlt = data_get($section, 'imageAlt', __('capell-theme-reel-room::sections.archive_hero.still_alt'));
    $items = data_get($section, 'items', []);
@endphp

<section
    id="archive"
    class="mva-section mva-archive-hero"
>
    <div class="mva-section-inner">
        <h2>{{ $heading }}</h2>
        <p class="mva-lede">{{ $summary }}</p>

        <div class="mva-archive-still">
            @if (filled($mediaUrl))
                <img
                    src="{{ $mediaUrl }}"
                    alt="{{ $mediaAlt }}"
                    loading="eager"
                    fetchpriority="high"
                    decoding="async"
                    class="mva-archive-still-image"
                />
            @endif
        </div>

        @if (is_iterable($items) && collect($items)->isNotEmpty())
            <div class="mva-archive-rows">
                @foreach ($items as $item)
                    <article class="mva-archive-row">
                        <h3>
                            {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                        </h3>
                        <p>
                            {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                        </p>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
