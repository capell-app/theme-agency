<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Http\Controllers;

use Capell\Admin\Support\SiteScope;
use Capell\Core\Models\Site;
use Capell\PublishingStudio\Actions\BuildSchedulerIcalFeedAction;
use Capell\PublishingStudio\Enums\SchedulerIcalFeedScopeEnum;
use Capell\PublishingStudio\Models\SchedulerIcalToken;
use Capell\PublishingStudio\Models\Workspace;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class SchedulerIcalFeedController
{
    public function __invoke(Request $request, string $token): Response
    {
        $feedToken = SchedulerIcalToken::query()
            ->where('token_hash', hash('sha256', $token))
            ->first();

        abort_if(! $feedToken instanceof SchedulerIcalToken || $feedToken->isRevoked() || ! $this->tokenOwnerStillAllowed($feedToken), 404);

        if ($feedToken->last_used_at === null || $feedToken->last_used_at->lessThanOrEqualTo(now()->subHour())) {
            $feedToken->last_used_at = CarbonImmutable::now();
            $feedToken->save();
        }

        $feed = BuildSchedulerIcalFeedAction::run($feedToken);
        $etag = '"' . sha1((string) $feed) . '"';
        $lastModified = $feedToken->updated_at?->toRfc7231String() ?? now()->toRfc7231String();

        if ($request->headers->get('If-None-Match') === $etag) {
            return response('', 304, [
                'ETag' => $etag,
                'Last-Modified' => $lastModified,
                'Cache-Control' => 'private, max-age=300',
            ]);
        }

        return response($feed, 200, [
            'Content-Type' => 'text/calendar; charset=UTF-8',
            'ETag' => $etag,
            'Last-Modified' => $lastModified,
            'Cache-Control' => 'private, max-age=300',
        ]);
    }

    private function tokenOwnerStillAllowed(SchedulerIcalToken $token): bool
    {
        $owner = $token->owner;

        if (! $owner instanceof Authenticatable) {
            return false;
        }

        if (! SiteScope::isGlobalActor($owner) && ! $owner->can('viewAny', Workspace::class)) {
            return false;
        }

        if ($token->scope === SchedulerIcalFeedScopeEnum::All) {
            return SiteScope::isGlobalActor($owner);
        }

        if ($token->scope === SchedulerIcalFeedScopeEnum::Mine) {
            return true;
        }

        $site = $token->site_id !== null ? Site::query()->find($token->site_id) : null;

        return $site instanceof Site && SiteScope::actorCanUseSite($owner, $site);
    }
}
