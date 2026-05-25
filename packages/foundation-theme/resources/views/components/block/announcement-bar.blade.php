@props([
    'align' => $block->getMeta('align', 'center'),
    'title' => $block->translation?->title,
    'content' => $block->translation?->content,
    'container',
    'loop',
    'containerKey',
    'containerWidth' => null,
    'block',
])

<x-capell-foundation-theme::block.wrapper
    class="capell-block-announcement-bar block-announcement-bar"
    container-class="text-center"
    :$container
    :$containerKey
    :$containerWidth
    :index="$loop->index"
    :$block
>
    <div class="rounded-md border border-current/15 px-4 py-3">
        <x-capell::content
            class="block-content"
            :compact="true"
            :content="$content"
            :content-type="$block->type->content_structure"
            :heading-size="$block->getMeta('heading_size', 'h3')"
            :title="$title"
            :text-align="$align"
        />

        @if ($block->getMeta('actions'))
            <x-capell::actions
                class="mt-3"
                :actions="$block->getMeta('actions')"
                :align="$align"
            />
        @endif
    </div>
</x-capell-foundation-theme::block.wrapper>
