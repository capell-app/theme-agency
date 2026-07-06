@php
    $heading = data_get($section, 'heading', __('capell-theme-quiet-type::sections.serialized_chapters.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-quiet-type::sections.serialized_chapters.summary'));
    $currentTitle = data_get($section, 'currentTitle', data_get($section, 'current_title'));
    $previous = data_get($section, 'previous');
    $next = data_get($section, 'next');
    $chapters = collect(data_get($section, 'chapters', []));
@endphp

{{--
    serialized-chapters-navigator (Part 2 §B) — a serialized long-form
    piece's chapter-to-chapter navigation: previous/next links either side
    of the current chapter, plus the full chapter index below so a reader
    can jump anywhere in the run. Default variant renders the full index
    inline; the `compact` variant (serialized-chapters--compact.blade.php)
    keeps only the prev/next pair, for surfaces without room for the whole
    contents list.
--}}
<section
    id="serialized-chapters"
    class="eser-section"
>
    <div class="eser-section-inner">
        <p class="eser-eyebrow">
            {{ __('capell-theme-quiet-type::sections.serialized_chapters.eyebrow') }}
        </p>
        <h2>{{ $heading }}</h2>
        <hr class="eser-heading-rule" />
        @if (filled($summary))
            <p class="eser-summary">{{ $summary }}</p>
        @endif

        <nav
            class="eser-chapter-pager"
            aria-label="{{ __('capell-theme-quiet-type::sections.serialized_chapters.pager_label') }}"
        >
            <a
                class="eser-chapter-pager-link {{ blank($previous) ? 'eser-chapter-pager-link-disabled' : '' }}"
                @if (filled($previous)) href="{{ data_get($previous, 'url', '#') }}" @endif
            >
                <span
                    class="eser-chapter-pager-direction"
                    >{{ __('capell-theme-quiet-type::sections.serialized_chapters.previous') }}</span
                >
                <span
                    class="eser-chapter-pager-title"
                    >{{ data_get($previous, 'title', __('capell-theme-quiet-type::sections.serialized_chapters.none')) }}</span
                >
            </a>

            @if (filled($currentTitle))
                <p class="eser-chapter-pager-current">{{ $currentTitle }}</p>
            @endif

            <a
                class="eser-chapter-pager-link eser-chapter-pager-link-next {{ blank($next) ? 'eser-chapter-pager-link-disabled' : '' }}"
                @if (filled($next)) href="{{ data_get($next, 'url', '#') }}" @endif
            >
                <span
                    class="eser-chapter-pager-direction"
                    >{{ __('capell-theme-quiet-type::sections.serialized_chapters.next') }}</span
                >
                <span
                    class="eser-chapter-pager-title"
                    >{{ data_get($next, 'title', __('capell-theme-quiet-type::sections.serialized_chapters.none')) }}</span
                >
            </a>
        </nav>

        @if ($chapters->isNotEmpty())
            <ol class="eser-chapter-index">
                @foreach ($chapters as $chapter)
                    @php
                        $chapterUrl = data_get($chapter, 'url', data_get($chapter, 'href'));
                        $isCurrent = data_get($chapter, 'current', false) === true;
                    @endphp
                    <li>
                        @if ($isCurrent)
                            <span
                                class="eser-chapter-index-current"
                                aria-current="true"
                            >
                                {{ data_get($chapter, 'title', '') }}
                            </span>
                        @elseif (filled($chapterUrl))
                            <a
                                class="eser-title-link"
                                href="{{ $chapterUrl }}"
                            >
                                {{ data_get($chapter, 'title', '') }}
                            </a>
                        @else
                            {{ data_get($chapter, 'title', '') }}
                        @endif
                    </li>
                @endforeach
            </ol>
        @endif
    </div>
</section>
