<?php

declare(strict_types=1);

namespace Capell\FoundationTheme\Livewire\Block;

use Capell\Core\Contracts\Pageable;
use Capell\Core\Enums\AssetComponentEnum;
use Capell\Core\Models\Language;
use Capell\Core\Models\Layout;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\Core\Models\Theme;
use Capell\FoundationTheme\Providers\FoundationThemeServiceProvider;
use Capell\FoundationTheme\Support\View\FoundationThemeViewName;
use Capell\Frontend\Actions\Performance\RecordExtensionRenderContributionAction;
use Capell\Frontend\Facades\Frontend;
use Capell\LayoutBuilder\Enums\CapellLayoutCacheKeyEnum;
use Capell\LayoutBuilder\Models\Widget;
use Capell\LayoutBuilder\Support\LayoutBlockData;
use Capell\LayoutBuilder\Support\Livewire\OpaqueBlockReference;
use Capell\LayoutBuilder\Support\Loader\LayoutLoader;
use Closure;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Throwable;

/**
 * @property-read Widget $block
 */
abstract class AbstractBlock extends Component
{
    public string $blockReference = '';

    protected string $containerKey = '';

    protected int $blockIndex = 0;

    protected int $occurrence = 1;

    protected string $blockKey = '';

    protected ?int $layoutId = null;

    protected ?int $languageId = null;

    protected ?int $pageId = null;

    protected ?string $pageType = null;

    protected ?int $siteId = null;

    /** @var array<array-key, mixed> */
    protected array $referenceBlockData = [];

    protected bool $resolvedLayoutLoaded = false;

    protected ?Layout $resolvedLayout = null;

    protected bool $resolvedLanguageLoaded = false;

    protected ?Language $resolvedLanguage = null;

    protected bool $resolvedPageLoaded = false;

    protected ?Pageable $resolvedPage = null;

    protected bool $resolvedSiteLoaded = false;

    protected ?Site $resolvedSite = null;

    protected bool $resolvedThemeLoaded = false;

    protected ?Theme $resolvedTheme = null;

    protected static string $defaultView = 'capell-foundation-theme::components.block.default';

    protected bool $skipRender = false;

    abstract protected function mountBlock(): void;

    public static function getViewName(): string
    {
        return static::$defaultView;
    }

    public static function getBlockByKey(string $blockKey): ?Widget
    {
        $cacheKey = CapellLayoutCacheKeyEnum::BlockByKey->value . $blockKey;

        return self::getCached(
            $cacheKey,
            fn () => Widget::query()->firstWhere('key', $blockKey),
        );
    }

    public function hydrate(): void
    {
        $this->initializeFromBlockReference();
        $this->initializeBlock();
    }

    /**
     * @param  array<array-key, mixed>  $blockData
     */
    public function mount(string $blockReference, array $blockData = []): void
    {
        $this->blockReference = $blockReference;
        $this->initializeFromBlockReference();

        $this->initializeBlock();
    }

