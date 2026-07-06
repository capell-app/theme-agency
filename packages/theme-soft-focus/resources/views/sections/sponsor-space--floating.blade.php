{{--
    sponsor-space-floating (Wave 4c signature widget #4, headline mechanic
    "scatter light table" applied to the single sponsor placement): the one
    sponsor card floats with a small seeded tilt and lift off the light
    table instead of sitting flush in the default variant's split layout.
    Guardrail §0.1: crc32-seeded rotation/offset only, no Math.random() —
    a single card still reads as "placed", not randomly jittered, and the
    same payload always floats it the same way. Guardrail §0.5: the card
    remains a normal focusable block in DOM order; reduced motion drops the
    hover lift but keeps the static float position.
--}}
@php
    $pageSeed = (string) data_get($section, 'pageSeed', data_get($section, 'slug', 'soft-focus-sponsor-space'));
    $item = data_get($section, 'items.0', [
        'title' => __('capell-theme-soft-focus::sections.sponsor.card_title'),
        'summary' => __('capell-theme-soft-focus::sections.sponsor.card_summary'),
    ]);
    $identity = (string) (data_get($item, 'title') ?? data_get($item, 'name') ?? 'sponsor');
    $seed = crc32($pageSeed . '::sponsor-space-floating::' . $identity);
    $rotation = (($seed % 400) / 100) - 2;
    $offsetBlock = (($seed >> 4) % 40) - 20;
@endphp

<section
    id="sponsor-space"
    class="qwg-section qwg-scatter-section"
    data-widget="sponsor-space-floating"
    data-variant="floating"
>
    <div class="qwg-section-inner qwg-split">
        <div>
            <p class="qwg-kicker">
                {{ __('capell-theme-soft-focus::sections.sponsor.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-soft-focus::sections.sponsor.heading')) }}
            </h2>
            <p class="qwg-lede">
                {{ data_get($section, 'summary', __('capell-theme-soft-focus::sections.sponsor.summary')) }}
            </p>
        </div>

        <article
            class="qwg-card qwg-scatter-tile qwg-scatter-tile-floating"
            tabindex="0"
            style="--qwg-scatter-rotate: {{ $rotation }}deg; --qwg-scatter-y: {{ $offsetBlock }}px; --qwg-scatter-z: 15;"
            data-scatter-tile
        >
            <p class="qwg-meta">
                {{ __('capell-theme-soft-focus::sections.sponsor.label') }}
            </p>
            <h3>{{ data_get($item, 'title', data_get($item, 'name', '')) }}</h3>
            <p>{{ data_get($item, 'summary', '') }}</p>
        </article>
    </div>
</section>
