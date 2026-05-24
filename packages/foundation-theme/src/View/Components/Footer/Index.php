<?php

declare(strict_types=1);

namespace Capell\FoundationTheme\View\Components\Footer;

use Capell\Core\Enums\BlueprintGroupEnum;
use Capell\Core\Enums\PageOrderEnum;
use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\FoundationTheme\Support\NavigationAvailability;
use Capell\Frontend\Actions\GetLayoutContainerWidthAction;
use Capell\Frontend\Enums\RenderHookLocation;
use Capell\Frontend\Facades\Frontend;
use Capell\Frontend\Support\Loader\PageLoader;
use Capell\Frontend\Support\Loader\SiteLoader;
use Capell\Frontend\Support\Render\RenderHookRegistry;
use Capell\Navigation\Actions\BuildNavigationRenderModelAction;
use Capell\Navigation\Data\NavigationRenderContextData;
use Capell\Navigation\Enums\NavigationHandle;
use Capell\Navigation\Models\Navigation;
use Capell\Navigation\Support\Loader\NavigationLoader;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

final class Index extends Component
{
    public mixed $contactPage;

    public mixed $containerWidth;

    public mixed $footerCopy;

    public ?string $footerDividerColor;

    public bool $hasFooterMenu;

    public bool $hasFooterPrimaryContent;

    public bool $hasLatestFooterPages;

    public string $footerRenderHooks;

    public mixed $footerSpacing;

    /**
     * @var Collection<array-key, mixed>
     */
    public Collection $latestFooterPages;

    public mixed $site;

    public mixed $siteLanguages;

    public mixed $subFooterMenuItems;

    public mixed $theme;

    public mixed $footerMenuItems;

    public function __construct(
        public string $headingClass = 'font-heading text-sm font-semibold uppercase leading-tight tracking-[0.08em] text-[var(--color-footer-heading)]',
    ) {
        $language = Frontend::language();
        $this->site = Frontend::site();
        $page = Frontend::page();
        $this->theme = Frontend::theme();
        $navigationAvailable = NavigationAvailability::check();

        $this->footerMenuItems = $navigationAvailable
            ? $this->menuItems(NavigationHandle::Footer->value, $language)
            : null;
        $this->subFooterMenuItems = $navigationAvailable
            ? $this->menuItems(NavigationHandle::SubFooter->value, $language)
            : null;
        $this->contactPage = Page::getFirstPageByTypeForSite('contact', $this->site, $language);
        $this->siteLanguages = SiteLoader::pageLanguages($this->site, $language, $page);
        $this->footerCopy = $this->site->translation->getMeta('footer_copy');
        $this->containerWidth = GetLayoutContainerWidthAction::run();
        $this->footerSpacing = $this->theme->getMeta('footer_spacing', 'compact');
        $this->footerDividerColor = (bool) $this->theme->getMeta('footer_divider') ? $this->theme->getMeta('footer_border_color') : null;
        $this->footerRenderHooks = resolve(RenderHookRegistry::class)->renderAll(
            RenderHookLocation::Footer,
            item: ['headingClass' => $this->headingClass],
            target: 'footer.index',
        );
        $this->latestFooterPages = PageLoader::getPages(
            language: $language,
            site: $this->site,
            limit: 4,
            ordering: PageOrderEnum::Latest,
            pageGroup: BlueprintGroupEnum::Default,
        );
        $this->hasFooterMenu = $this->footerMenuItems?->isNotEmpty() === true;
        $this->hasLatestFooterPages = ! $this->hasFooterMenu && $this->latestFooterPages->isNotEmpty();
        $hasFooterRenderHooks = trim($this->footerRenderHooks) !== '';
        $this->hasFooterPrimaryContent = $this->hasFooterMenu || $this->hasLatestFooterPages || $hasFooterRenderHooks;
    }

    public function render(): View
    {
        return view('capell::components.footer.index');
    }

    private function menuItems(string $key, ?Language $language): mixed
    {
        $menu = NavigationLoader::getNavigation($key, $this->site, $language);

        if (! $menu instanceof Navigation || ! $language instanceof Language) {
            return null;
        }

        return BuildNavigationRenderModelAction::run(new NavigationRenderContextData(
            navigation: $menu,
            page: Frontend::page(),
            site: $this->site,
            language: $language,
            siteDomain: $this->site->siteDomain,
        ))->items;
    }
}
