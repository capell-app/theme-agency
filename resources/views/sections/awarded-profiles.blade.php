@php
    use Capell\Core\Support\Security\PublicUrlSanitizer;

    $heading = data_get($section, 'heading', __('capell-theme-agency::sections.updates.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-agency::sections.updates.summary'));
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-agency::sections.updates.release_title'), 'summary' => __('capell-theme-agency::sections.updates.release_summary')],
        ['title' => __('capell-theme-agency::sections.updates.beta_title'), 'summary' => __('capell-theme-agency::sections.updates.beta_summary')],
    ]);
    $ctaLabel = data_get($section, 'label', __('capell-theme-agency::sections.updates.button'));
    $ctaUrl = PublicUrlSanitizer::sanitize(data_get($section, 'url', '#awards')) ?? '#awards';
@endphp

<section
    id="awards"
    class="ppc-section ppc-section-dark"
>
    <div class="ppc-section-inner ppc-split">
        <div>
            <p class="ppc-kicker">{{ __('capell-theme-agency::sections.updates.kicker') }}</p>
            <h2>{{ $heading }}</h2>
            <p class="ppc-lede">{{ $summary }}</p>
            <a
                class="ppc-button"
                href="{{ $ctaUrl }}"
            >
                {{ $ctaLabel }}
            </a>
        </div>
        <div class="ppc-grid">
            @foreach ($items as $item)
                @php
                    $itemImage = PublicUrlSanitizer::sanitize(data_get($item, 'image', data_get($item, 'imageUrl')));
                    $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    $itemUrl = PublicUrlSanitizer::sanitize(data_get($item, 'url', data_get($item, 'href')));
                    $award = data_get($item, 'award', __('capell-theme-agency::sections.updates.default_award'));
                @endphp

                <article
                    @if (filled(data_get($item, 'id'))) id="{{ data_get($item, 'id') }}" @endif
                    class="ppc-card"
                >
                    <figure class="ppc-plate">
                        <div class="ppc-plate-frame">
                            <span class="ppc-badge">{{ $award }}</span>

                            @if (filled($itemImage))
                                <img
                                    src="{{ $itemImage }}"
                                    alt="{{ $itemAlt }}"
                                    loading="lazy"
                                    decoding="async"
                                    width="400"
                                    height="300"
                                    class="ppc-plate-media"
                                />
                            @else
                                <div
                                    class="ppc-plate-media ppc-plate-media-empty"
                                    aria-hidden="true"
                                ></div>
                            @endif
                        </div>
                    </figure>
                    <h3>
                        @if (filled($itemUrl))
                            <a
                                class="ppc-title-link"
                                href="{{ $itemUrl }}"
                            >
                                {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                            </a>
                        @else
                            {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                        @endif
                    </h3>
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
