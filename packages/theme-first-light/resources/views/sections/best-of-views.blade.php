@php
    $items = collect(data_get($section, 'items', []))
        ->filter(fn (mixed $item): bool => filled(data_get($item, 'title', data_get($item, 'name'))))
        ->values();
    $buttonLabel = data_get($section, 'button', data_get($section, 'label'));
    $buttonUrl = data_get($section, 'buttonUrl', data_get($section, 'url', '#best-of-views'));
@endphp

<section
    id="best-of-views"
    class="mcf-section"
>
    <div class="mcf-section-inner">
        <p class="mcf-kicker">
            {{ data_get($section, 'kicker', __('capell-theme-first-light::sections.best_of.kicker')) }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-first-light::sections.best_of.heading')) }}
        </h2>
        @if (filled(data_get($section, 'summary')))
            <p class="mcf-lede">{{ data_get($section, 'summary') }}</p>
        @endif

        @if ($items->isNotEmpty())
            <ol class="mcf-rank">
                @foreach ($items as $item)
                    @php
                        $itemTitle = data_get($item, 'title', data_get($item, 'name', ''));
                        $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                        $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                        $itemAlt = data_get($item, 'imageAlt', $itemTitle);
                        $itemMeta = data_get($item, 'meta', data_get($item, 'views'));
                    @endphp

                    <li
                        id="best-{{ $loop->iteration }}"
                        class="mcf-rank-row"
                    >
                        <span
                            class="mcf-rank-number"
                            aria-hidden="true"
                        >
                            {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                        </span>

                        @if (filled($itemImage))
                            <img
                                src="{{ $itemImage }}"
                                alt="{{ $itemAlt }}"
                                width="88"
                                height="55"
                                loading="lazy"
                                decoding="async"
                                class="mcf-rank-thumb"
                            />
                        @else
                            <span
                                class="mcf-rank-thumb mcf-capture-media-empty"
                                aria-hidden="true"
                            ></span>
                        @endif
                        <span class="mcf-rank-body">
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
                        </span>
                        @if (filled($itemMeta))
                            <span class="mcf-meta">{{ $itemMeta }}</span>
                        @endif
                    </li>
                @endforeach
            </ol>
        @endif

        @if (filled($buttonLabel))
            <div class="mcf-actions">
                <a
                    class="mcf-button mcf-button-secondary"
                    href="{{ $buttonUrl }}"
                >
                    {{ $buttonLabel }}
                </a>
            </div>
        @endif
    </div>
</section>
