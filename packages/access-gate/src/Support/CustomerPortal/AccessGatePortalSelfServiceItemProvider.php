<?php

declare(strict_types=1);

namespace Capell\AccessGate\Support\CustomerPortal;

use Capell\AccessGate\Enums\BrowserTokenStatus;
use Capell\AccessGate\Enums\GrantStatus;
use Capell\AccessGate\Enums\RegistrationStatus;
use Capell\AccessGate\Models\BrowserToken;
use Capell\AccessGate\Models\Grant;
use Capell\AccessGate\Models\Registration;
use Capell\CustomerPortal\Contracts\PortalSelfServiceItemProvider;
use Capell\CustomerPortal\Data\PortalSelfServiceItemData;
use Capell\CustomerPortal\Enums\PortalSelfServiceItemType;
use Capell\CustomerPortal\Models\PortalAccount;
use Illuminate\Database\Eloquent\Builder;

final class AccessGatePortalSelfServiceItemProvider implements PortalSelfServiceItemProvider
{
    /**
     * @return iterable<PortalSelfServiceItemData>
     */
    public function selfServiceItemsFor(PortalAccount $portalAccount): iterable
    {
        $email = $this->email($portalAccount);

        if ($email === null) {
            return [];
        }

        return [
            ...$this->grantItems($portalAccount, $email),
            ...$this->browserTokenItems($portalAccount, $email),
            ...$this->registrationItems($portalAccount, $email),
        ];
    }

    /**
     * @return list<PortalSelfServiceItemData>
     */
    private function grantItems(PortalAccount $portalAccount, string $email): array
    {
        return Grant::query()
            ->with(['area', 'registration'])
            ->where('status', GrantStatus::Active->value)
            ->whereRaw('lower(email) = ?', [$email])
            ->whereHas('area', fn (Builder $query): Builder => $this->scopeAreaToPortal($query, $portalAccount))
            ->latest('starts_at')
            ->latest('id')
            ->limit(10)
            ->get()
            ->map(fn (Grant $grant): PortalSelfServiceItemData => new PortalSelfServiceItemData(
                key: 'access-gate.grant.' . $grant->getKey(),
                type: PortalSelfServiceItemType::GatedResource,
                label: $grant->area?->name ?? __('capell-access-gate::public.portal.gated_resource'),
                description: $this->grantDescription($grant),
                url: $grant->registration?->requested_url,
                status: __('capell-access-gate::public.portal.grant_status.' . $grant->status->value),
                occurredAt: $grant->starts_at ?? $grant->updated_at,
                meta: [
                    'grant_id' => (int) $grant->getKey(),
                    'area_id' => (int) $grant->access_area_id,
                    'status' => $grant->status->value,
                ],
            ))
            ->all();
    }

    /**
     * @return list<PortalSelfServiceItemData>
     */
    private function browserTokenItems(PortalAccount $portalAccount, string $email): array
    {
        return BrowserToken::query()
            ->with(['area', 'grant.registration'])
            ->where('status', BrowserTokenStatus::Active->value)
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->whereHas('grant', function (Builder $query) use ($email): void {
                $query
                    ->where('status', GrantStatus::Active->value)
                    ->whereRaw('lower(email) = ?', [$email]);
            })
            ->whereHas('area', fn (Builder $query): Builder => $this->scopeAreaToPortal($query, $portalAccount))
            ->latest('last_used_at')
            ->latest('id')
            ->limit(10)
            ->get()
            ->map(fn (BrowserToken $browserToken): PortalSelfServiceItemData => new PortalSelfServiceItemData(
                key: 'access-gate.browser-token.' . $browserToken->getKey(),
                type: PortalSelfServiceItemType::GatedResource,
                label: $browserToken->area?->name ?? __('capell-access-gate::public.portal.gated_resource'),
                description: $this->browserTokenDescription($browserToken),
                url: $browserToken->grant?->registration?->requested_url,
                status: __('capell-access-gate::public.portal.browser_token_status.' . $browserToken->status->value),
                occurredAt: $browserToken->last_used_at ?? $browserToken->updated_at,
                meta: [
                    'browser_token_id' => (int) $browserToken->getKey(),
                    'grant_id' => (int) $browserToken->grant_id,
                    'area_id' => (int) $browserToken->access_area_id,
                    'status' => $browserToken->status->value,
                ],
            ))
            ->all();
    }

    /**
     * @return list<PortalSelfServiceItemData>
     */
    private function registrationItems(PortalAccount $portalAccount, string $email): array
    {
        return Registration::query()
            ->with('area')
            ->whereRaw('lower(email_normalized) = ?', [$email])
            ->whereIn('status', [
                RegistrationStatus::Pending->value,
                RegistrationStatus::Approved->value,
            ])
            ->whereDoesntHave('grants', function (Builder $query): void {
                $query->where('status', GrantStatus::Active->value);
            })
            ->whereHas('area', fn (Builder $query): Builder => $this->scopeAreaToPortal($query, $portalAccount))
            ->latest('requested_at')
            ->latest('id')
            ->limit(10)
            ->get()
            ->map(fn (Registration $registration): PortalSelfServiceItemData => new PortalSelfServiceItemData(
                key: 'access-gate.registration.' . $registration->getKey(),
                type: PortalSelfServiceItemType::GatedResource,
                label: $registration->area?->name ?? __('capell-access-gate::public.portal.gated_resource'),
                description: __('capell-access-gate::public.portal.registration_description'),
                url: $registration->requested_url,
                status: __('capell-access-gate::public.portal.registration_status.' . $registration->status->value),
                occurredAt: $registration->approved_at ?? $registration->requested_at,
                meta: [
                    'registration_id' => (int) $registration->getKey(),
                    'area_id' => (int) $registration->access_area_id,
                    'status' => $registration->status->value,
                ],
            ))
            ->all();
    }

    private function scopeAreaToPortal(Builder $query, PortalAccount $portalAccount): Builder
    {
        return $query->where(function (Builder $query) use ($portalAccount): void {
            $query
                ->whereNull('site_id')
                ->orWhere('site_id', $portalAccount->site_id);
        });
    }

    private function grantDescription(Grant $grant): string
    {
        if ($grant->expires_at === null) {
            return __('capell-access-gate::public.portal.grant_description');
        }

        return __('capell-access-gate::public.portal.grant_expires_description', [
            'date' => $grant->expires_at->toFormattedDateString(),
        ]);
    }

    private function browserTokenDescription(BrowserToken $browserToken): string
    {
        if ($browserToken->expires_at === null) {
            return __('capell-access-gate::public.portal.browser_token_description');
        }

        return __('capell-access-gate::public.portal.browser_token_expires_description', [
            'date' => $browserToken->expires_at->toFormattedDateString(),
        ]);
    }

    private function email(PortalAccount $portalAccount): ?string
    {
        if (! is_string($portalAccount->email) || trim($portalAccount->email) === '') {
            return null;
        }

        return mb_strtolower(trim($portalAccount->email));
    }
}
