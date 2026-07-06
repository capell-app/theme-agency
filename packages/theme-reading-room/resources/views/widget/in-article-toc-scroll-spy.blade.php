@php
    $heading = (string) ($widget->getMeta('heading') ?? __('capell-theme-reading-room::sections.toc.heading'));
    $items = is_array($widget->getMeta('items')) ? $widget->getMeta('items') : [];
    $variant = (string) ($widget->getMeta('variant') ?? 'rail');
@endphp

{{--
    `in-article-toc-scroll-spy` — reuses the shared `scroll-spy.js` module
    (Wave 2.6) rather than writing new scroll-tracking JS: CSS scroll-driven
    animation is preferred where supported (the module adds
    `data-scroll-spy-css-driven`, consumed here by
    `[data-scroll-spy-css-driven] a[aria-current="true"]` in
    `theme-reading-room.css`), falling back to `IntersectionObserver`
    everywhere else. `aria-current="true"` on the active link is the
    module's a11y contract either way — see
    `packages/theme-foundation/resources/js/widgets/widget/scroll-spy.js`'s
    header doc comment.

    Two variants:
    - `rail` (default): a vertical sticky side rail (this widget's usual
      placement in the third `.rr-doc-layout` column on wide viewports).
    - `inline`: a compact "On this page" box placed inline at the top of the
      article body, for surfaces without the third-column layout.
--}}
<nav
    class="rr-toc {{ $variant === 'inline' ? 'rr-toc-inline' : '' }}"
    data-scroll-spy
    aria-label="{{ $heading }}"
>
    <p class="rr-toc-heading">{{ $heading }}</p>

    @foreach ($items as $item)
        <a href="#{{ data_get($item, 'anchor', '') }}">
            {{ data_get($item, 'label', '') }}
        </a>
    @endforeach
</nav>
