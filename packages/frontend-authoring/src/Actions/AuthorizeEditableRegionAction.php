<?php

declare(strict_types=1);

namespace Capell\FrontendAuthoring\Actions;

use Capell\Core\Models\PageUrl;
use Capell\FrontendAuthoring\Data\EditableRegionPayloadData;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Lorisleiva\Actions\Concerns\AsObject;

class AuthorizeEditableRegionAction
{
    use AsObject;

    public function handle(AuthenticatableContract $user, EditableRegionPayloadData $payload, ?PageUrl $pageUrl = null): bool
    {
        if ($pageUrl instanceof PageUrl && ! $this->payloadMatchesPageUrl($payload, $pageUrl)) {
            return false;
        }

        foreach ($payload->permissions as $permission) {
            if (! Gate::forUser($user)->allows($permission, [$pageUrl, $payload])) {
                return false;
            }
        }

        if (Gate::forUser($user)->allows('frontend-authoring.edit', [$pageUrl, $payload])) {
            return true;
        }

        $pageable = $pageUrl?->pageable;

        return $pageable instanceof Model
            && (
                Gate::forUser($user)->allows('editContent', $pageable)
                || Gate::forUser($user)->allows('update', $pageable)
            );
    }

    private function payloadMatchesPageUrl(EditableRegionPayloadData $payload, PageUrl $pageUrl): bool
    {
        return $payload->pageUrlId === (int) $pageUrl->getKey()
            && $payload->siteId === (int) $pageUrl->site_id
            && $payload->languageId === (int) $pageUrl->language_id
            && $payload->regionKey !== '';
    }
}
