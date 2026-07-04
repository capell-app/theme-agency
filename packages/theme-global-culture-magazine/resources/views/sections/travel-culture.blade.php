@php
    $heading = data_get($section, 'heading', __('capell-theme-global-culture-magazine::sections.sections.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-global-culture-magazine::sections.sections.summary'));
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-global-culture-magazine::sections.sections.home_title'), 'summary' => __('capell-theme-global-culture-magazine::sections.sections.home_summary'), 'meta' => __('capell-theme-global-culture-magazine::sections.sections.home_meta')],
        ['title' => __('capell-theme-global-culture-magazine::sections.sections.quarter_title'), 'summary' => __('capell-theme-global-culture-magazine::sections.sections.quarter_summary'), 'meta' => __('capell-theme-global-culture-magazine::sections.sections.quarter_meta')],
        ['title' => __('capell-theme-global-culture-magazine::sections.sections.gallery_title'), 'summary' => __('capell-theme-global-culture-magazine::sections.sections.gallery_summary'), 'meta' => __('capell-theme-global-culture-magazine::sections.sections.gallery_meta')],
    ]);
@endphp

<section
    class="gcm-section"
    id="travel-culture"
>
    <div class="gcm-section-inner">
        <div class="gcm-intro">
            <p class="gcm-kicker">
                {{ __('capell-theme-global-culture-magazine::sections.sections.kicker') }}
            </p>
            <h2>{{ $heading }}</h2>
            <p class="gcm-lede">{{ $summary }}</p>
        </div>

        <div class="gcm-grid gcm-grid-3">
            @foreach ($items as $item)
                @php
                    $storyTitle = (string) data_get($item, 'title', data_get($item, 'name', ''));
                    $storyMeta = (string) data_get($item, 'meta', data_get($item, 'category', __('capell-theme-global-culture-magazine::sections.sections.default_meta')));
                    $storyImage = (string) data_get($item, 'imageUrl', data_get($item, 'image', ''));
                    $storyUrl = (string) data_get($item, 'url', data_get($item, 'href', ''));
                @endphp

                <article>
                    <figure class="gcm-plate">
                        <div class="gcm-plate-frame">
                            @if ($storyImage !== '')
                                <img
                                    src="{{ $storyImage }}"
                                    alt="{{ $storyTitle }}"
                                    loading="lazy"
                                />
                            @else
                                <span
                                    class="gcm-plate-initial"
                                    aria-hidden="true"
                                >
                                    {{ mb_substr(trim($storyMeta) !== '' ? trim($storyMeta) : 'A', 0, 1) }}
                                </span>
                            @endif
                        </div>
                        <figcaption class="gcm-plate-caption">
                            <span class="gcm-meta">
                                {{ $storyMeta }}
                            </span>
                            <span>
                                {{ data_get($item, 'care_note', __('capell-theme-global-culture-magazine::sections.sections.care_note')) }}
                            </span>
                        </figcaption>
                    </figure>
                    <h3 class="gcm-story-title">
                        @if ($storyUrl !== '')
                            <a
                                class="gcm-title-link"
                                href="{{ $storyUrl }}"
                            >
                                {{ $storyTitle }}
                            </a>
                        @else
                            {{ $storyTitle }}
                        @endif
                    </h3>
                    <p class="gcm-story-summary">
                        {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
