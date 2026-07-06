@php
    $heading = (string) ($widget->getMeta('heading') ?? __('capell-theme-main-stage::generic.speakers.kicker'));
    $summary = (string) ($widget->getMeta('summary') ?? '');
    $variant = (string) ($widget->getMeta('variant') ?? 'wall');
    $speakers = is_array($widget->getMeta('speakers')) ? $widget->getMeta('speakers') : [];
@endphp

{{--
    `speaker-wall-hover-bios` — Part 2 §E signature widget.

    Mechanic: a grid of speaker cards whose bio copy is revealed on
    hover/focus (`:focus-within`/`:hover` driven CSS, no JS) rather than a
    separate detail page — keyboard-reachable via native tab order (§0.5).

    Two variants: `wall` (dense grid, up to 4 columns, the FOMO "look how
    many big names" register) and `carousel` (horizontal scroll-snap rail
    for a smaller, curated speaker set) — both share the same card markup and
    hover-reveal mechanic; only the container layout differs.
--}}
<section
    id="speakers"
    class="mst-shell mst-section"
>
    <div class="mst-section-inner">
        <div class="mst-heading-row">
            <div>
                <p class="mst-eyebrow">{{ __('capell-theme-main-stage::generic.speakers.kicker') }}</p>
                <h2>{{ $heading }}</h2>
                @if ($summary !== '')
                    <p class="mst-lede">{{ $summary }}</p>
                @endif
            </div>
        </div>

        <div
            class="mst-speaker-wall mst-speaker-wall--{{ $variant === 'carousel' ? 'carousel' : 'wall' }}"
        >
            @foreach ($speakers as $speaker)
                <article
                    class="mst-speaker-card"
                    tabindex="0"
                >
                    @if (data_get($speaker, 'photo'))
                        <img
                            src="{{ data_get($speaker, 'photo') }}"
                            alt="{{ data_get($speaker, 'name', '') }}"
                            class="mst-speaker-photo"
                            loading="lazy"
                        />
                    @else
                        <span
                            class="mst-speaker-photo mst-speaker-photo-empty"
                            aria-hidden="true"
                        ></span>
                    @endif

                    <div class="mst-speaker-card-base">
                        <h3 class="mst-speaker-name">
                            {{ data_get($speaker, 'name', '') }}
                        </h3>
                        <p class="mst-speaker-role">{{ data_get($speaker, 'role', '') }}</p>
                    </div>

                    <div class="mst-speaker-bio">
                        <p>{{ data_get($speaker, 'bio', '') }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
