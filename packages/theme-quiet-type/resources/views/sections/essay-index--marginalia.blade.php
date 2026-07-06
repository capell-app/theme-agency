@php
    $heading = data_get($section, 'heading', __('capell-theme-quiet-type::sections.essay_index.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-quiet-type::sections.essay_index.summary'));
    $items = collect(data_get($section, 'items', data_get($section, 'essays', [])));
@endphp

{{--
    essay-with-dropcap-and-marginalia (Part 2 §B, quiet-type's headline
    mechanic). This variant differs from the base essay-index by giving the
    featured essay a genuine long-form treatment: a dropcap lead paragraph
    (reusing the `.eser-dropcap` prose rule already carried by
    editorial-statement) plus a margin aside carrying the editors' pull
    annotations for that piece — the "marginalia" half of the mechanic.
    Non-featured rows keep the plain numbered-index treatment.
--}}
<section
    id="essay-index"
    class="eser-section eser-section-wide"
>
    <div class="eser-section-inner">
        <p class="eser-eyebrow">
            {{ __('capell-theme-quiet-type::sections.essay_index.eyebrow') }}
        </p>
        <h2>{{ $heading }}</h2>
        <hr class="eser-heading-rule" />
        @if (filled($summary))
            <p class="eser-summary">{{ $summary }}</p>
        @endif

        @if ($items->isNotEmpty())
            <div class="eser-index">
                @foreach ($items as $item)
                    @php
                        $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                        $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                        $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                        $author = data_get($item, 'author', data_get($item, 'byline'));
                        $isFeatured = $loop->first && filled($itemImage);
                        $marginalia = collect(data_get($item, 'marginalia', []));
                    @endphp

                    <article
                        class="eser-index-row {{ $isFeatured ? 'eser-index-row-featured' : '' }}"
                    >
                        @if (! $isFeatured)
                            <span
                                class="eser-index-numeral"
                                aria-hidden="true"
                            >
                                {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                            </span>
                        @endif

                        <div
                            class="{{ $isFeatured && $marginalia->isNotEmpty() ? 'eser-marginalia-layout' : '' }}"
                        >
                            <div>
                                @if ($isFeatured)
                                    <img
                                        src="{{ $itemImage }}"
                                        alt="{{ $itemAlt }}"
                                        width="1400"
                                        height="600"
                                        loading="lazy"
                                        decoding="async"
                                        class="eser-index-figure"
                                    />
                                @endif

                                @if (filled($author))
                                    <p class="eser-index-byline">{{ $author }}</p>
                                @endif

                                <h3 class="eser-index-title">
                                    @if (filled($itemUrl))
                                        <a
                                            class="eser-title-link"
                                            href="{{ $itemUrl }}"
                                        >
                                            {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                                        </a>
                                    @else
                                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                                    @endif
                                </h3>

                                @if ($isFeatured)
                                    <p class="eser-index-dek eser-dropcap">
                                        {{ data_get($item, 'summary', data_get($item, 'dek', '')) }}
                                    </p>
                                @else
                                    <p class="eser-index-dek">
                                        {{ data_get($item, 'summary', data_get($item, 'dek', '')) }}
                                    </p>
                                @endif
                            </div>

                            @if ($isFeatured && $marginalia->isNotEmpty())
                                <aside
                                    class="eser-marginalia"
                                    aria-label="{{ __('capell-theme-quiet-type::sections.essay_index.marginalia_label') }}"
                                >
                                    @foreach ($marginalia as $note)
                                        <p class="eser-marginalia-note">{{ $note }}</p>
                                    @endforeach
                                </aside>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
