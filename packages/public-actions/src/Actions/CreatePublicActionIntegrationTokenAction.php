<?php

declare(strict_types=1);

namespace Capell\PublicActions\Actions;

use Capell\Admin\Support\SiteScope;
use Capell\Core\Models\Site;
use Capell\PublicActions\Data\PublicActionIntegrationTokenData;
use Capell\PublicActions\Enums\PublicActionIntegrationProvider;
use Capell\PublicActions\Enums\PublicActionIntegrationTokenAbility;
use Capell\PublicActions\Models\PublicActionIntegrationToken;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static PublicActionIntegrationTokenData run(string $name, PublicActionIntegrationProvider $provider = PublicActionIntegrationProvider::Zapier, ?int $siteId = null, list<PublicActionIntegrationTokenAbility> $abilities = [], ?Authenticatable $actor = null)
 */
final class CreatePublicActionIntegrationTokenAction
{
    use AsAction;

    /**
     * @param  list<PublicActionIntegrationTokenAbility>  $abilities
     */
    public function handle(
        string $name,
        PublicActionIntegrationProvider $provider = PublicActionIntegrationProvider::Zapier,
        ?int $siteId = null,
        array $abilities = [],
        ?Authenticatable $actor = null,
    ): PublicActionIntegrationTokenData {
        $this->authorizeSiteScope($actor, $siteId);

        $resolvedAbilities = $abilities === []
            ? PublicActionIntegrationTokenAbility::cases()
            : $abilities;
        $plainTextToken = 'cpa_' . Str::random(64);

        $token = PublicActionIntegrationToken::query()->create([
            'site_id' => $siteId,
            'name' => $name,
            'token_hash' => PublicActionIntegrationToken::hashPlainTextToken($plainTextToken),
            'provider' => $provider,
            'abilities' => array_map(
                static fn (PublicActionIntegrationTokenAbility $ability): string => $ability->value,
                $resolvedAbilities,
            ),
        ]);

        return new PublicActionIntegrationTokenData(
            plainTextToken: $plainTextToken,
            token: $token,
            abilities: array_values($token->abilities ?? []),
        );
    }

    private function authorizeSiteScope(?Authenticatable $actor, ?int $siteId): void
    {
        if (! $actor instanceof Authenticatable) {
            return;
        }

        if ($siteId === null) {
            throw_unless(SiteScope::isGlobalActor($actor), AuthorizationException::class);

            return;
        }

        $site = Site::query()->find($siteId);

        throw_unless(
            $site instanceof Site && SiteScope::actorCanUseSite($actor, $site),
            AuthorizationException::class,
        );
    }
}
