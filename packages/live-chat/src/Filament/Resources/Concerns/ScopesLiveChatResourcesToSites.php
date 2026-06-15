<?php

declare(strict_types=1);

namespace Capell\LiveChat\Filament\Resources\Concerns;

use Capell\Admin\Support\SiteScope;
use Capell\Core\Models\Site;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema as DatabaseSchema;
use LogicException;

trait ScopesLiveChatResourcesToSites
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function prepareFormDataForPersistence(array $data): array
    {
        if (! self::canScopeLiveChatToSites()) {
            return $data;
        }

        $siteId = self::siteIdFromLiveChatData($data['site_id'] ?? null);
        $actor = auth()->user();

        if ($siteId === null) {
            throw_unless(
                $actor instanceof Authenticatable && SiteScope::isGlobalActor($actor),
                AuthorizationException::class,
            );

            $data['site_id'] = null;

            return $data;
        }

        $site = Site::query()->find($siteId);

        throw_unless(
            $site instanceof Site && SiteScope::actorCanUseSite($actor, $site),
            AuthorizationException::class,
        );

        $data['site_id'] = $siteId;

        return $data;
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    protected static function scopeLiveChatQueryToActorSites(Builder $query): Builder
    {
        return self::canScopeLiveChatToSites()
            ? SiteScope::applyForCurrentActor($query, denyWhenMissingActor: true)
            : $query;
    }

    /**
     * @return array<int, string>
     */
    protected static function liveChatSiteOptions(): array
    {
        if (! self::canScopeLiveChatToSites()) {
            return [];
        }

        return SiteScope::applyForCurrentActor(Site::query()->select(['name', 'id']), 'id', true)
            ->ordered()
            ->get()
            ->mapWithKeys(static fn (Site $site): array => [self::siteOptionKey($site) => self::siteOptionLabel($site)])
            ->all();
    }

    protected static function canScopeLiveChatToSites(): bool
    {
        return DatabaseSchema::hasTable('sites');
    }

    private static function siteIdFromLiveChatData(mixed $siteId): ?int
    {
        if ($siteId === null || $siteId === '') {
            return null;
        }

        throw_unless(is_int($siteId) || is_numeric($siteId), AuthorizationException::class);

        return (int) $siteId;
    }

    private static function siteOptionKey(Site $site): int
    {
        $siteId = $site->getAttribute('id');

        if (is_int($siteId)) {
            return $siteId;
        }

        if (is_string($siteId) && ctype_digit($siteId)) {
            return (int) $siteId;
        }

        throw new LogicException('Site options require an integer site id.');
    }

    private static function siteOptionLabel(Site $site): string
    {
        $siteName = $site->getAttribute('name');

        return is_string($siteName) ? $siteName : '';
    }
}
