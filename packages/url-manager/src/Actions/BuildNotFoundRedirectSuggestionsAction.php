<?php

declare(strict_types=1);

namespace Capell\UrlManager\Actions;

use Capell\UrlManager\Enums\NotFoundOpportunityStatus;
use Capell\UrlManager\Enums\RedirectRuleStatus;
use Capell\UrlManager\Models\NotFoundOpportunity;
use Capell\UrlManager\Models\RedirectRule;
use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\Actions\Concerns\AsAction;

final class BuildNotFoundRedirectSuggestionsAction
{
    use AsAction;

    public function handle(?int $siteId = null, ?int $languageId = null, int $limit = 50): int
    {
        $updated = 0;

        NotFoundOpportunity::query()
            ->where('status', NotFoundOpportunityStatus::Open->value)
            ->when($siteId !== null, fn (Builder $query): Builder => $query->where('site_id', $siteId))
            ->when($languageId !== null, fn (Builder $query): Builder => $query->where('language_id', $languageId))
            ->orderByDesc('hit_count')
            ->limit($limit)
            ->get()
            ->each(function (NotFoundOpportunity $opportunity) use (&$updated): void {
                $suggestion = $this->bestSuggestionFor($opportunity);

                if ($suggestion === null) {
                    return;
                }

                $opportunity->forceFill(['suggested_target_url' => $suggestion])->save();
                $updated++;
            });

        return $updated;
    }

    private function bestSuggestionFor(NotFoundOpportunity $opportunity): ?string
    {
        $sourceSlug = $this->lastPathSegment($opportunity->source_url);

        if ($sourceSlug === null) {
            return null;
        }

        /** @var RedirectRule|null $redirectRule */
        $redirectRule = RedirectRule::query()
            ->where('status', RedirectRuleStatus::Active->value)
            ->where(function (Builder $query) use ($opportunity): void {
                $query->whereNull('site_id')
                    ->when($opportunity->site_id !== null, fn (Builder $query): Builder => $query->orWhere('site_id', $opportunity->site_id));
            })
            ->where(function (Builder $query) use ($opportunity): void {
                $query->whereNull('language_id')
                    ->when($opportunity->language_id !== null, fn (Builder $query): Builder => $query->orWhere('language_id', $opportunity->language_id));
            })
            ->where('target_url', 'like', '%' . $sourceSlug . '%')
            ->orderByRaw('site_id IS NULL')
            ->orderByRaw('language_id IS NULL')
            ->first();

        return $redirectRule?->target_url;
    }

    private function lastPathSegment(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH);

        if (! is_string($path)) {
            return null;
        }

        $segments = array_values(array_filter(explode('/', trim($path, '/')), fn (string $segment): bool => $segment !== ''));

        return $segments === [] ? null : end($segments);
    }
}
