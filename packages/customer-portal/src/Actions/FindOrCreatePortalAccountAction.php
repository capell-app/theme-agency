<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Actions;

use Capell\CustomerPortal\Data\PortalAccountIdentityData;
use Capell\CustomerPortal\Models\PortalAccount;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static PortalAccount run(PortalAccountIdentityData $identityData)
 */
class FindOrCreatePortalAccountAction
{
    use AsAction;

    public function handle(PortalAccountIdentityData $identityData): PortalAccount
    {
        $normalizedEmail = PortalAccount::normalizeEmail($identityData->email);
        $hasOwnerIdentity = $identityData->ownerType !== null && $identityData->ownerType !== '' && $identityData->ownerId !== null;

        if ($normalizedEmail === null && ! $hasOwnerIdentity) {
            throw ValidationException::withMessages([
                'email' => __('capell-customer-portal::validation.account_identity_required'),
            ]);
        }

        return DB::transaction(function () use ($identityData, $normalizedEmail, $hasOwnerIdentity): PortalAccount {
            $portalAccount = $this->findExistingAccount($identityData, $normalizedEmail, $hasOwnerIdentity);

            if (! $portalAccount instanceof PortalAccount) {
                /** @var PortalAccount $portalAccount */
                $portalAccount = PortalAccount::query()->create([
                    'site_id' => $identityData->siteId,
                    'owner_type' => $hasOwnerIdentity ? $identityData->ownerType : null,
                    'owner_id' => $hasOwnerIdentity ? $identityData->ownerId : null,
                    'email' => $normalizedEmail,
                    'display_name' => $identityData->displayName,
                    'profile' => $identityData->profile,
                    'preferences' => $identityData->preferences,
                    'status' => $identityData->status,
                    'last_seen_at' => CarbonImmutable::now(),
                ]);

                return $portalAccount;
            }

            $portalAccount->forceFill([
                'owner_type' => $portalAccount->owner_type ?: ($hasOwnerIdentity ? $identityData->ownerType : null),
                'owner_id' => $portalAccount->owner_id ?: ($hasOwnerIdentity ? $identityData->ownerId : null),
                'email' => $portalAccount->email ?: $normalizedEmail,
                'display_name' => $identityData->displayName ?: $portalAccount->display_name,
                'profile' => array_replace($portalAccount->profile ?? [], $identityData->profile),
                'preferences' => array_replace($portalAccount->preferences ?? [], $identityData->preferences),
                'last_seen_at' => CarbonImmutable::now(),
            ])->save();

            return $portalAccount->refresh();
        });
    }

    private function findExistingAccount(PortalAccountIdentityData $identityData, ?string $normalizedEmail, bool $hasOwnerIdentity): ?PortalAccount
    {
        /** @var PortalAccount|null $portalAccount */
        $portalAccount = PortalAccount::query()
            ->where('site_id', $identityData->siteId)
            ->where(function (Builder $query) use ($identityData, $normalizedEmail, $hasOwnerIdentity): void {
                if ($normalizedEmail !== null) {
                    $query->where('email_hash', PortalAccount::emailHash($normalizedEmail));
                }

                if ($hasOwnerIdentity) {
                    $ownerQueryMethod = $normalizedEmail === null ? 'where' : 'orWhere';

                    $query->{$ownerQueryMethod}(function (Builder $ownerQuery) use ($identityData): void {
                        $ownerQuery
                            ->where('owner_type', $identityData->ownerType)
                            ->where('owner_id', $identityData->ownerId);
                    });
                }
            })
            ->first();

        return $portalAccount;
    }
}
