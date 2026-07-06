@php
    // §0.3 payload cap: a heading map is a short curated list well under any
    // §0.3 cap; no explicit cap needed. §0.1 determinism: link order is
    // plain document order from the payload, never computed/randomised.
    $heading = data_get($section, 'heading', __('capell-theme-quiet-type::sections.scroll_position_menu.heading'));
    $items = collect(data_get($section, 'items', data_get($section, 'headings', [])));
@endphp

{{--
    scroll-position-menu (Part 2 §B) — a heading map rendered as a side
    rail, using the shared Wave 2.6 scroll-spy.js module: CSS
    scroll-driven first (this theme's `.eser-section` reveal already
    proves `animation-timeline: view()` support server-side via
    `@supports`), IntersectionObserver fallback, `aria-current` on the
    active link — exactly the contract scroll-spy.js documents. No new
    scroll-tracking JS is written here; `data-scroll-spy` on the nav is
    the entire integration surface. Default variant docks the rail to the
    end (right); the `left-rail` variant (scroll-position-menu--left-rail.blade.php)
    docks it to the start.
--}}
<nav
    id="scroll-position-menu"
    class="eser-section eser-scroll-menu eser-scroll-menu-end"
    data-scroll-spy
    aria-label="{{ __('capell-theme-quiet-type::sections.scroll_position_menu.aria_label') }}"
>
    <div class="eser-section-inner eser-scroll-menu-inner">
        <p class="eser-eyebrow">{{ $heading }}</p>

        @if ($items->isNotEmpty())
            <ol class="eser-scroll-menu-list">
                @foreach ($items as $item)
                    @php
                        $anchor = ltrim((string) data_get($item, 'anchor', data_get($item, 'id', '')), '#');
                    @endphp
                    <li>
                        <a href="#{{ $anchor }}">
                            {{ data_get($item, 'label', data_get($item, 'title', '')) }}
                        </a>
                    </li>
                @endforeach
            </ol>
        @endif
    </div>
</nav>
