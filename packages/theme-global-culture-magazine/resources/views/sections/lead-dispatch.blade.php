@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-global-culture-magazine::sections.lead.house_title'), 'summary' => __('capell-theme-global-culture-magazine::sections.lead.house_summary')],
        ['title' => __('capell-theme-global-culture-magazine::sections.lead.studio_title'), 'summary' => __('capell-theme-global-culture-magazine::sections.lead.studio_summary')],
        ['title' => __('capell-theme-global-culture-magazine::sections.lead.city_title'), 'summary' => __('capell-theme-global-culture-magazine::sections.lead.city_summary')],
    ]);
    $itemCollection = collect($items);
    $firstItem = $itemCollection->first();
    $firstTitle = (string) data_get($firstItem, 'title', data_get($firstItem, 'name', ''));
    $firstImage = (string) data_get($firstItem, 'imageUrl', data_get($firstItem, 'image', ''));
@endphp

<section
    class="gcm-section"
    id="lead-dispatch"
>
    <div class="gcm-section-inner gcm-split">
        <div>
            <p class="gcm-kicker">
                {{ __('capell-theme-global-culture-magazine::sections.lead.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-global-culture-magazine::sections.lead.heading')) }}
            </h2>
            <p class="gcm-lede">
                {{ data_get($section, 'summary', __('capell-theme-global-culture-magazine::sections.lead.summary')) }}
            </p>

            <figure class="gcm-plate gcm-plate-offset">
                <div class="gcm-plate-frame">
                    @if ($firstImage !== '')
                        <img
                            src="{{ $firstImage }}"
                            alt="{{ $firstTitle }}"
                            loading="lazy"
                        />
                    @else
                        <span
                            class="gcm-plate-initial"
                            aria-hidden="true"
                        >
                            {{ mb_substr(trim($firstTitle) !== '' ? trim($firstTitle) : 'A', 0, 1) }}
                        </span>
                    @endif
                </div>
                <figcaption class="gcm-plate-caption">
                    <span class="gcm-meta">
                        {{ __('capell-theme-global-culture-magazine::sections.lead.plate_meta') }}
                    </span>
                    <span>{{ $firstTitle }}</span>
                </figcaption>
            </figure>
        </div>

        <ol class="gcm-index">
            @foreach ($items as $item)
                <li class="gcm-index-row">
                    <span
                        class="gcm-index-number"
                        aria-hidden="true"
                    >
                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                    </span>
                    <p class="gcm-meta">
                        {{ data_get($item, 'meta', data_get($item, 'category', __('capell-theme-global-culture-magazine::sections.lead.default_meta'))) }}
                    </p>
                    <div>
                        @php
                            $itemTitle = (string) data_get($item, 'title', data_get($item, 'name', ''));
                            $itemUrl = (string) data_get($item, 'url', data_get($item, 'href', ''));
                        @endphp

                        <h3>
                            @if ($itemUrl !== '')
                                <a
                                    class="gcm-title-link"
                                    href="{{ $itemUrl }}"
                                >
                                    {{ $itemTitle }}
                                </a>
                            @else
                                {{ $itemTitle }}
                            @endif
                        </h3>
                        <p>{{ data_get($item, 'summary', '') }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</section>
