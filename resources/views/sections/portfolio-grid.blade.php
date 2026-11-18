@php
    use Capell\Core\Support\Security\PublicUrlSanitizer;

    $heading = data_get($section, 'heading');
    $summary = data_get($section, 'summary');
    $items = collect(data_get($section, 'items', []))
        ->filter(fn (mixed $item): bool => filled(data_get($item, 'title', data_get($item, 'name'))))
        ->values();
@endphp

@if ($items->isNotEmpty())
    <section
        id="portfolio-grid"
        class="ppc-section"
    >
        <div class="ppc-section-inner">
            @if (filled(data_get($section, 'kicker')))
                <p class="ppc-kicker">{{ data_get($section, 'kicker') }}</p>
            @endif

            @if (filled($heading))
                <h2>{{ $heading }}</h2>
            @endif

            @if (filled($summary))
                <p class="ppc-lede">{{ $summary }}</p>
            @endif

            <div class="ppc-grid">
                @foreach ($items as $item)
                    @php
                        $itemImage = PublicUrlSanitizer::sanitize(data_get($item, 'image', data_get($item, 'imageUrl')));
                        $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                        $itemUrl = PublicUrlSanitizer::sanitize(data_get($item, 'url', data_get($item, 'href')));
                    @endphp

                    <article
                        @if (filled(data_get($item, 'id'))) id="{{ data_get($item, 'id') }}" @endif
                        class="ppc-card"
                    >
                        <figure class="ppc-plate">
                            <div class="ppc-plate-frame">
                                @if (filled($itemImage))
                                    <x-capell::image-source
                                        :image="$itemImage"
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
                        @if (filled(data_get($item, 'summary', data_get($item, 'description'))))
                            <p>{{ data_get($item, 'summary', data_get($item, 'description')) }}</p>
                        @endif
                        @if (filled(data_get($item, 'meta', data_get($item, 'category'))) || filled(data_get($item, 'care_note')))
                            <div class="ppc-card-meta-row">
                                @if (filled(data_get($item, 'meta', data_get($item, 'category'))))
                                    <span class="ppc-chip">
                                        {{ data_get($item, 'meta', data_get($item, 'category')) }}
                                    </span>
                                @endif

                                @if (filled(data_get($item, 'care_note')))
                                    <span class="ppc-meta">
                                        {{ data_get($item, 'care_note') }}
                                    </span>
                                @endif
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif
