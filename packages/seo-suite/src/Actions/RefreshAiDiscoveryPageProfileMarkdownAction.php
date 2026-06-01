<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Actions;

use Capell\Core\Models\Page;
use Capell\SeoSuite\Data\AiDiscoveryRenderContextData;
use Capell\SeoSuite\Models\AiDiscoveryPageProfile;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static bool run(AiDiscoveryPageProfile $profile)
 */
final class RefreshAiDiscoveryPageProfileMarkdownAction
{
    use AsAction;

    public function handle(AiDiscoveryPageProfile $profile): bool
    {
        $profile->loadMissing(['site', 'language']);

        if ($profile->site === null || $profile->language === null) {
            return false;
        }

        $page = $this->pageForProfile($profile);

        if (! $page instanceof Page) {
            return false;
        }

        $markdown = GeneratePageMarkdownAction::run(
            new AiDiscoveryRenderContextData($profile->site, $profile->language),
            $page,
        );

        if (trim($markdown) === '') {
            return false;
        }

        $profile->forceFill([
            'generated_markdown' => $markdown,
            'markdown_hash' => hash('sha256', $markdown),
            'last_generated_at' => now(),
        ])->save();

        return true;
    }

    private function pageForProfile(AiDiscoveryPageProfile $profile): ?Page
    {
        return $profile->page()
            ->with([
                'translation' => fn (Builder $query): Builder => $query->where('language_id', $profile->language_id),
            ])
            ->first();
    }
}
