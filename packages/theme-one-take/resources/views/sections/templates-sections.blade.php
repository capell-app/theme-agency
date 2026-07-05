@php
    $heading = data_get($section, 'heading', __('capell-theme-one-take::sections.updates.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-one-take::sections.updates.summary'));
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-one-take::sections.updates.release_title'), 'summary' => __('capell-theme-one-take::sections.updates.release_summary')],
        ['title' => __('capell-theme-one-take::sections.updates.beta_title'), 'summary' => __('capell-theme-one-take::sections.updates.beta_summary')],
    ]);
    $itemImage = data_get(collect($items)->first(), 'image', data_get(collect($items)->first(), 'imageUrl'));
@endphp

<section
    id="templates-sections"
    class="ops-section ops-section-dark"
>
    <div class="ops-section-inner ops-split">
        <div>
            <p class="ops-kicker">
                {{ __('capell-theme-one-take::sections.updates.kicker') }}
            </p>
            <h2>{{ $heading }}</h2>
            <p class="ops-lede">{{ $summary }}</p>
            <div class="ops-actions">
                <a
                    class="ops-button"
                    href="{{ data_get($section, 'url', '/') }}"
                >
                    {{ data_get($section, 'label', __('capell-theme-one-take::sections.updates.button')) }}
                </a>
            </div>
        </div>
        <div class="ops-grid">
            @foreach ($items as $item)
                @php
                    $cardImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                    $cardAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                @endphp

                <article class="ops-card">
                    @if (filled($cardImage))
                        <img
                            src="{{ $cardImage }}"
                            alt="{{ $cardAlt }}"
                            width="960"
                            height="600"
                            loading="lazy"
                            decoding="async"
                            class="ops-capture-media ops-capture-media-wide"
                            style="border-radius: 0.7rem"
                        />
                    @endif

                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
