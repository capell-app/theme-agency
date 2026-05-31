<?php

declare(strict_types=1);

namespace Capell\Hero\View\Components\Block;

use Capell\Core\Models\Language;
use Capell\Core\Models\Media;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\Core\Models\Theme;
use Capell\Frontend\Actions\GetPageVariablesAction;
use Capell\Frontend\Actions\RenderHtmlContentAction;
use Capell\Frontend\Facades\Frontend;
use Capell\Hero\Actions\ResolveHeroBackgroundDataAction;
use Capell\Hero\Actions\ResolveHeroMediaDataAction;
use Capell\Hero\Data\HeroAssetSlideData;
use Capell\Hero\Data\HeroBlockRenderData;
use Capell\LayoutBuilder\Actions\GetBlockContainerWidthAction;
use Capell\LayoutBuilder\Actions\HeroBlockHasPrimaryHeadingAction;
use Capell\LayoutBuilder\Models\WidgetAsset;
use Closure;
use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Override;

class Hero extends AbstractBlock
{
    public $containerIndex;

    public ?HeroBlockRenderData $hero = null;

    protected static string $defaultView = 'capell-hero::components.block.hero';

    /**
     * @param  array<array-key, mixed>  $morphRelations
     */
    public static function loadBlockAssets(array &$morphRelations, ?Language $language = null): void
    {
        $morphRelations[Page::class]['related'] = fn (BuilderContract $query): BuilderContract => $query->with(Page::getMorphRelations($language))
            ->withWhereHas('translation', fn (BuilderContract $query): BuilderContract => $query->with('language'));

        foreach (array_keys($morphRelations) as $assetModel) {
            if ($assetModel === Page::class) {
                continue;
            }

            if (! is_a($assetModel, Model::class, true)) {
                continue;
            }

            if (! method_exists($assetModel, 'getMorphRelations')) {
                continue;
            }

            $morphRelations[$assetModel]['related'] = fn (BuilderContract $query): BuilderContract => $query
                ->with($assetModel::getMorphRelations($language))
                ->withWhereHas('translation', fn (BuilderContract $query): BuilderContract => $query->with('language'));
        }
    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    #[Override]
    public function render(array $data = []): View|string|Closure
    {
        $data['hero'] = $this->hero;
        $data['containerKey'] = $this->containerKey;
        $data['blockIndex'] = $this->blockIndex;
        $data['loop'] = $this->loop;

        return parent::render($data);
    }

    protected function mountBlock(): void
    {
        $page = Frontend::page();
        $site = Frontend::site();
        $theme = Frontend::theme();
        $resolvedPage = $page instanceof Page ? $page : null;
        $resolvedSite = $site instanceof Site ? $site : null;
        $resolvedTheme = $theme instanceof Theme ? $theme : null;
        $paginationResults = $this->blockData['results'] ?? Frontend::getFrontendData('pagination_results');

        $this->hero = $this->resolveRenderData($resolvedPage, $resolvedSite, $resolvedTheme, $paginationResults);

        if (! $this->hero->shouldRender) {
            $this->skipRender = true;

            return;
        }

        if ($this->hero->hasPaginationSummary) {
            Frontend::setFrontendData('has_pagination_summary', true);
        }

        if (! $resolvedPage instanceof Page) {
            return;
        }

        HeroBlockHasPrimaryHeadingAction::run($this->block, $resolvedPage);
    }

    private function resolveRenderData(
        ?Page $page,
        ?Site $site,
        ?Theme $theme,
        mixed $paginationResults,
    ): HeroBlockRenderData {
        $pageVariables = $this->pageVariables($page, $site);
        $pageTranslation = $this->loadedRelation($page, 'translation');
        $blockTranslation = $this->loadedRelation($this->block, 'translation');
        $rawPageHero = data_get($pageTranslation, 'meta.hero');
        $rawPageHeroTitle = data_get($pageTranslation, 'meta.hero_title');
        $backgroundColor = $this->stringMeta('background_color');
        $color = $this->stringMeta('color', $this->themeStringMeta($theme, 'color', 'dark'));
        $contentAlign = $this->stringMeta('content_align', 'center');
        $contentWidth = $this->stringMeta('content_width', 'balanced');
        $slides = $this->slides($page, $site, $theme, $color);
        $pageHeroContentHtml = is_string($rawPageHero) && $rawPageHero !== ''
            ? RenderHtmlContentAction::run((string) __($rawPageHero, $pageVariables), array_filter(['page' => $page, 'site' => $site]))
            : null;
        $pageHeroTitle = is_string($rawPageHeroTitle) && $rawPageHeroTitle !== ''
            ? (string) __($rawPageHeroTitle, $pageVariables)
            : null;
        $blockFallbackTitle = $this->translatedString(data_get($blockTranslation, 'title'), $pageVariables);
        $resolvedPaginationResults = $paginationResults instanceof LengthAwarePaginator ? $paginationResults : null;
        $hasPaginationSummary = $resolvedPaginationResults instanceof LengthAwarePaginator && $resolvedPaginationResults->hasPages();
        $hasFallbackSlide = filled($pageHeroTitle) || filled($pageHeroContentHtml) || $resolvedPaginationResults instanceof LengthAwarePaginator;
        $shouldRender = $slides->isNotEmpty()
            || $hasFallbackSlide
            || ! config('capell-layout-builder.block.skip_render_empty', true);

        return new HeroBlockRenderData(
            backgroundColor: $backgroundColor,
            color: $color,
            carousel: $this->carousel(),
            containerClass: GetBlockContainerWidthAction::run($this->block)->getContainerClass(),
            contentAlignmentClass: match ($contentAlign) {
                'left', 'start' => 'items-start text-left',
                default => 'items-center text-center',
            },
            contentWidthClass: match ($contentWidth) {
                'compact' => 'max-w-[42rem]',
                'wide' => 'max-w-[72rem]',
                default => 'max-w-[min(62rem,100%)]',
            },
            height: match ($this->stringMeta('height')) {
                'small' => '24em',
                'medium' => '36em',
                'large' => '60vh',
                default => null,
            },
            mediaPosition: $this->stringMeta('media_position', 'right'),
            mediaSize: $this->stringMeta('media_size', 'default'),
            pageBackgroundImage: $this->loadedRelation($this->block, 'image') instanceof Media ? $this->loadedRelation($this->block, 'image') : null,
            pageBackgroundColor: $this->stringMeta('background_color', $this->themeStringMeta($theme, 'background_color')),
            pageBackgroundSize: $this->stringMeta('background_size', 'cover'),
            pageBackgroundPosition: $this->stringMeta('background_position', 'center'),
            pageBackgroundAttachment: $this->stringMeta('background_attachment', 'scroll'),
            pageBackgroundRepeat: $this->stringMeta('background_repeat', 'no-repeat'),
            pageHeroBackground: $hasFallbackSlide ? ResolveHeroBackgroundDataAction::run($theme, $this->block) : null,
            pageHeroMedia: $hasFallbackSlide ? ResolveHeroMediaDataAction::run($theme, $this->block) : null,
            pageHeroContentHtml: $pageHeroContentHtml,
            pageHeroTitle: $pageHeroTitle,
            blockFallbackTitle: $blockFallbackTitle,
            paginationResults: $resolvedPaginationResults,
            hasPaginationSummary: $hasPaginationSummary,
            slideClass: $this->slideClass($theme),
            slides: $slides,
            total: $slides->count(),
            shouldRender: $shouldRender,
        );
    }

    /**
     * @return array<string, string>
     */
    private function pageVariables(?Page $page, ?Site $site): array
    {
        return collect(GetPageVariablesAction::run($page, $site))
            ->filter(static fn (mixed $value): bool => is_scalar($value) || $value === null)
            ->map(static fn (mixed $value): string => (string) $value)
            ->all();
    }

    /**
     * @return Collection<int, HeroAssetSlideData>
     */
    private function slides(?Page $page, ?Site $site, ?Theme $theme, string $color): Collection
    {
        $assets = $this->loadedRelation($this->block, 'assets');

        if (! $assets instanceof Collection) {
            return collect();
        }

        return $assets
            ->filter(fn (mixed $blockAsset): bool => $blockAsset instanceof WidgetAsset)
            ->map(function (WidgetAsset $blockAsset) use ($page, $site, $theme, $color): HeroAssetSlideData {
                $slide = HeroAssetSlideData::fromBlockAsset($blockAsset, $this->block, $color, $page, $site);

                return new HeroAssetSlideData(
                    asset: $slide->asset,
                    color: $slide->color,
                    actions: $slide->actions,
                    related: $slide->related,
                    heroBackground: ResolveHeroBackgroundDataAction::run($theme, $this->block, $blockAsset),
                    heroMedia: ResolveHeroMediaDataAction::run($theme, $this->block, $blockAsset),
                    backgroundAttachment: $slide->backgroundAttachment ?? $this->stringMeta('background_attachment', 'scroll'),
                    backgroundColor: $slide->backgroundColor ?? $this->stringMeta('background_color', $this->themeStringMeta($theme, 'background_color')),
                    backgroundPosition: $slide->backgroundPosition ?? $this->stringMeta('background_position', 'center'),
                    backgroundRepeat: $slide->backgroundRepeat ?? $this->stringMeta('background_repeat', 'no-repeat'),
                    backgroundSize: $slide->backgroundSize ?? $this->stringMeta('background_size', 'cover'),
                    content: $slide->content,
                    contentHtml: $slide->contentHtml,
                    linkText: $slide->linkText,
                    title: $slide->title,
                    linkedPage: $slide->linkedPage,
                    url: $slide->url,
                    backgroundImage: $slide->backgroundImage,
                    image: $slide->image,
                    images: $slide->images,
                );
            })
            ->values();
    }

    /**
     * @return array{
     *     align: string,
     *     arrows: bool,
     *     autoPlay: bool,
     *     autoDelay: int,
     *     buttonClass: string,
     *     disableOnInteraction: bool,
     *     drag: bool,
     *     effect: string,
     *     fade: bool,
     *     loop: bool,
     *     pagination: bool,
     *     pauseOnHover: bool,
     *     rewind: bool,
     *     speed: int,
     *     touch: bool|null,
     *     wheel: bool
     * }
     */
    private function carousel(): array
    {
        $touch = $this->block->getMeta('carousel_touch');

        return [
            'align' => $this->stringMeta('carousel_align', 'center'),
            'arrows' => $this->boolMeta('carousel_arrows', true),
            'autoPlay' => $this->boolMeta('carousel_auto_play', true),
            'autoDelay' => $this->intMeta('carousel_auto_delay', 8000),
            'buttonClass' => 'hover:bg-primary focus:bg-primary pointer-events-auto bg-white/80 shadow-md transition hover:text-white focus:text-white disabled:pointer-events-none disabled:opacity-50',
            'disableOnInteraction' => $this->boolMeta('carousel_disable_on_interaction', true),
            'drag' => $this->boolMeta('carousel_drag', true),
            'effect' => $this->stringMeta('carousel_effect', 'slide'),
            'fade' => $this->boolMeta('carousel_fade', false),
            'loop' => $this->boolMeta('carousel_loop', true),
            'pagination' => $this->boolMeta('carousel_pagination', true),
            'pauseOnHover' => $this->boolMeta('carousel_pause_on_hover', true),
            'rewind' => $this->boolMeta('carousel_rewind', false),
            'speed' => $this->intMeta('carousel_speed', 300),
            'touch' => is_bool($touch) ? $touch : (is_numeric($touch) ? (bool) $touch : null),
            'wheel' => $this->boolMeta('carousel_wheel', true),
        ];
    }

    private function slideClass(?Theme $theme): string
    {
        if ($this->containerIndex === 0 && $this->themeStringMeta($theme, 'header_position') === 'fixed') {
            return ' pt-20 lg:pt-32';
        }

        return '';
    }

    private function stringMeta(string $key, ?string $fallback = null): ?string
    {
        $value = $this->block->getMeta($key, $fallback);

        return is_string($value) && $value !== '' ? $value : $fallback;
    }

    private function themeStringMeta(?Theme $theme, string $key, ?string $fallback = null): ?string
    {
        $value = $theme?->getMeta($key, $fallback);

        return is_string($value) && $value !== '' ? $value : $fallback;
    }

    private function boolMeta(string $key, bool $fallback): bool
    {
        return filter_var($this->block->getMeta($key, $fallback), FILTER_VALIDATE_BOOL);
    }

    private function intMeta(string $key, int $fallback): int
    {
        $value = $this->block->getMeta($key, $fallback);

        return is_numeric($value) ? (int) $value : $fallback;
    }

    /**
     * @param  array<string, string>  $variables
     */
    private function translatedString(mixed $value, array $variables): ?string
    {
        return is_string($value) && $value !== '' ? (string) __($value, $variables) : null;
    }

    private function loadedRelation(?Model $model, string $relation): mixed
    {
        if (! $model instanceof Model || ! $model->relationLoaded($relation)) {
            return null;
        }

        return $model->getRelation($relation);
    }
}
