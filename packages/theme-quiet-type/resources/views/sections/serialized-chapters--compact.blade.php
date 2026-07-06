@php
    $currentTitle = data_get($section, 'currentTitle', data_get($section, 'current_title'));
    $previous = data_get($section, 'previous');
    $next = data_get($section, 'next');
@endphp

{{--
    serialized-chapters-navigator, compact variant — prev/next pager only,
    no rendered chapter index. Used on surfaces (e.g. a single-chapter
    detail page) that have no room for the full contents list.
--}}
<section
    id="serialized-chapters"
    class="eser-section eser-section-compact"
>
    <div class="eser-section-inner">
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
    </div>
</section>
