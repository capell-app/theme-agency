<?php

declare(strict_types=1);

namespace Capell\FrontendAuthoring\Actions;

use Capell\Core\Models\PageUrl;
use Capell\FrontendAuthoring\Data\EditableRegionPayloadData;
use Capell\FrontendAuthoring\Support\EditableRegionRegistry;
use Capell\FrontendAuthoring\Support\EditableRegionSigner;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Lorisleiva\Actions\Concerns\AsObject;

class ValidateEditableRegionPayloadAction
{
    use AsObject;

    public function handle(EditableRegionPayloadData $payload, AuthenticatableContract $user): EditableRegionPayloadData
    {
        abort_unless($payload->pageUrlId > 0 && $payload->siteId > 0 && $payload->languageId > 0 && $payload->regionKey !== '', 403);

        $pageUrl = PageUrl::query()
            ->with(['pageable.translation'])
            ->find($payload->pageUrlId);

        abort_unless($pageUrl instanceof PageUrl, 403);
        abort_unless(
            $payload->siteId === (int) $pageUrl->site_id
            && $payload->languageId === (int) $pageUrl->language_id,
            403,
        );

        $signer = resolve(EditableRegionSigner::class);
        $expectedId = $signer->idFor($payload);
        $region = collect(resolve(EditableRegionRegistry::class)->regionsFor($pageUrl))
            ->first(fn (EditableRegionPayloadData $candidate): bool => hash_equals($expectedId, $signer->idFor($candidate)));

        abort_unless($region instanceof EditableRegionPayloadData, 403);
        abort_unless(AuthorizeEditableRegionAction::run($user, $region, $pageUrl), 403);

        return $region;
    }
}
