@php
    $item = data_get($section, 'items.0', [
        'title' => __('capell-theme-quiet-web-gallery::sections.sponsor.card_title'),
        'summary' => __('capell-theme-quiet-web-gallery::sections.sponsor.card_summary'),
    ]);
@endphp

<section
    id="sponsor-space"
    class="qwg-section"
>
    <div class="qwg-section-inner qwg-split">
        <div>
            <p class="qwg-kicker">
                {{ __('capell-theme-quiet-web-gallery::sections.sponsor.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-quiet-web-gallery::sections.sponsor.heading')) }}
            </h2>
            <p class="qwg-lede">
                {{ data_get($section, 'summary', __('capell-theme-quiet-web-gallery::sections.sponsor.summary')) }}
            </p>
        </div>

        <article class="qwg-card">
            <p class="qwg-meta">
                {{ __('capell-theme-quiet-web-gallery::sections.sponsor.label') }}
            </p>
            <h3>
                {{ data_get($item, 'title', data_get($item, 'name', '')) }}
            </h3>
            <p>
                {{ data_get($item, 'summary', '') }}
            </p>
        </article>
    </div>
</section>
