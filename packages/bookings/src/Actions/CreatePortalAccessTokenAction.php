<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\CustomerPortal\Models\PortalAccount;
use Illuminate\Support\Facades\Crypt;
use JsonException;
use Lorisleiva\Actions\Concerns\AsAction;
use RuntimeException;

/**
 * @method static string run(PortalAccount $portalAccount)
 */
class CreatePortalAccessTokenAction
{
    use AsAction;

    public function handle(PortalAccount $portalAccount): string
    {
        $portalAccountId = filter_var($portalAccount->getKey(), FILTER_VALIDATE_INT);
        $siteId = filter_var($portalAccount->site_id, FILTER_VALIDATE_INT);

        if ($portalAccountId === false || $siteId === false) {
            throw new RuntimeException('Portal account access tokens require integer site and account identifiers.');
        }

        try {
            $payload = json_encode([
                'portal_account_id' => $portalAccountId,
                'site_id' => $siteId,
            ], JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Unable to encode portal account access token payload.', previous: $exception);
        }

        return rtrim(strtr(base64_encode(Crypt::encryptString($payload)), '+/', '-_'), '=');
    }
}
