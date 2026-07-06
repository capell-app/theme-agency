{{--
    floating-glass-nav (Wave 4c signature widget, §D "glassmorphism
    showcase"): a floating glass-treated navigation element — a slim pill of
    section shortcuts that sits above the page content, carrying the shared
    `.glass-surface` backdrop-filter treatment rather than an opaque bar.

    No JS: this is a plain anchor list of in-page `#anchor` links (or
    absolute URLs, if the payload supplies them); "floating" is achieved
    with `position: sticky` (see `.floating-glass-nav` in
    `resources/css/theme-liquid-glass.css`), not a JS scroll listener.

    Variant contract (see glass-feature-card.blade.php's docblock for why
    this is inline `variant`-key branching): two reachable states via
    payload `variant`:
    - `pill` (default): a single centred rounded-full pill.
    - `bar`: a full-width translucent bar, links left-aligned — for callers
      who want the same shortcuts without the floating pill's centred,
      compact footprint.
--}}
@php
    $label = (string) ($widget->getMeta('label') ?? __('capell-theme-liquid-glass::generic.floating_glass_nav_label'));
    $items = is_array($widget->getMeta('items')) ? $widget->getMeta('items') : [];
    $variant = (string) ($widget->getMeta('variant') ?? 'pill');
@endphp

<nav
    @class ([
        'glass-surface floating-glass-nav sticky top-4 z-10 mx-auto flex w-fit items-center gap-1 border border-(--theme-foreground)/10 bg-(--theme-surface)/75 px-2 py-2',
        'floating-glass-nav--bar w-full justify-start' => $variant === 'bar',
    ])
    aria-label="{{ $label }}"
    data-variant="{{ $variant }}"
>
    @foreach ($items as $item)
        <a
            href="{{ data_get($item, 'url', '#') }}"
            class="inline-flex min-h-9 items-center justify-center rounded-full px-4 py-2 text-sm font-bold text-(--theme-foreground) hover:bg-(--theme-foreground)/10"
        >
            {{ data_get($item, 'label', '') }}
        </a>
    @endforeach
</nav>
