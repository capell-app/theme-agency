<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Actions;

use Capell\SeoSuite\Models\AiDiscoveryPageProfile;
use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static int run(?int $limit = null)
 */
final class RefreshStaleAiDiscoveryMarkdownAction
{
    use AsAction;

    public function handle(?int $limit = null): int
    {
        $query = AiDiscoveryPageProfile::query()
            ->where('include_in_ai_index', true)
            ->where(function (Builder $builder): void {
                $builder
                    ->whereNull('generated_markdown')
                    ->orWhereNull('last_generated_at');
            })
            ->orderBy('id');

        if ($limit !== null) {
            $query->limit(max(0, $limit));
        }

        $refreshed = 0;

        $query->get()->each(function (AiDiscoveryPageProfile $profile) use (&$refreshed): void {
            if (RefreshAiDiscoveryPageProfileMarkdownAction::run($profile)) {
                $refreshed++;
            }
        });

        return $refreshed;
    }
}
