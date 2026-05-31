<?php

declare(strict_types=1);

namespace Capell\Newsletter\Support;

use Capell\Admin\Support\SiteScope;
use Capell\Core\Models\Site;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;

final class NewsletterAdminAccess
{
    public static function authorizeSiteId(int $siteId): void
    {
        throw_unless(self::actorCanUseSiteId(auth()->user(), $siteId), AuthorizationException::class);
    }

    public static function actorCanUseSiteId(?Authenticatable $actor, int $siteId): bool
    {
        $site = Site::query()->find($siteId);

        return $site instanceof Site && SiteScope::actorCanUseSite($actor, $site);
    }
}
