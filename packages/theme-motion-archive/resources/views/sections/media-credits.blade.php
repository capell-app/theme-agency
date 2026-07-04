@php
    $heading = data_get($section, 'heading', __('capell-theme-motion-archive::sections.media_credits.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-motion-archive::sections.media_credits.summary'));
    $items = data_get($section, 'items', []);
@endphp

<section
    id="media-credits"
    class="mva-section"
>
    <div class="mva-section-inner">
        <p class="mva-kicker">
            {{ __('capell-theme-motion-archive::sections.media_credits.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="mva-lede">{{ $summary }}</p>

        <div
            class="mva-credit-grid"
            style="margin-top: 2rem"
        >
            @foreach ($items as $item)
                <article class="mva-credit-card">
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>
                        {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
