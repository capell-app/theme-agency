<?php

declare(strict_types=1);

namespace Capell\AiCreator\Actions;

use Capell\AiCreator\Data\SiteSpec\CapellSiteSpecData;
use Capell\AiCreator\Data\SiteSpec\CapellSiteSpecPageData;
use Capell\AiCreator\Enums\AiCreatorSessionStatus;
use Capell\AiCreator\Models\AiCreatorSession;
use Capell\Core\Models\Site;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * Build a non-destructive preview site for a session from a candidate spec.
 *
 * Materialises a real Site (so the agent/user can actually view it) but marks
 * it `meta.is_preview = true` and stamps the owning session, keeping it
 * distinct from a published site. The same spec is stored into the session plan
 * columns in the exact shape ApplyAiCreatorSessionAction reads, so a later
 * apply rebuilds the confirmed site from the reviewed preview. Status moves to
 * Previewed. No inference — every value comes from the spec.
 *
 * Public exclusion is enforced in core: Site::scopeExcludingPreview() filters
 * `meta.is_preview` and is applied on the public resolution path
 * (LoadSiteDomainFromUrlAction) and site pickers (Site::getOptions()), so the
 * flag set here is sufficient to keep the preview site off the front end.
 *
 * @method static AiCreatorSession run(AiCreatorSession $session, CapellSiteSpecData $spec)
 */
final class BuildAiCreatorSitePreviewAction
{
    use AsObject;

    public function handle(AiCreatorSession $session, CapellSiteSpecData $spec): AiCreatorSession
    {
        return DB::transaction(function () use ($session, $spec): AiCreatorSession {
            $site = BuildCapellSiteFromSpecAction::run($spec);
            $this->markPreview($site, $session);

            $session->forceFill([
                'site_id' => $site->id,
                'theme_plan' => [
                    'site' => $spec->site->toArray(),
                    'theme' => $spec->theme->toArray(),
                    'language' => $spec->language->toArray(),
                ],
                'page_plan' => array_map(
                    static fn (CapellSiteSpecPageData $page): array => $page->toArray(),
                    $spec->pages,
                ),
                'preview_output' => $this->previewOutput($site, $spec),
                'status' => AiCreatorSessionStatus::Previewed,
            ])->save();

            return $session->refresh();
        });
    }

    private function markPreview(Site $site, AiCreatorSession $session): void
    {
        $meta = $site->meta ?? [];
        $meta['is_preview'] = true;
        $meta['preview_session_id'] = $session->id;

        $site->meta = $meta;
        $site->save();
    }

    /**
     * @return array<string, mixed>
     */
    private function previewOutput(Site $site, CapellSiteSpecData $spec): array
    {
        return [
            'site_id' => $site->id,
            'is_preview' => true,
            'pages' => array_map(
                static fn (CapellSiteSpecPageData $page): array => [
                    'title' => $page->title,
                    'slug' => $page->slug,
                    'url' => $page->resolvedUrl(),
                ],
                $spec->pages,
            ),
        ];
    }
}
