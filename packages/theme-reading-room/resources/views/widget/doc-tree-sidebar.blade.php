@php
    $heading = (string) ($widget->getMeta('heading') ?? __('capell-theme-reading-room::sections.doc_tree.heading'));
    $tree = is_array($widget->getMeta('tree')) ? $widget->getMeta('tree') : [];
    $currentUrl = (string) ($widget->getMeta('currentUrl') ?? '');
    $variant = (string) ($widget->getMeta('variant') ?? 'nested');
@endphp

{{--
    `doc-tree-sidebar` — a native `<details>`-based collapsible tree, no JS
    required for expand/collapse (§0.4: near-zero per-theme JS). Rendered
    into the brand-new `docs-sidebar` Layout Builder area (Wave 6, §E).

    Two variants:
    - `nested` (default): folders may contain folders, native `<details>`
      nesting throughout.
    - `flat-groups`: one level of `<details>` groups, each containing only
      leaf links — a flatter, denser index for smaller doc trees.

    `rr-shell` is carried on this widget's root for the same reason
    documented in Night Shift's bespoke widget views.
--}}
<nav
    class="rr-shell"
    aria-label="{{ $heading }}"
>
    <p class="rr-toc-heading">{{ $heading }}</p>

    <div
        class="rr-tree"
        data-doc-tree-variant="{{ $variant }}"
    >
        @foreach ($tree as $node)
            @php
                $nodeChildren = is_array(data_get($node, 'children')) ? data_get($node, 'children') : [];
                $nodeUrl = (string) data_get($node, 'url', '');
                $isCurrent = $nodeUrl !== '' && $nodeUrl === $currentUrl;
            @endphp

            @if ($nodeChildren !== [])
                <details
                    class="rr-tree-node"
                    @if ($variant === 'flat-groups' || $isCurrent) open @endif
                >
                    <summary>{{ data_get($node, 'label', '') }}</summary>

                    <div class="rr-tree-children">
                        @foreach ($nodeChildren as $child)
                            @php
                                $childUrl = (string) data_get($child, 'url', '');
                                $childIsCurrent = $childUrl !== '' && $childUrl === $currentUrl;
                            @endphp

                            <a
                                href="{{ $childUrl }}"
                                class="rr-tree-leaf"
                                @if ($childIsCurrent) aria-current="page" @endif
                            >
                                {{ data_get($child, 'label', '') }}
                            </a>
                        @endforeach
                    </div>
                </details>
            @else
                <a
                    href="{{ $nodeUrl }}"
                    class="rr-tree-leaf"
                    @if ($isCurrent) aria-current="page" @endif
                >
                    {{ data_get($node, 'label', '') }}
                </a>
            @endif
        @endforeach
    </div>
</nav>
