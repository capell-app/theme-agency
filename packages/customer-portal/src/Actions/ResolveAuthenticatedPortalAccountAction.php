<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Actions;

use Capell\CustomerPortal\Data\PortalAccountIdentityData;
use Capell\CustomerPortal\Models\PortalAccount;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static PortalAccount run(Authenticatable $user)
 */
class ResolveAuthenticatedPortalAccountAction
{
    use AsAction;

    public function handle(Authenticatable $user): PortalAccount
    {
        return FindOrCreatePortalAccountAction::run(new PortalAccountIdentityData(
            siteId: $this->siteId(),
            email: $this->email($user),
            displayName: $this->displayName($user),
            ownerType: $this->ownerType($user),
            ownerId: $this->ownerId($user),
        ));
    }

    private function siteId(): int
    {
        $configuredSiteId = config('capell-customer-portal.site_id');

        if (is_numeric($configuredSiteId) && (int) $configuredSiteId > 0) {
            return (int) $configuredSiteId;
        }

        if (Schema::hasTable('sites')) {
            $siteId = DB::table('sites')->orderBy('id')->value('id');

            if (is_numeric($siteId) && (int) $siteId > 0) {
                return (int) $siteId;
            }
        }

        throw ValidationException::withMessages([
            'site_id' => __('capell-customer-portal::validation.site_required'),
        ]);
    }

    private function email(Authenticatable $user): ?string
    {
        $email = data_get($user, 'email');

        return is_string($email) && $email !== '' ? $email : null;
    }

    private function displayName(Authenticatable $user): ?string
    {
        $name = data_get($user, 'name');

        return is_string($name) && $name !== '' ? $name : null;
    }

    private function ownerType(Authenticatable $user): ?string
    {
        if ($user instanceof Model) {
            return $user->getMorphClass();
        }

        return $user::class;
    }

    private function ownerId(Authenticatable $user): ?int
    {
        $identifier = $user->getAuthIdentifier();

        return is_numeric($identifier) ? (int) $identifier : null;
    }
}
