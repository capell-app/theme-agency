@php
    $heading = data_get($section, 'heading', __('capell-theme-quiet-type::sections.author_profiles.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-quiet-type::sections.author_profiles.summary'));
    $items = collect(data_get($section, 'items', data_get($section, 'authors', [])));
@endphp

{{--
    author-bio-with-bibliography (Part 2 §B). Adds a per-author list of
    prior work beneath the bio — the piece the base author-profiles
    treatment doesn't carry — so a reader can trace an essayist's other
    writing without leaving the masthead.
--}}
<section
    id="author-profiles"
    class="eser-section"
>
    <div class="eser-section-inner">
        <p class="eser-eyebrow">
            {{ __('capell-theme-quiet-type::sections.author_profiles.eyebrow') }}
        </p>
        <h2>{{ $heading }}</h2>
        <hr class="eser-heading-rule" />
        @if (filled($summary))
            <p class="eser-summary">{{ $summary }}</p>
        @endif

        @if ($items->isNotEmpty())
            <div class="eser-portrait-grid">
                @foreach ($items as $item)
                    @php
                        $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                        $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                        $bibliography = collect(data_get($item, 'bibliography', []));
                    @endphp

                    <article>
                        @if (filled($itemImage))
                            <img
                                src="{{ $itemImage }}"
                                alt="{{ $itemAlt }}"
                                width="104"
                                height="104"
                                loading="lazy"
                                decoding="async"
                                class="eser-portrait"
                            />
                        @endif

                        <h3 class="eser-portrait-name">
                            {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                        </h3>
                        <p class="eser-portrait-bio">
                            {{ data_get($item, 'summary', data_get($item, 'bio', '')) }}
                        </p>

                        @if ($bibliography->isNotEmpty())
                            <ul class="eser-bibliography">
                                @foreach ($bibliography as $work)
                                    <li class="eser-bibliography-item">
                                        <span
                                            class="eser-bibliography-title"
                                            >{{ data_get($work, 'title', '') }}</span
                                        >
                                        @if (filled(data_get($work, 'year')))
                                            <span
                                                class="eser-bibliography-year"
                                                >{{ data_get($work, 'year') }}</span
                                            >
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
