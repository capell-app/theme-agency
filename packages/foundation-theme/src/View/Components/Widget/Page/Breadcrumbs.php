<?php

declare(strict_types=1);

namespace Capell\FoundationTheme\View\Components\Widget\Page;

use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\FoundationTheme\View\Components\Widget\AbstractWidget;
use Capell\Frontend\Actions\GetPageVariablesAction;
use Capell\Frontend\Facades\Frontend;
use Capell\Frontend\Support\Loader\PageLoader;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Override;
use Stringable;

class Breadcrumbs extends AbstractWidget
{
    protected static string $defaultView = 'capell-foundation-theme::components.widget.page.breadcrumbs';

    #[Override]
    public function render(array $data = []): View|string|Closure
    {
        $page = Frontend::page();
        $site = Frontend::site();
        $language = Frontend::language();

        $preparedAncestors = Frontend::getFrontendData('foundation.page.ancestors');

        if ($preparedAncestors instanceof Collection) {
            $ancestors = $preparedAncestors;
        } elseif (Frontend::getFrontendData('blog.article.render_data') !== null) {
            return '';
        } else {
            $ancestors = $page instanceof Page && $site instanceof Site && $language instanceof Language
                ? PageLoader::getPageAncestors($page, $language, $site)
                : null;
        }
        $pageTranslation = $page instanceof Page && $page->relationLoaded('translation') ? $page->translation : null;

        $currentPageLabel = $pageTranslation !== null
            ? __($pageTranslation->label, $this->translationVariables($page, $site))
            : '';

        $showCurrentPage = $page instanceof Page && ($page->url_params === null || Frontend::params() === []);
        $frontendData = Frontend::getFrontendData();
        $hasPreparedHome = is_array($frontendData) && array_key_exists('foundation.page.home', $frontendData);
        $preparedHome = Frontend::getFrontendData('foundation.page.home');
        $home = $hasPreparedHome
            ? $preparedHome
            : ($site instanceof Site && $language instanceof Language ? $site->getHomePage($language) : null);
        $homeTranslation = $home instanceof Page && $home->relationLoaded('translation') ? $home->translation : null;
        $siteDomain = $site instanceof Site && $site->relationLoaded('siteDomain') ? $site->siteDomain : null;

        return parent::render([
            ...$data,
            'ancestors' => $ancestors,
            'currentPageLabel' => $currentPageLabel,
            'homeLabel' => $homeTranslation?->label,
            'homeUrl' => $siteDomain?->url,
            'page' => $page,
            'showCurrentPage' => $showCurrentPage,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function translationVariables(?Page $page, ?Site $site): array
    {
        return (new Collection(GetPageVariablesAction::run($page, $site)))
            ->filter(fn (mixed $value): bool => is_scalar($value) || $value instanceof Stringable)
            ->map(fn (mixed $value): string => (string) $value)
            ->all();
    }
}
