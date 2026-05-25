@php
    use Capell\Core\Enums\ContainerWidthEnum;
    use Capell\FoundationTheme\Actions\BuildBlockAssetRenderDataAction;
    use Capell\Frontend\Facades\Frontend;
    use Illuminate\Support\Str;

    $theme = Frontend::theme();
@endphp

@props([
    'color' => $block->getMeta('color', 'dark'),
    'columns' => $container['meta']['override_columns'] ?? $block->getMeta('columns', 4),
    'container',
    'containerKey',
    'containerWidth' => null,
    'large' => false,
    'loop',
    'size' => $block->getMeta('size'),
    'spacing' => $block->getMeta('spacing'),
    'block',
    'block_theme' => $block->getMeta('block_theme'),
])
@if ($block->assets->isNotEmpty() || ! config('capell-layout-builder.block.skip_render_empty', true))
    <x-capell-foundation-theme::block.wrapper
        :class="'capell-asset-media block-media-gallery' . ($containerWidth === ContainerWidthEnum::Full ? ' px-4' : '')"
        :$container
        :$containerKey
        :$containerWidth
        :index="$loop->index"
        :$block
    >
        @if ($block->translation)
            <x-capell::content
                :class="'mb-5' . ($containerWidth === ContainerWidthEnum::Full ? ' container' : '')"
                :compact="true"
                align="center"
                :content="$block->translation->content"
                :content-type="$block->type->content_structure"
                :color="$color"
                :divider="$block->getMeta('content_divider')"
                :muted="in_array($containerKey, $theme->secondary_containers)"
                :title="$block->translation->title"
                :text-align="$block->getMeta('align', 'center')"
                :heading-style="$block->getMeta('heading_style')"
            />
        @endif

        @if ($block->assets->isNotEmpty())
            <div
                @class([
                    'grid grid-cols-2 md:grid-cols-3 2xl:container',
                    'gap-2' => $spacing === 'sm',
                    'gap-4' => $spacing === 'md',
                    'gap-6' => $spacing === 'lg',
                ])
            >
                @foreach ($block->assets as $blockAsset)
                    {{-- format-ignore-start --}}
                @php
                    $assetRenderData = BuildBlockAssetRenderDataAction::run($blockAsset);
                    $image = $assetRenderData->image;
                    if (! $image) {
                        report('Image not found for BlockAsset: ' . $blockAsset->asset_type . ' ' . $blockAsset->id);
                        continue;
                    }
                @endphp
                {{-- format-ignore-end --}}
                    <div
                        @class([
                            'block-media-item group relative h-full cursor-pointer overflow-hidden text-center',
                            'md:col-span-1 md:row-span-2' => ($loop->iteration > 5 && $loop->iteration % 5 === 0) || $loop->iteration === 2,
                        ])
                        role="button"
                        tabindex="0"
                        onkeydown="
                            if (event.key === 'Enter' || event.key === ' ') {
                                event.preventDefault()
                                this.querySelector(
                                    '[data-lightbox], .lightbox',
                                )?.click()
                            }
                        "
                    >
                        @if ($image->media && Str::startsWith($image->media->getMimeType(), 'video/'))
                            <x-capell::media
                                :class="'h-full w-full bg-gray-50 object-cover shadow transition-transform duration-300 group-hover:scale-105 group-focus-within:scale-105' . ($theme->withDarkMode ? ' dark:bg-gray-800' : '')"
                                :height="$large ? 600 : 300"
                                :$loop
                                :media="$image->media"
                                :preview="null"
                                :alt="$assetRenderData->alt"
                                :width="440"
                                media_type="video"
                                fit="crop-center"
                                lightbox="true"
                            />
                        @else
                            <x-capell::image-source
                                :class="'h-full w-full bg-gray-50 object-cover shadow transition-transform duration-300 group-hover:scale-105 group-focus-within:scale-105' . ($theme->withDarkMode ? ' dark:bg-gray-800' : '')"
                                :height="$large ? 600 : 300"
                                :$loop
                                :image="$image"
                                :alt="$assetRenderData->alt"
                                :width="440"
                                lightbox="true"
                            />
                        @endif

                        @if ($assetRenderData->title)
                            <div
                                @class([
                                    'pointer-events-none absolute inset-x-0 bottom-0 flex items-center justify-center
                                break-words bg-gray-600/75 px-2 py-4 font-medium leading-none leading-tight text-white
                                transform translate-y-full opacity-0 transition-all duration-300
                                group-hover:translate-y-0 group-hover:opacity-100
                                group-focus-within:translate-y-0 group-focus-within:opacity-100',
                                    'text-sm' => $size === 'sm',
                                    'text-lg' => $size === 'lg',
                                    'rounded-b' => (bool) $theme->getMeta('rounded_images'),
                                ])
                            >
                                {{ $assetRenderData->title }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </x-capell-foundation-theme::block.wrapper>
@endif
