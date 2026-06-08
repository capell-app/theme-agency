<?php

declare(strict_types=1);

namespace Capell\UrlManager\Actions;

use Capell\UrlManager\Models\RedirectRule;
use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static list<array<string, bool|int|string|null>> run(?int $siteId = null, ?int $languageId = null)
 */
final class ExportRedirectRulesAction
{
    use AsAction;

    /**
     * @return list<array<string, bool|int|string|null>>
     */
    public function handle(?int $siteId = null, ?int $languageId = null): array
    {
        return array_values(RedirectRule::query()
            ->when($siteId !== null, fn (Builder $query): Builder => $query->where('site_id', $siteId))
            ->when($languageId !== null, fn (Builder $query): Builder => $query->where('language_id', $languageId))
            ->orderBy('source_url')
            ->get()
            ->map(fn (RedirectRule $redirectRule): array => [
                'source_url' => $redirectRule->source_url,
                'target_url' => $redirectRule->target_url,
                'site_id' => $redirectRule->site_id,
                'language_id' => $redirectRule->language_id,
                'status_code' => $redirectRule->status_code,
                'match_type' => $redirectRule->match_type->value,
                'status' => $redirectRule->status->value,
                'priority' => $redirectRule->priority,
                'preserve_query' => $redirectRule->preserve_query,
                'hit_count' => $redirectRule->hit_count,
                'last_hit_at' => $redirectRule->last_hit_at?->toISOString(),
                'notes' => $redirectRule->notes,
            ])
            ->values()
            ->all());
    }
}
