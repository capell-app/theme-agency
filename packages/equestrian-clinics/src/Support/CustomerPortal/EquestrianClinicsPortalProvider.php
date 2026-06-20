<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Support\CustomerPortal;

use Capell\CustomerPortal\Contracts\PortalDashboardItemProvider;
use Capell\CustomerPortal\Contracts\PortalProfileProvider;
use Capell\CustomerPortal\Contracts\PortalSelfServiceItemProvider;
use Capell\CustomerPortal\Data\PortalDashboardItemData;
use Capell\CustomerPortal\Data\PortalProfileData;
use Capell\CustomerPortal\Data\PortalSelfServiceItemData;
use Capell\CustomerPortal\Enums\PortalDashboardItemPriority;
use Capell\CustomerPortal\Enums\PortalSelfServiceItemType;
use Capell\CustomerPortal\Models\PortalAccount;
use Capell\EquestrianClinics\Enums\EquestrianSlotBookingStatusEnum;
use Capell\EquestrianClinics\Models\EquestrianHorseProfile;
use Capell\EquestrianClinics\Models\EquestrianRiderProfile;
use Capell\EquestrianClinics\Models\EquestrianSlotBooking;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class EquestrianClinicsPortalProvider implements PortalDashboardItemProvider, PortalProfileProvider, PortalSelfServiceItemProvider
{
    public function profileFor(PortalAccount $portalAccount): PortalProfileData
    {
        return new PortalProfileData(
            accountId: (int) $portalAccount->id,
            siteId: (int) $portalAccount->site_id,
            email: null,
            displayName: null,
            status: $portalAccount->status,
            profile: [
                'equestrian' => [
                    'riders' => $this->riders($portalAccount)
                        ->map(fn (EquestrianRiderProfile $riderProfile): array => [
                            'id' => (int) $riderProfile->id,
                            'name' => $riderProfile->name,
                            'skill_tiers' => $riderProfile->skill_tiers ?? [],
                            'cash_approved' => $riderProfile->isCashApproved(),
                        ])
                        ->values()
                        ->all(),
                    'horses' => $this->horses($portalAccount)
                        ->map(fn (EquestrianHorseProfile $horseProfile): array => [
                            'id' => (int) $horseProfile->id,
                            'name' => $horseProfile->name,
                            'fitness_status' => $horseProfile->fitness_status,
                            'vaccinated_until' => $horseProfile->vaccinated_until?->toDateString(),
                            'suitable_skill_tiers' => $horseProfile->suitable_skill_tiers ?? [],
                        ])
                        ->values()
                        ->all(),
                ],
            ],
        );
    }

    public function dashboardItemsFor(PortalAccount $portalAccount): iterable
    {
        $upcomingBookings = $this->upcomingBookings($portalAccount);
        $horseCount = $this->horses($portalAccount)->count();

        if ($upcomingBookings->isEmpty() && $horseCount === 0) {
            return [];
        }

        return [
            new PortalDashboardItemData(
                key: 'equestrian-clinics.profile',
                label: __('capell-equestrian-clinics::package.portal.dashboard_label'),
                description: __('capell-equestrian-clinics::package.portal.dashboard_description', [
                    'bookings' => $upcomingBookings->count(),
                    'horses' => $horseCount,
                ]),
                count: $upcomingBookings->count(),
                priority: PortalDashboardItemPriority::High,
                meta: [
                    'horse_count' => $horseCount,
                    'rider_count' => $this->riders($portalAccount)->count(),
                ],
            ),
        ];
    }

    public function selfServiceItemsFor(PortalAccount $portalAccount): iterable
    {
        return [
            ...$this->riderItems($portalAccount),
            ...$this->horseItems($portalAccount),
            ...$this->bookingItems($portalAccount),
        ];
    }

    /**
     * @return Collection<int, EquestrianRiderProfile>
     */
    private function riders(PortalAccount $portalAccount): Collection
    {
        /** @var Collection<int, EquestrianRiderProfile> $riders */
        $riders = EquestrianRiderProfile::query()
            ->where('portal_account_id', $portalAccount->getKey())
            ->where(function (Builder $query) use ($portalAccount): void {
                $query->whereNull('site_id')
                    ->orWhere('site_id', $portalAccount->site_id);
            })
            ->where('active', true)
            ->orderBy('name')
            ->get();

        return $riders;
    }

    /**
     * @return Collection<int, EquestrianHorseProfile>
     */
    private function horses(PortalAccount $portalAccount): Collection
    {
        /** @var Collection<int, EquestrianHorseProfile> $horses */
        $horses = EquestrianHorseProfile::query()
            ->where('portal_account_id', $portalAccount->getKey())
            ->where(function (Builder $query) use ($portalAccount): void {
                $query->whereNull('site_id')
                    ->orWhere('site_id', $portalAccount->site_id);
            })
            ->where('active', true)
            ->orderBy('name')
            ->get();

        return $horses;
    }

    /**
     * @return Collection<int, EquestrianSlotBooking>
     */
    private function upcomingBookings(PortalAccount $portalAccount): Collection
    {
        $riderIds = $this->riders($portalAccount)->pluck('id')->all();
        $horseIds = $this->horses($portalAccount)->pluck('id')->all();

        if ($riderIds === [] && $horseIds === []) {
            return collect();
        }

        /** @var Collection<int, EquestrianSlotBooking> $bookings */
        $bookings = EquestrianSlotBooking::query()
            ->with(['slot.tourDay', 'riderProfile', 'horseProfile'])
            ->whereIn('status', [
                EquestrianSlotBookingStatusEnum::Held->value,
                EquestrianSlotBookingStatusEnum::Confirmed->value,
            ])
            ->where(function (Builder $query) use ($riderIds, $horseIds): void {
                if ($riderIds !== []) {
                    $query->whereIn('rider_profile_id', $riderIds);
                }

                if ($horseIds !== []) {
                    $method = $riderIds === [] ? 'whereIn' : 'orWhereIn';
                    $query->{$method}('horse_profile_id', $horseIds);
                }
            })
            ->whereHas('slot.tourDay', function (Builder $query): void {
                $query->where('starts_at', '>=', CarbonImmutable::now());
            })
            ->limit(10)
            ->get()
            ->sortBy(fn (EquestrianSlotBooking $booking): string => $booking->slot->starts_at->toIso8601String())
            ->values();

        return $bookings;
    }

    /**
     * @return list<PortalSelfServiceItemData>
     */
    private function riderItems(PortalAccount $portalAccount): array
    {
        return array_values(
            $this->riders($portalAccount)
                ->map(function (EquestrianRiderProfile $riderProfile): PortalSelfServiceItemData {
                    $cashApprovedLabel = __('capell-equestrian-clinics::package.portal.cash_approved');

                    return new PortalSelfServiceItemData(
                        key: 'equestrian-clinics.rider.' . $riderProfile->id,
                        type: PortalSelfServiceItemType::GatedResource,
                        label: $riderProfile->name,
                        description: __('capell-equestrian-clinics::package.portal.rider_profile_description'),
                        status: $riderProfile->isCashApproved() && is_string($cashApprovedLabel)
                            ? $cashApprovedLabel
                            : null,
                        meta: [
                            'rider_profile_id' => (int) $riderProfile->id,
                            'skill_tiers' => $riderProfile->skill_tiers ?? [],
                        ],
                    );
                })
                ->all(),
        );
    }

    /**
     * @return list<PortalSelfServiceItemData>
     */
    private function horseItems(PortalAccount $portalAccount): array
    {
        return array_values(
            $this->horses($portalAccount)
                ->map(fn (EquestrianHorseProfile $horseProfile): PortalSelfServiceItemData => new PortalSelfServiceItemData(
                    key: 'equestrian-clinics.horse.' . $horseProfile->id,
                    type: PortalSelfServiceItemType::GatedResource,
                    label: $horseProfile->name,
                    description: __('capell-equestrian-clinics::package.portal.horse_profile_description'),
                    status: $horseProfile->fitness_status,
                    meta: [
                        'horse_profile_id' => (int) $horseProfile->id,
                        'vaccinated_until' => $horseProfile->vaccinated_until?->toDateString(),
                        'suitable_skill_tiers' => $horseProfile->suitable_skill_tiers ?? [],
                    ],
                ))
                ->all(),
        );
    }

    /**
     * @return list<PortalSelfServiceItemData>
     */
    private function bookingItems(PortalAccount $portalAccount): array
    {
        return array_values(
            $this->upcomingBookings($portalAccount)
                ->map(fn (EquestrianSlotBooking $booking): PortalSelfServiceItemData => new PortalSelfServiceItemData(
                    key: 'equestrian-clinics.booking.' . $booking->id,
                    type: PortalSelfServiceItemType::EventRegistration,
                    label: $booking->slot->title,
                    description: $booking->horseProfile === null
                        ? $booking->slot->tourDay->title
                        : $booking->slot->tourDay->title . ' - ' . $booking->horseProfile->name,
                    status: $booking->status->getLabel(),
                    occurredAt: $booking->slot->starts_at,
                    meta: [
                        'slot_booking_id' => (int) $booking->id,
                        'payment_status' => $booking->payment_status->value,
                        'starts_at' => $booking->slot->starts_at->toIso8601String(),
                    ],
                ))
                ->all(),
        );
    }
}
