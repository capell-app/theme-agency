@php
    $items = collect(data_get($section, 'items', data_get($section, 'entries', [])))
        ->filter(fn (mixed $item): bool => filled(data_get($item, 'title', data_get($item, 'name'))))
        ->values();
@endphp

<section
    id="curation-feed"
    class="mcf-section"
>
    <div class="mcf-section-inner">
        <p class="mcf-kicker">
            {{ data_get($section, 'kicker', __('capell-theme-first-light::sections.feed.kicker')) }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-first-light::sections.feed.heading')) }}
        </h2>
        @if (filled(data_get($section, 'summary')))
            <p class="mcf-lede">{{ data_get($section, 'summary') }}</p>
        @endif

        @if ($items->isNotEmpty())
            <div class="mcf-feed">
                @foreach ($items as $item)
                    @php
                        $itemTitle = data_get($item, 'title', data_get($item, 'name', ''));
                        $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                        $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                        $itemAlt = data_get($item, 'imageAlt', $itemTitle);
                        $itemMaker = data_get($item, 'maker', data_get($item, 'author'));
                        $itemSource = data_get($item, 'source', data_get($item, 'domain'));
                        $itemMeta = data_get($item, 'meta', data_get($item, 'category'));
                        $itemTags = collect(data_get($item, 'tags', []))->filter(fn (mixed $tag): bool => filled($tag))->values();
                    @endphp

                    <article
                        id="entry-{{ $loop->iteration }}"
                        class="mcf-entry"
                    >
                        @if (filled($itemImage))
                            <img
                                src="{{ $itemImage }}"
                                alt="{{ $itemAlt }}"
                                width="1200"
                                height="750"
                                loading="lazy"
                                decoding="async"
                                class="mcf-capture-media"
                            />
                        @else
                            <div
                                class="mcf-capture-media mcf-capture-media-empty"
                                aria-hidden="true"
                            ></div>
                        @endif

                        <div class="mcf-entry-caption">
                            <div>
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
                                @if (filled($itemMaker) || filled($itemSource))
                                    <p class="mcf-entry-source">
                                        {{ collect([$itemMaker, $itemSource])->filter()->implode(' · ') }}
                                    </p>
                                @endif
                            </div>
                            @if (filled($itemMeta))
                                <p class="mcf-meta">{{ $itemMeta }}</p>
                            @endif
                        </div>

                        @if (filled(data_get($item, 'summary', data_get($item, 'description'))))
                            <p class="mcf-tiny">
                                {{ data_get($item, 'summary', data_get($item, 'description')) }}
                            </p>
                        @endif

                        @if ($itemTags->isNotEmpty())
                            <ul class="mcf-tags">
                                @foreach ($itemTags as $tag)
                                    <li class="mcf-tag">{{ $tag }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </article>
                @endforeach
            </div>
        @else
            <div class="mcf-empty">
                <p class="mcf-empty-heading">
                    {{ __('capell-theme-first-light::sections.feed.empty_heading') }}
                </p>
                <p class="mcf-empty-note">
                    {{ __('capell-theme-first-light::sections.feed.empty') }}
                </p>
            </div>
        @endif
    </div>
</section>
