@php
    use Capell\Core\Contracts\Pageable;
    use Capell\Core\Enums\ContentStructure;
    use Capell\FoundationTheme\Actions\BuildPageContentRenderDataAction;
@endphp

@props([
    'container',
    'containerKey',
    'containerWidth' => null,
    'hasPrimaryHeading' => false,
    'headingTag' => $block->getMeta('heading_tag'),
    'headingSize' => $block->getMeta('heading_size', 'h1'),
    'layout' => null,
    'loop',
    'pageRecord' => null,
    'pageContents' => ['title', 'content'],
    'size' => $block->getMeta('size', 'lg'),
    'site' => null,
    'theme' => null,
    'urlParams' => null,
    'block',
    'blockData',
])
@php
    $nextPage ??= null;
    $previousPage ??= null;
    $configuredPageContents = $blockData['meta']['page_content'] ?? ($block->getMeta('page_content') ?: null);
    $pageContents = is_array($configuredPageContents) ? $configuredPageContents : $pageContents;
    $pageContents = array_values(array_filter((array) $pageContents));
    $pageContents = $pageContents === [] ? ['title', 'content'] : $pageContents;
@endphp

{{-- format-ignore-start --}}
@php
    $page = $pageRecord;
    $secondaryContainers = $theme?->secondary_containers ?? ['sidebar'];
    $pageContentRenderData = BuildPageContentRenderDataAction::run(
        page: $page instanceof Pageable ? $page : null,
        pageContents: $pageContents,
        showPageTitle: ! (empty($blockData['meta']['show_page_title']) && $hasPrimaryHeading),
    );

    $hasContent = $pageContentRenderData->hasContent;
    $hasTitle = $pageContentRenderData->hasTitle;
    $hasNeighborLinks = $previousPage instanceof Pageable || $nextPage instanceof Pageable;

    if (! $headingTag) {
        $headingTag = ($hasPrimaryHeading ? 'h2' : 'h1');
    }

@endphp
{{-- format-ignore-end --}}
@if ($hasContent || $hasTitle || $hasNeighborLinks)
    <x-capell-foundation-theme::block.wrapper
        :$container
        :$containerKey
        :$containerWidth
        :index="$loop->index"
        :$block
        class="capell-page-content block-page-content"
        container-class="capell-standard-page mx-auto max-w-4xl"
        tag="article"
    >
        @if (in_array('content', $pageContents, true))
            @if ($pageContentRenderData->contentStructure === ContentStructure::Blocks)
                @if ($hasTitle)
                    <{{ $headingTag }} class="text-{{ $headingSize }} mb-6">
                        {{ $pageContentRenderData->title }}
                    </{{ $headingTag }}>
                @endif

                <x-capell::widgets
                    :widgets="$pageContentRenderData->content"
                    :$layout
                    :$containerKey
                    :$page
                />
            @else
                <x-capell::content
                    class="capell-standard-page-content prose-headings:text-slate-950 prose-a:text-primary prose-p:leading-8 text-slate-700"
                    :content="$pageContentRenderData->content"
                    :content-type="$pageContentRenderData->contentStructure"
                    :divider="$block->getMeta('content_divider')"
                    :heading-size="$headingSize"
                    :heading-tag="$headingTag"
                    :$layout
                    :muted="in_array($containerKey, $secondaryContainers)"
                    :image="$pageContentRenderData->image"
                    :page-record="$page"
                    :site="$site"
                    :text-align="$block->getMeta('align')"
                    :theme="$theme"
                    :title="$hasTitle ? $pageContentRenderData->title : null"
                    :url-params="$urlParams"
                    width="content"
                />
            @endif
        @endif

        @if (! empty($block->translation?->actions))
            <x-capell::actions
                class="mt-4"
                :actions="$block->translation?->actions"
                button_color="primary"
            />
        @endif

        @if ($hasNeighborLinks)
            <div class="clear-both">
                <nav
                    class="neighbor-links mt-10 flex divide-y divide-gray-100 border-t border-gray-100 pt-6 md:divide-x md:divide-y-0"
                    aria-label="{{ __('capell-foundation-theme::generic.page_navigation') }}"
                >
                    @if ($previousPage)
                        <x-capell::page.neighbor-link
                            :neighbor-page="$previousPage"
                            neighbor="previous"
                        />
                    @endif

                    @if ($nextPage)
                        <x-capell::page.neighbor-link
                            :neighbor-page="$nextPage"
                            neighbor="next"
                            class="ml-auto"
                        />
                    @endif
                </nav>
            </div>
        @endif
    </x-capell-foundation-theme::block.wrapper>
@endif
