<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\CustomerPortal\Models\PortalAccount;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;
use JsonException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static array{site_id: int, portal_account: PortalAccount} run(string $portalToken)
 */
class ResolvePortalAccessTokenAction
{
    use AsAction;

    /**
     * @return array{site_id: int, portal_account: PortalAccount}
     */
    public function handle(string $portalToken): array
    {
        $encryptedPayload = base64_decode(
            strtr($portalToken, '-_', '+/') . str_repeat('=', (4 - strlen($portalToken) % 4) % 4),
            true,
        );

        if ($encryptedPayload === false) {
            throw $this->invalidToken();
        }

        try {
            $payload = json_decode(Crypt::decryptString($encryptedPayload), true, 512, JSON_THROW_ON_ERROR);
        } catch (DecryptException|JsonException) {
            throw $this->invalidToken();
        }

        if (! is_array($payload)) {
            throw $this->invalidToken();
        }

        $siteId = filter_var($payload['site_id'] ?? null, FILTER_VALIDATE_INT);
        $portalAccountId = filter_var($payload['portal_account_id'] ?? null, FILTER_VALIDATE_INT);

        if ($siteId === false || $portalAccountId === false) {
            throw $this->invalidToken();
        }

        /** @var PortalAccount|null $portalAccount */
        $portalAccount = PortalAccount::query()->find($portalAccountId);

        if (! $portalAccount instanceof PortalAccount || (int) $portalAccount->site_id !== $siteId) {
            throw $this->invalidToken();
        }

        return [
            'portal_account' => $portalAccount,
            'site_id' => $siteId,
        ];
    }

    private function invalidToken(): ValidationException
    {
        return ValidationException::withMessages([
            'token' => __('capell-bookings::validation.portal_token_invalid'),
        ]);
    }
}
