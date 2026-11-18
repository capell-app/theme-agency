@php
    use Capell\Core\Support\Security\PublicUrlSanitizer;

    $items = collect(data_get($section, 'items', data_get($section, 'posts', [])))
        ->filter(fn (mixed $item): bool => filled(data_get($item, 'title', data_get($item, 'name'))))
        ->values();
    $heading = data_get($section, 'heading');
@endphp

@if (filled($heading) || $items->isNotEmpty())
    <section
        id="content-listing"
        class="ppc-section"
    >
        <div class="ppc-section-inner">
            @if (filled(data_get($section, 'kicker')))
                <p class="ppc-kicker">{{ data_get($section, 'kicker') }}</p>
            @endif

            @if (filled($heading))
                <h2>{{ $heading }}</h2>
            @endif

            @if ($items->isNotEmpty())
                <div class="ppc-index">
                    @foreach ($items as $item)
                        @php
                            $itemUrl = PublicUrlSanitizer::sanitize(data_get($item, 'url', data_get($item, 'href')));
                            $itemImage = PublicUrlSanitizer::sanitize(data_get($item, 'image', data_get($item, 'imageUrl')));
                            $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                        @endphp

                        <article
                            class="ppc-index-row {{ filled($itemImage) ? 'ppc-index-row-media' : '' }}"
                        >
                            @if (filled($itemImage))
                                <x-capell::image-source
                                    :image="$itemImage"
                                    alt="{{ $itemAlt }}"
                                    loading="lazy"
                                    decoding="async"
                                    width="176"
                                    height="132"
                                    class="ppc-index-thumb"
                                />
                            @endif

                            <div>
                                @if (filled(data_get($item, 'category', data_get($item, 'meta'))))
                                    <p class="ppc-meta">{{ data_get($item, 'category', data_get($item, 'meta')) }}</p>
                                @endif

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
                            </div>
                            @if (filled(data_get($item, 'summary', data_get($item, 'description'))))
                                <p>{{ data_get($item, 'summary', data_get($item, 'description')) }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            @else
                <p class="ppc-lede">{{ __('capell-theme-agency::sections.listing.empty') }}</p>
            @endif
        </div>
    </section>
@endif
