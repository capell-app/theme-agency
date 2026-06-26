<?php

declare(strict_types=1);

namespace Capell\AiCreator\Actions;

use Capell\AiCreator\Data\SiteSpec\CapellSiteSpecData;
use Capell\AiCreator\Enums\AiCreatorSessionStatus;
use Capell\AiCreator\Models\AiCreatorSession;
use Capell\Core\Models\Site;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static AiCreatorSession run(AiCreatorSession $session)
 */
final class ApplyAiCreatorSessionAction
{
    use AsAction;

    public function handle(AiCreatorSession $session): AiCreatorSession
    {
        return DB::transaction(function () use ($session): AiCreatorSession {
            $attributes = [
                'status' => AiCreatorSessionStatus::Applied,
                'applied_at' => now(),
            ];

            // Build the site only when the session carries a spec in its plan
            // columns. Without one there is nothing to materialise, so the
            // apply simply records the terminal status (inference-free either
            // way — every value comes from the stored spec).
            if ($this->hasSpec($session)) {
                $site = BuildCapellSiteFromSpecAction::run($this->specFromSession($session));
                $this->promoteFromPreview($site);
                $attributes['site_id'] = $site->id;
            }

            $session->forceFill($attributes)->save();

            return $session->refresh();
        });
    }

    /**
     * Applying promotes a previewed site into a published one: the builder
     * reuses the same Site row (create-or-update by name), so clear the preview
     * flags rather than leaving a stray preview. A no-op when applying without a
     * prior preview.
     */
    private function promoteFromPreview(Site $site): void
    {
        $meta = $site->meta ?? [];

        if (! array_key_exists('is_preview', $meta) && ! array_key_exists('preview_session_id', $meta)) {
            return;
        }

        unset($meta['is_preview'], $meta['preview_session_id']);
        $site->meta = $meta;
        $site->save();
    }

    private function hasSpec(AiCreatorSession $session): bool
    {
        $themePlan = $session->theme_plan ?? [];

        return isset($themePlan['site'], $themePlan['theme']) && ($session->page_plan ?? []) !== [];
    }

    /**
     * Reassemble the site spec from the session plan columns. `theme_plan`
     * holds site + theme + language; `page_plan` holds the ordered pages[].
     */
    private function specFromSession(AiCreatorSession $session): CapellSiteSpecData
    {
        $themePlan = $session->theme_plan ?? [];

        $payload = [
            'site' => $themePlan['site'] ?? [],
            'theme' => $themePlan['theme'] ?? [],
            'pages' => $session->page_plan ?? [],
        ];

        if (isset($themePlan['language'])) {
            $payload['language'] = $themePlan['language'];
        }

        return CapellSiteSpecData::from($payload);
    }
}