    #[Computed]
    public function block(): Widget
    {
        $block = $this->resolveScopedBlock();

        throw_if(! $block instanceof Widget, Exception::class, 'Widget not found');

        return $block;
    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    public function render(array $data = []): View|Closure|string
    {
        if ($this->skipRender) {
            return '<div style="display: none"></div>';
        }

        $this->recordNonCacheableRenderContribution();

        $data = array_merge([
            'container' => $this->containerData(),
            'containerKey' => $this->containerKey,
            'containerWidth' => null,
            'component_item' => $this->getComponentItem(),
            'hasPrimaryHeading' => (bool) $this->frontendData('has_primary_heading'),
            'index' => $this->blockIndex,
            'language' => $this->currentLanguage(),
            'layout' => $this->currentLayout(),
            'loop' => (object) ['index' => $this->blockIndex],
            'pageRecord' => $this->currentPage(),
            'urlParams' => $this->frontendParams(),
            'site' => $this->currentSite(),
            'theme' => $this->currentTheme(),
            'block' => $this->block(),
            'blockData' => $this->blockData(),
        ], $data);

        return resolve(Factory::class)->make($this->getComponent(), $data);
    }

    /**
     * Retrieve (and store if missing) a cached value using the array cache driver.
     */
    protected static function getCached(string $key, callable $resolver, bool $asBool = false): mixed
    {
        $cached = Cache::driver('array')->get($key);
        if ($cached !== null) {
            return $asBool ? (bool) $cached : $cached;
        }

        $result = $resolver();
        Cache::driver('array')->forever($key, $result);

        return $asBool ? (bool) $result : $result;
    }

    protected function getComponent(): string
    {
        return FoundationThemeViewName::canonical(
            $this->block()->getViewFile() ?? static::$defaultView,
        );
    }

    protected function getComponentItem(): string
    {
        return $this->block()->getComponentItem() ?? $this->getDefaultComponentItem();
    }

    protected function getDefaultComponentItem(): string
    {
        return AssetComponentEnum::Card->value;
    }

    protected function initializeBlock(): void
    {
        $this->mountBlock();

        if ($this->skipRender) {
            $this->skipRender('<div style="display: none"></div>');
        }
    }

    private function initializeFromBlockReference(): void
    {
        $reference = OpaqueBlockReference::decode($this->blockReference);

        $containerKey = $reference['container_key'] ?? null;
        $blockKey = $reference['block_key'] ?? null;
        $languageId = $reference['language_id'] ?? null;
        $layoutId = $reference['layout_id'] ?? null;
        $occurrence = $reference['occurrence'] ?? null;
        $pageId = $reference['page_id'] ?? null;
        $pageType = $reference['page_type'] ?? null;
        $siteId = $reference['site_id'] ?? null;
        $blockData = $reference['block_data'] ?? [];
        $blockIndex = $reference['block_index'] ?? null;

        throw_if(! is_string($containerKey) || $containerKey === '' || ! is_string($blockKey) || $blockKey === '', Exception::class, 'Widget reference is invalid');

        $this->containerKey = $containerKey;
        $this->blockKey = $blockKey;
        $this->languageId = is_numeric($languageId) ? (int) $languageId : null;
        $this->layoutId = is_numeric($layoutId) ? (int) $layoutId : null;
        $this->occurrence = is_numeric($occurrence) ? max(1, (int) $occurrence) : 1;
        $this->pageId = is_numeric($pageId) ? (int) $pageId : null;
        $this->pageType = is_string($pageType) && $pageType !== '' ? $pageType : null;
        $this->siteId = is_numeric($siteId) ? (int) $siteId : null;
        $this->referenceBlockData = is_array($blockData) ? $blockData : [];
        $this->blockIndex = is_numeric($blockIndex) ? max(0, (int) $blockIndex) : 0;

        throw_if($this->pageId === null || $this->siteId === null, Exception::class, 'Widget reference is invalid');

        $this->clearResolvedContext();
    }

    private function resolveScopedBlock(): ?Widget
    {
        $layout = $this->currentLayout();
        $language = $this->currentLanguage();

        if (! $layout instanceof Layout || ! $language instanceof Language) {
            return null;
        }

        if (! $this->layoutBelongsToCurrentContext($layout)) {
            return null;
        }

        return resolve(LayoutLoader::class)->getLayoutBlock(
            layout: $layout,
            widgetKey: $this->blockKey,
            language: $language,
            page: $this->currentPage(),
            containerKey: $this->containerKey,
            occurrence: $this->occurrence,
            containerKeys: [$this->containerKey],
        );
    }

    private function currentLayout(): ?Layout
    {
        if ($this->resolvedLayoutLoaded) {
            return $this->resolvedLayout;
        }

        try {
            $layout = Frontend::layout();
        } catch (Throwable) {
            $layout = null;
        }

        if ($layout instanceof Layout) {
            $this->resolvedLayout = $layout;
            $this->resolvedLayoutLoaded = true;

            return $this->resolvedLayout;
        }

        $this->resolvedLayout = $this->layoutId === null ? null : Layout::query()->find($this->layoutId);
        $this->resolvedLayoutLoaded = true;

        return $this->resolvedLayout;
    }

    private function currentLanguage(): ?Language
    {
        if ($this->resolvedLanguageLoaded) {
            return $this->resolvedLanguage;
        }

        try {
            $language = Frontend::language();
        } catch (Throwable) {
            $language = null;
        }

        if ($language instanceof Language) {
            $this->resolvedLanguage = $language;
            $this->resolvedLanguageLoaded = true;

            return $this->resolvedLanguage;
        }

        $this->resolvedLanguage = $this->languageId === null ? null : Language::query()->find($this->languageId);
        $this->resolvedLanguageLoaded = true;

        return $this->resolvedLanguage;
    }

    private function currentPage(): ?Pageable
    {
        if ($this->resolvedPageLoaded) {
            return $this->resolvedPage;
        }

        try {
            $page = Frontend::page();
        } catch (Throwable) {
            $page = null;
        }

        if ($page instanceof Pageable) {
            $this->resolvedPage = $page;
            $this->resolvedPageLoaded = true;

            return $this->resolvedPage;
        }

        if ($this->pageId === null) {
            $this->resolvedPageLoaded = true;

            return null;
        }

        $pageClass = $this->pageType !== null ? Relation::getMorphedModel($this->pageType) : null;
        $pageClass ??= Page::class;

        if (! is_a($pageClass, Pageable::class, true)) {
            $this->resolvedPageLoaded = true;

            return null;
        }

        try {
            $page = $pageClass::query()->with(['translation', 'type', 'image'])->find($this->pageId);
        } catch (Throwable) {
            $page = $pageClass::query()->find($this->pageId);
        }

        $this->resolvedPage = $page instanceof Pageable ? $page : null;
        $this->resolvedPageLoaded = true;

        return $this->resolvedPage;
    }

    /**
     * @return array<array-key, mixed>
     */
    private function containerData(): array
    {
        $layout = $this->currentLayout();
        $container = $layout instanceof Layout ? ($layout->containers[$this->containerKey] ?? []) : [];

        return is_array($container) ? $container : [];
    }

    /**
     * @return array<array-key, mixed>
     */
    private function blockData(): array
    {
        $blockData = [
            'block_key' => $this->blockKey,
            'occurrence' => $this->occurrence,
        ];

        foreach (LayoutBlockData::normalizeMany($this->containerData()['blocks'] ?? []) as $layoutBlockData) {
            if (
                LayoutBlockData::key($layoutBlockData) === $this->blockKey
                && LayoutBlockData::occurrence($layoutBlockData) === $this->occurrence
            ) {
                return array_merge($layoutBlockData, $this->referenceBlockData, $blockData);
            }
        }

        return array_merge($this->referenceBlockData, $blockData);
    }

    private function layoutBelongsToCurrentContext(Layout $layout): bool
    {
        $site = $this->currentSite();
        $page = $this->currentPage();
        $pageSiteId = $page->site_id ?? null;

        if ($site instanceof Site && $this->siteId !== null && (int) $site->getKey() !== $this->siteId) {
            return false;
        }

        if (is_numeric($pageSiteId) && $this->siteId !== null && (int) $pageSiteId !== $this->siteId) {
            return false;
        }

        if ($site instanceof Site && is_numeric($pageSiteId) && (int) $pageSiteId !== (int) $site->getKey()) {
            return false;
        }

        if ($layout->site_id === null) {
            return true;
        }

        if ($site instanceof Site && $layout->site_id !== (int) $site->getKey()) {
            return false;
        }

        if ($this->siteId !== null && $layout->site_id !== $this->siteId) {
            return false;
        }

        if (is_numeric($pageSiteId) && (int) $pageSiteId !== $layout->site_id) {
            return false;
        }

        return true;
    }

    private function currentSite(): ?Site
    {
        if ($this->resolvedSiteLoaded) {
            return $this->resolvedSite;
        }

        try {
            $site = Frontend::site();
        } catch (Throwable) {
            $site = null;
        }

        if ($site instanceof Site) {
            $this->resolvedSite = $site;
            $this->resolvedSiteLoaded = true;

            return $this->resolvedSite;
        }

        $this->resolvedSite = $this->siteId === null ? null : Site::query()->find($this->siteId);
        $this->resolvedSiteLoaded = true;

        return $this->resolvedSite;
    }

    private function currentTheme(): ?Theme
    {
        if ($this->resolvedThemeLoaded) {
            return $this->resolvedTheme;
        }

        try {
            $theme = Frontend::theme();
        } catch (Throwable) {
            $theme = null;
        }

        $this->resolvedTheme = $theme instanceof Theme ? $theme : null;
        $this->resolvedThemeLoaded = true;

        return $this->resolvedTheme;
    }

    /**
     * @return array<array-key, mixed>
     */
    private function frontendParams(): array
    {
        try {
            return Frontend::params();
        } catch (Throwable) {
            return [];
        }
    }

    private function frontendData(string $key): mixed
    {
        try {
            return Frontend::getFrontendData($key);
        } catch (Throwable) {
            return null;
        }
    }

    private function clearResolvedContext(): void
    {
        $this->resolvedLayoutLoaded = false;
        $this->resolvedLayout = null;
        $this->resolvedLanguageLoaded = false;
        $this->resolvedLanguage = null;
        $this->resolvedPageLoaded = false;
        $this->resolvedPage = null;
        $this->resolvedSiteLoaded = false;
        $this->resolvedSite = null;
        $this->resolvedThemeLoaded = false;
        $this->resolvedTheme = null;
    }

    private function recordNonCacheableRenderContribution(): void
    {
        RecordExtensionRenderContributionAction::run(
            packageName: FoundationThemeServiceProvider::$packageName,
            surface: 'frontend',
            contributionType: 'livewire-component',
            contributionClass: static::class,
            elapsedMilliseconds: 0.0,
            frontendRenderBudgetMs: 20,
            cacheTags: ['foundation-theme'],
            cacheable: false,
            sensitiveOutput: false,
            variesBy: ['site', 'locale'],
        );
    }
}
