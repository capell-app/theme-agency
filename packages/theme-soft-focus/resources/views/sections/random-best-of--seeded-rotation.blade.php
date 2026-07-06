{{--
    random-best-of-cta (Wave 4c signature widget #5, headline mechanic
    "scatter light table"; misleadingly named in the programme spec —
    "shuffle" means a seeded rotation, never actual randomness). Guardrail
    §0.1: the featured pick is never chosen with Math.random() — when the
    payload supplies more than one candidate in `items`, the featured index
    is `data_get($section, 'featuredIndex')` if editorially pinned, else the
    day-of-year modulo the candidate count (a deterministic function of the
    render date, not the request), so html-cache still serves one identical
    response per day to every visitor and the same payload always resolves
    the same pick within that day. The default `random-best-of` variant
    already behaves this way with a single item; this variant makes the
    rotation explicit and visible via a candidate rail under the spotlight.
--}}
@php
    $items = collect(data_get($section, 'items', [
        ['title' => __('capell-theme-soft-focus::sections.spotlight.pick_title'), 'summary' => __('capell-theme-soft-focus::sections.spotlight.pick_summary')],
    ]))
        ->filter(fn (mixed $entry): bool => filled(data_get($entry, 'title', data_get($entry, 'name'))))
        ->values();

    $candidateCount = max($items->count(), 1);
    $featuredIndex = data_get($section, 'featuredIndex');
    $featuredIndex = is_int($featuredIndex) && $featuredIndex >= 0 && $featuredIndex < $candidateCount
        ? $featuredIndex
        : ((int) data_get($section, 'dayOfYear', (int) date('z')) % $candidateCount);

    $item = $items->get($featuredIndex, [
        'title' => __('capell-theme-soft-focus::sections.spotlight.pick_title'),
        'summary' => __('capell-theme-soft-focus::sections.spotlight.pick_summary'),
    ]);
    $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
    $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
@endphp

<section
    id="random-best-of"
    class="qwg-section qwg-section-dark"
    data-widget="random-best-of-cta"
    data-variant="seeded-rotation"
    data-rotation-index="{{ $featuredIndex }}"
    data-rotation-count="{{ $candidateCount }}"
>
    <div class="qwg-section-inner qwg-split">
        <figure class="qwg-frame">
            <div class="qwg-frame-mat">
                @if (filled($itemImage))
                    <img
                        src="{{ $itemImage }}"
                        alt="{{ $itemAlt }}"
                        loading="lazy"
                        decoding="async"
                        class="qwg-frame-media qwg-frame-media-wide"
                    />
                @else
                    <div
                        class="qwg-frame-media qwg-frame-media-wide"
                        aria-hidden="true"
                    ></div>
                @endif
            </div>
            <figcaption class="qwg-frame-caption">
                <span class="qwg-frame-number">
                    {{ __('capell-theme-soft-focus::sections.spotlight.label') }}
                </span>
                <span>
                    {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                </span>
            </figcaption>
        </figure>

        <div>
            <p class="qwg-kicker">
                {{ __('capell-theme-soft-focus::sections.spotlight.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-soft-focus::sections.spotlight.heading')) }}
            </h2>
            <p class="qwg-lede">
                {{ data_get($section, 'summary', __('capell-theme-soft-focus::sections.spotlight.summary')) }}
            </p>
            <p class="qwg-lede">{{ data_get($item, 'summary', '') }}</p>

            @if ($items->count() > 1)
                <ul
                    class="qwg-rotation-rail"
                    aria-label="{{ __('capell-theme-soft-focus::sections.spotlight.rail_label') }}"
                >
                    @foreach ($items as $index => $candidate)
                        <li>
                            <span
                                class="qwg-rotation-dot {{ $index === $featuredIndex ? 'is-active' : '' }}"
                                aria-hidden="true"
                            ></span>
                            <span class="qwg-rotation-label">
                                {{ data_get($candidate, 'title', data_get($candidate, 'name', '')) }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</section>
