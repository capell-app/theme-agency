@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-far-field::sections.cities.tokyo_title'), 'summary' => __('capell-theme-far-field::sections.cities.tokyo_summary')],
        ['title' => __('capell-theme-far-field::sections.cities.copenhagen_title'), 'summary' => __('capell-theme-far-field::sections.cities.copenhagen_summary')],
        ['title' => __('capell-theme-far-field::sections.cities.lisbon_title'), 'summary' => __('capell-theme-far-field::sections.cities.lisbon_summary')],
        ['title' => __('capell-theme-far-field::sections.cities.mexico_title'), 'summary' => __('capell-theme-far-field::sections.cities.mexico_summary')],
    ]);
@endphp

<section
    class="gcm-section"
    id="city-guides"
>
    <div class="gcm-section-inner">
        <div class="gcm-intro">
            <p class="gcm-kicker">
                {{ __('capell-theme-far-field::sections.cities.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-far-field::sections.cities.heading')) }}
            </h2>
            @if (data_get($section, 'summary', '') !== '')
                <p class="gcm-lede">{{ data_get($section, 'summary') }}</p>
            @endif
        </div>
        <div class="gcm-grid gcm-grid-4">
            @foreach ($items as $item)
                @php
                    $guideTitle = (string) data_get($item, 'title', data_get($item, 'name', ''));
                    $guideUrl = (string) data_get($item, 'url', data_get($item, 'href', ''));
                @endphp

                <article class="gcm-card">
                    <span
                        class="gcm-byline-mark"
                        aria-hidden="true"
                    >
                        {{ mb_substr(trim($guideTitle) !== '' ? trim($guideTitle) : 'A', 0, 1) }}
                    </span>
                    <h3>
                        @if ($guideUrl !== '')
                            <a
                                class="gcm-title-link"
                                href="{{ $guideUrl }}"
                            >
                                {{ $guideTitle }}
                            </a>
                        @else
                            {{ $guideTitle }}
                        @endif
                    </h3>
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
