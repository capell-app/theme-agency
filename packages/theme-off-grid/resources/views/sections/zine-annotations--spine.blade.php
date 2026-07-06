{{--
    zine-annotations, `spine` variant: annotations rendered as a single
    vertical marginalia spine (a bordered rail down the inline-start edge of
    each note) rather than the default 3-up grid — reads closer to
    handwritten margin notes running down a printed page.
--}}

@php
    $items = data_get($section, 'items', []);
@endphp

<section
    id="zine-annotations"
    class="rwi-section rwi-section-field"
    data-widget="zine-annotations"
    data-variant="spine"
>
    <div class="rwi-section-inner">
        <span class="rwi-index-tag">06</span>
        <p class="rwi-kicker">
            {{ __('capell-theme-off-grid::sections.annotations.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-off-grid::sections.annotations.heading')) }}
        </h2>
        <p class="rwi-lede">
            {{ data_get($section, 'summary', __('capell-theme-off-grid::sections.annotations.summary')) }}
        </p>

        <div
            class="rwi-annotation-spine"
            style="margin-top: 2rem"
        >
            @foreach ($items as $item)
                <article class="rwi-annotation rwi-annotation-spine-item">
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
