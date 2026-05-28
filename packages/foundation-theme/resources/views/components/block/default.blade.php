<?php
use Capell\Frontend\Facades\Frontend;

$theme = Frontend::theme();

?>

@props([
    'align' => $block->getMeta('align'),
    'headingSize' => $block->getMeta('heading_size', 'h2'),
    'colorScheme' => $block->getMeta('color_scheme'),
    'size' => $block->getMeta('size'),
    'style' => $block->getMeta('style', 'row'),
    'reverseOrder' => $block->getMeta('reverse_order'),
    'title' => $block->translation?->title,
    'content' => $block->translation?->content,
    'container',
    'loop',
    'containerKey',
    'containerWidth' => null,
    'block',
])

<x-capell-foundation-theme::block.wrapper
    class="capell-block-default block-default"
    :container-class="
        'flex flex-col gap-x-5 gap-y-3 lg:gap-x-10 '
        . (match ($style) {
            'row' => ($reverseOrder ? 'md:flex-row-reverse' : 'md:flex-row'),
            default => null,
        })
    "
    :$container
    :$containerKey
    :$containerWidth
    :index="$loop->index"
    :$block
>
    <div
        @class([
            '@container flex-1',
            'my-auto py-4' => $block->image,
        ])
    >
        @if ($content || $title)
            <x-capell::content
                class="block-content mb-2"
                :compact="true"
                :content="$content"
                :content-type="$block->type->content_structure"
                :color="$colorScheme"
                :divider="$block->getMeta('content_divider')"
                :heading-size="$headingSize"
                :muted="in_array($containerKey, $theme->secondary_containers)"
                :heading-style="$block->getMeta('heading_style')"
                :title="$title"
                :text-align="$align"
            />
        @endif

        @if ($block->getMeta('actions'))
            <x-capell::actions
                class="mt-4"
                :actions="$block->getMeta('actions')"
                :align="$align"
            />
        @endif
    </div>

    @if ($block->image)
        <div
            @class([
                match ($style) {
                    'row' => 'flex-1 lg:max-w-[40%]',
                    default => null,
                },
            ])
        >
            <x-capell::media
                :media="$block->image"
                class="h-full w-full object-cover"
            />
        </div>
    @endif
</x-capell-foundation-theme::block.wrapper>
