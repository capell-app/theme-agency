@php
    $items = data_get($section, 'items', []);
@endphp

<section
    id="zine-annotations"
    class="rwi-section rwi-section-field"
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
            class="rwi-annotation-grid"
            style="margin-top: 2rem"
        >
            @foreach ($items as $item)
                <article class="rwi-annotation">
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
