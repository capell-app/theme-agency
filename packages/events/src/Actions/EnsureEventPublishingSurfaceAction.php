<?php

declare(strict_types=1);

namespace Capell\Events\Actions;

use Capell\Core\Actions\SetupPageUrlsAction;
use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static Page run(Site $site, ?Collection $languages = null)
 */
class EnsureEventPublishingSurfaceAction
{
    use AsAction;

    /**
     * @param  Collection<array-key, mixed>  $languages
     */
    public function handle(Site $site, ?Collection $languages = null): Page
    {
        $defaults = resolve(EnsureEventPublishingDefaultsAction::class);
        $defaults->handle();

        $site->unsetRelation('siteDomains');
        $site->loadMissing(['language', 'siteDomains.language']);

        $languages ??= $site->getAllLanguages();

        $page = Page::query()->firstOrNew([
            'site_id' => $site->getKey(),
            'blueprint_id' => $defaults->eventsListingPageType()->getKey(),
            'layout_id' => $defaults->eventsListingLayout()->getKey(),
            'parent_id' => null,
        ]);

        $page->forceFill([
            'name' => __('capell-events::generic.events'),
        ])->save();

        $languages->each(function (Language $language) use ($page): void {
            $page->translations()->firstOrCreate([
                'language_id' => $language->getKey(),
            ], [
                'title' => __('capell-events::generic.events'),
                'content' => '',
                'meta' => [
                    'label' => __('capell-events::generic.events'),
                    'slug' => 'events',
                ],
            ]);
        });

        SetupPageUrlsAction::run($page);

        return $page;
    }
}
