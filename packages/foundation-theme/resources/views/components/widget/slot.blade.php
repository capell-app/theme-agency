@props(['pageSlot', 'container' => null, 'containerKey' => null, 'containerWidth' => null, 'loop' => null, 'widget' => null])

<x-capell-foundation-theme::widget.wrapper
    class="capell-widget-slot widget-page-slot"
    :$container
    :$containerKey
    :$containerWidth
    :index="$loop?->index ?? 0"
    :$widget
>
    <div>
        {{ $pageSlot }}
    </div>
</x-capell-foundation-theme::widget.wrapper>
