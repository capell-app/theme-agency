@php
    $heading = data_get($section, 'heading', __('capell-theme-quiet-type::sections.scroll_position_menu.heading'));
    $items = collect(data_get($section, 'items', data_get($section, 'headings', [])));
@endphp

{{--
    scroll-position-menu, left-rail variant — same shared scroll-spy.js
    integration as the default treatment (see scroll-position-menu.blade.php),
    docked to the start (left) of the layout rather than the end.
--}}
<nav
    id="scroll-position-menu"
    class="eser-section eser-scroll-menu eser-scroll-menu-start"
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
