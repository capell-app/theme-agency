<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Actions;

use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\SeoSuite\Models\AiDiscoveryPageProfile;
use Capell\SeoSuite\Models\AiDiscoverySiteProfile;
use Capell\SiteDiscovery\Actions\DiscoverPublicPagesAction;
use Capell\SiteDiscovery\Data\DiscoverablePageData;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use LogicException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static Collection<int, AiDiscoveryPageProfile> run(Site $site, Language $language, ?Collection<int, DiscoverablePageData> $discoverablePages = null)
 */
final class SyncAiDiscoveryPageProfilesAction
{
    use AsAction;

    /**
     * @param  Collection<int, DiscoverablePageData>|null  $discoverablePages
     * @return Collection<int, AiDiscoveryPageProfile>
     */
    public function handle(Site $site, Language $language, ?Collection $discoverablePages = null): Collection
    {
        $siteProfile = ResolveAiDiscoveryProfileAction::run($site, $language);

        throw_unless($siteProfile instanceof AiDiscoverySiteProfile, LogicException::class, 'Resolving an AI Discovery site profile returned an unexpected page profile.');

        $pages = $discoverablePages ?? DiscoverPublicPagesAction::run($site, $language);
        $pageModels = $this->pageModels($pages);

        if ($pageModels->isEmpty()) {
            return collect();
        }

        $profiles = $this->profilesForPages($pageModels, $site, $language);
        $this->createMissingProfiles($pageModels, $profiles, $site, $language, $siteProfile);
        $profiles = $this->profilesForPages($pageModels, $site, $language);

        return $pageModels
            ->map(fn (Page $page): ?AiDiscoveryPageProfile => $this->applyPageOverrides($profiles->get($page->getKey()), $page, $site, $language))
            ->filter(fn (?AiDiscoveryPageProfile $profile): bool => $profile instanceof AiDiscoveryPageProfile)
            ->values();
    }

    /**
     * @param  Collection<int, DiscoverablePageData>  $pages
     * @return EloquentCollection<int, Page>
     */
    private function pageModels(Collection $pages): EloquentCollection
    {
        return new EloquentCollection(
            $pages
                ->map(fn (DiscoverablePageData $data): ?Page => $data->page)
                ->filter(fn (?Page $page): bool => $page instanceof Page)
                ->values()
                ->all(),
        );
    }

    /**
     * @param  EloquentCollection<int, Page>  $pages
     * @return Collection<int, AiDiscoveryPageProfile>
     */
    private function profilesForPages(EloquentCollection $pages, Site $site, Language $language): Collection
    {
        return AiDiscoveryPageProfile::query()
            ->where('site_id', $site->getKey())
            ->where('language_id', $language->getKey())
            ->whereIn('page_id', $pages->pluck('id')->all())
            ->get()
            ->keyBy('page_id');
    }

    /**
     * @param  EloquentCollection<int, Page>  $pages
     * @param  Collection<int, AiDiscoveryPageProfile>  $profiles
     */
    private function createMissingProfiles(
        EloquentCollection $pages,
        Collection $profiles,
        Site $site,
        Language $language,
        AiDiscoverySiteProfile $siteProfile,
    ): void {
        $now = now();
        $rows = $pages
            ->reject(fn (Page $page): bool => $profiles->has($page->getKey()))
            ->map(fn (Page $page): array => [
                'page_id' => $page->getKey(),
                'site_id' => $site->getKey(),
                'language_id' => $language->getKey(),
                'include_in_ai_index' => $siteProfile->default_include_pages,
                'section' => $siteProfile->default_section,
                'priority' => 500,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->values();

        if ($rows->isEmpty()) {
            return;
        }

        DB::table((new AiDiscoveryPageProfile)->getTable())->insertOrIgnore($rows->all());
    }

    private function applyPageOverrides(?AiDiscoveryPageProfile $profile, Page $page, Site $site, Language $language): ?AiDiscoveryPageProfile
    {
        if (! $profile instanceof AiDiscoveryPageProfile) {
            return null;
        }

        $profile->setRelation('site', $site);
        $profile->setRelation('language', $language);
        $profile->setRelation('page', $page);

        $profile->fill($this->pageMetaOverrides($page));

        if ($profile->isDirty()) {
            $profile->save();
        }

        return $profile;
    }

    /**
     * @return array<string, bool|int|string|null>
     */
    private function pageMetaOverrides(Page $page): array
    {
        $settings = (array) ($page->meta['ai_discovery'] ?? []);
        $overrides = [];

        if (array_key_exists('include_in_ai_index', $settings)) {
            $overrides['include_in_ai_index'] = (bool) $settings['include_in_ai_index'];
        }

        foreach (['summary', 'section', 'exclude_reason', 'markdown_override'] as $key) {
            if (array_key_exists($key, $settings)) {
                $value = is_scalar($settings[$key]) ? trim((string) $settings[$key]) : null;
                $overrides[$key] = $value !== '' ? $value : null;
            }
        }

        if (array_key_exists('priority', $settings) && is_numeric($settings['priority'])) {
            $overrides['priority'] = max(0, min(1000, (int) $settings['priority']));
        }

        return $overrides;
    }
}
