@php
    use Capell\Core\Support\Security\PublicUrlSanitizer;

    $items = collect(data_get($section, 'items', []))
        ->filter(fn (mixed $item): bool => filled(data_get($item, 'title', data_get($item, 'name'))))
        ->values();
    $heading = data_get($section, 'heading');
    $summary = data_get($section, 'summary');
@endphp

@if ($items->isNotEmpty())
    <section
        id="featured"
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
                        $award = data_get($item, 'award');
                    @endphp

                    <article
                        @if (filled(data_get($item, 'id'))) id="{{ data_get($item, 'id') }}" @endif
                        class="ppc-card"
                    >
                        <figure class="ppc-plate">
                            <div class="ppc-plate-frame">
                                @if (filled($award))
                                    <span class="ppc-badge">{{ $award }}</span>
                                @endif

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
                        @if (filled(data_get($item, 'summary')))
                            <p>{{ data_get($item, 'summary') }}</p>
                        @endif
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif
