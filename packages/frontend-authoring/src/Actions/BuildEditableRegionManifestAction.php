<?php

declare(strict_types=1);

namespace Capell\FrontendAuthoring\Actions;

use Capell\Core\Models\PageUrl;
use Capell\FrontendAuthoring\Data\EditableRegionData;
use Capell\FrontendAuthoring\Support\EditableRegionRegistry;
use Capell\FrontendAuthoring\Support\EditableRegionSigner;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Lorisleiva\Actions\Concerns\AsObject;

class BuildEditableRegionManifestAction
{
    use AsObject;

    /**
     * @return array<string, array<string, mixed>>
     */
    public function handle(PageUrl $pageUrl, ?AuthenticatableContract $user = null): array
    {
        $registry = resolve(EditableRegionRegistry::class);
        $signer = resolve(EditableRegionSigner::class);
        $manifest = [];

        foreach ($registry->regionsFor($pageUrl) as $payload) {
            if ($user instanceof AuthenticatableContract && ! AuthorizeEditableRegionAction::run($user, $payload, $pageUrl)) {
                continue;
            }

            $region = new EditableRegionData(
                id: $signer->idFor($payload),
                label: $payload->label,
                type: $payload->type,
                selector: $payload->selector,
                editUrl: $signer->signedEditUrl($payload),
                surface: $payload->surface,
                target: $payload->target,
                description: $payload->description,
                context: $payload->context,
                permissions: $payload->permissions,
            );

            $manifest[$region->id] = $region->toArray();
        }

        return $manifest;
    }
}
