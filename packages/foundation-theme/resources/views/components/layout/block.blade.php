@php
    use Capell\FoundationTheme\View\Components\Block\Page\Children as PageChildrenComponent;
    use Capell\FoundationTheme\View\Components\Block\Page\Content as PageContentComponent;
    use Capell\FoundationTheme\View\Components\Block\Page\Latest as PageLatestComponent;
    use Capell\FoundationTheme\View\Components\Block\Page\Siblings as PageSiblingsComponent;
    use Capell\FoundationTheme\View\Components\Block\Slot as SlotComponent;
    use Capell\Frontend\Facades\Frontend;
    use Capell\LayoutBuilder\Support\Livewire\OpaqueBlockReference;
    use Illuminate\Contracts\View\View as ViewContract;
    use Livewire\Blaze\Blaze;
@endphp

@props([
    'component',
    'container',
    'containerColspan' => null,
    'containerKey',
    'containerIndex',
    'containerWidth' => null,
    'layout',
    'loop',
    'occurrence' => $blockData['occurrence'] ?? 1,
    'pageSlot' => null,
    'type',
    'block',
    'blockIndex',
    'blockData',
])

@if ($type === 'blade')
    @php
        $pageBlockComponent = match ($component) {
            'capell::block.page.children' => PageChildrenComponent::class,
            'capell::block.page.content' => PageContentComponent::class,
            'capell::block.page.latest' => PageLatestComponent::class,
            'capell::block.page.siblings' => PageSiblingsComponent::class,
            'capell::block.slot' => SlotComponent::class,
            'capell::block.page.children' => PageChildrenComponent::class,
            'capell::block.page.content' => PageContentComponent::class,
            'capell::block.page.latest' => PageLatestComponent::class,
            'capell::block.page.siblings' => PageSiblingsComponent::class,
            'capell::block.slot' => SlotComponent::class,
            default => null,
        };
    @endphp

    @if ($pageBlockComponent !== null)
        @php
            $pageBlock = new $pageBlockComponent(
                container: $container,
                containerKey: $containerKey,
                blockIndex: $blockIndex,
                loop: $loop,
                block: $block,
                blockData: $blockData,
                pageSlot: $pageSlot,
            );

            $pageBlockOutput = $pageBlock->render();

            if ($pageBlockOutput instanceof ViewContract) {
                $wasBlazeEnabled = Blaze::isEnabled();
                Blaze::disable();

                try {
                    $pageBlockOutput = $pageBlockOutput->render();
                } finally {
                    if ($wasBlazeEnabled) {
                        Blaze::enable();
                    }
                }
            }
        @endphp

        {!! $pageBlockOutput !!}
    @else
        <x-dynamic-component
            class="site-layout-block"
            :component="$component"
            :$container
            :$containerColspan
            :$containerKey
            :$containerIndex
            :$containerWidth
            :block="$block"
            :blockData="$blockData"
            :blockIndex="$blockIndex"
            :$loop
            :$pageSlot
            :$occurrence
            :$block
            :$blockData
            :$blockIndex
        />
    @endif
@elseif ($type === 'livewire')
    @php
        $blockReference = OpaqueBlockReference::encode([
            'container_key' => $containerKey,
            'block_key' => $blockData['block_key'] ?? $block->key,
            'layout_id' => $layout?->getKey(),
            'language_id' => Frontend::language()?->getKey(),
            'occurrence' => $occurrence,
            'page_id' => Frontend::page()?->getKey(),
            'page_type' => Frontend::page()?->getMorphClass(),
            'site_id' => Frontend::site()?->getKey(),
            'block_data' => $blockData,
            'block_index' => $blockIndex,
        ]);
    @endphp

    @livewire($component,
        [
            'blockReference' => $blockReference,
        ],
        key($containerKey . '-' . $block->key . '-' . $occurrence))
@endif
