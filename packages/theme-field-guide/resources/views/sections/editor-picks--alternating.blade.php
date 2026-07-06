{{--
    editor-picks-curated (Wave 4c signature widget #3, alternating variant):
    the curation desk's note for each pick alternates from the right rail to
    the left rail down the stack, so the curator's voice reads as a running
    margin conversation rather than a repeated card footer.
--}}
@php
    $heading = data_get($section, 'heading', __('capell-theme-field-guide::sections.picks.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-field-guide::sections.picks.summary'));
    $items = collect(data_get($section, 'items', []))
        ->filter(fn (mixed $item): bool => filled(data_get($item, 'title', data_get($item, 'name'))))
        ->values();
@endphp

<section
    id="editor-picks"
    class="fga-section fga-section-panel"
    data-widget="editor-picks-curated"
    data-variant="alternating"
>
    <div class="fga-section-inner">
        <div class="fga-section-head">
            <div class="fga-section-head-copy">
                <p class="fga-kicker">
                    {{ __('capell-theme-field-guide::sections.picks.kicker') }}
                </p>
                <h2>{{ $heading }}</h2>
                <p class="fga-lede">{{ $summary }}</p>
            </div>
            <p class="fga-mono-note">
                {{ __('capell-theme-field-guide::sections.picks.count_note') }}
            </p>
        </div>

        <div class="fga-curator-stack">
            @foreach ($items as $item)
                @php
                    $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                    $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                    $itemTags = data_get($item, 'tags', []);
                    $curatorNote = data_get($item, 'curatorNote', data_get($item, 'summary', data_get($item, 'description')));
                    $isNoteOnStart = $loop->index % 2 === 0;
                @endphp

                <article
                    class="fga-curator-row {{ $isNoteOnStart ? 'fga-curator-row-note-start' : 'fga-curator-row-note-end' }}"
                >
                    <div class="fga-curator-media-col">
                        @if (filled($itemImage))
                            <img
                                src="{{ $itemImage }}"
                                alt="{{ $itemAlt }}"
                                loading="lazy"
                                decoding="async"
                                class="fga-capture-media fga-capture-media-wide"
                            />
                        @else
                            <div
                                class="fga-capture-media fga-capture-media-wide fga-capture-media-empty"
                                aria-hidden="true"
                            ></div>
                        @endif
                    </div>
                    <div class="fga-curator-note-col">
                        <span class="fga-capture-index">
                            {{ __('capell-theme-field-guide::sections.picks.pick_label') }} {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                        </span>
                        <h3>
                            @if (filled($itemUrl))
                                <a
                                    class="fga-title-link"
                                    href="{{ $itemUrl }}"
                                >
                                    {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                                </a>
                            @else
                                {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                            @endif
                        </h3>

                        @if (is_iterable($itemTags) && collect($itemTags)->isNotEmpty())
                            <ul class="fga-chip-row">
                                @foreach ($itemTags as $tag)
                                    <li>
                                        <span
                                            class="fga-chip fga-chip-{{ data_get($tag, 'facet', 'type') }}"
                                        >
                                            {{ data_get($tag, 'label', is_string($tag) ? $tag : '') }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        @if (filled($curatorNote))
                            <blockquote class="fga-curator-note">
                                <p>{{ $curatorNote }}</p>
                                <cite
                                    >{{ __('capell-theme-field-guide::sections.picks.curator_byline') }}</cite
                                >
                            </blockquote>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
