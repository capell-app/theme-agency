@php
    $items = collect(data_get($section, 'items', []))
        ->filter(fn (mixed $item): bool => filled(data_get($item, 'title', data_get($item, 'name'))))
        ->values();
@endphp

<section
    id="app-website-icons"
    class="mcf-section"
>
    <div class="mcf-section-inner">
        <p class="mcf-kicker">
            {{ data_get($section, 'kicker', __('capell-theme-first-light::sections.icons.kicker')) }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-first-light::sections.icons.heading')) }}
        </h2>
        @if (filled(data_get($section, 'summary')))
            <p class="mcf-lede">{{ data_get($section, 'summary') }}</p>
        @endif

        @if ($items->isNotEmpty())
            <div class="mcf-icon-grid">
                @foreach ($items as $item)
                    @php
                        $itemTitle = data_get($item, 'title', data_get($item, 'name', ''));
                        $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                        $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                        $itemInitial = mb_strtoupper(mb_substr(trim($itemTitle), 0, 1));
                    @endphp

                    <article class="mcf-icon-card">
                        @if (filled($itemImage))
                            <img
                                src="{{ $itemImage }}"
                                alt="{{ data_get($item, 'imageAlt', $itemTitle) }}"
                                width="48"
                                height="48"
                                loading="lazy"
                                decoding="async"
                                class="mcf-icon-tile"
                            />
                        @else
                            <span
                                class="mcf-icon-tile"
                                aria-hidden="true"
                            >
                                {{ $itemInitial }}
                            </span>
                        @endif
                        @if (filled(data_get($item, 'meta', data_get($item, 'category'))))
                            <p class="mcf-meta">
                                {{ data_get($item, 'meta', data_get($item, 'category')) }}
                            </p>
                        @endif

                        <h3>
                            @if (filled($itemUrl))
                                <a
                                    class="mcf-title-link"
                                    href="{{ $itemUrl }}"
                                >
                                    {{ $itemTitle }}
                                </a>
                            @else
                                {{ $itemTitle }}
                            @endif
                        </h3>
                        @if (filled(data_get($item, 'summary', data_get($item, 'description'))))
                            <p>
                                {{ data_get($item, 'summary', data_get($item, 'description')) }}
                            </p>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
