<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\EquestrianClinics\Actions\AllocateHorseToSlotAction;
use Capell\EquestrianClinics\Actions\BuildFacilityReportAction;
use Capell\EquestrianClinics\Actions\BuildOpenSlotDemandHeatmapAction;
use Capell\EquestrianClinics\Actions\GenerateTourDaySlotsAction;
use Capell\EquestrianClinics\Actions\QuoteTourDaySlotBookingAction;
use Capell\EquestrianClinics\Actions\RecordHostRequestAction;
use Capell\EquestrianClinics\Actions\ReserveFacilityResourceAction;
use Capell\EquestrianClinics\Actions\ValidateRiderHorseEligibilityAction;
use Illuminate\Support\Facades\Schema;

final class EquestrianClinicsHealthCheck implements ChecksExtensionHealth
{
    /** @var list<string> */
    private const array REQUIRED_TABLES = [
        'equestrian_venues',
        'equestrian_tour_days',
        'equestrian_tour_day_slots',
        'equestrian_rider_profiles',
        'equestrian_horse_profiles',
        'equestrian_facility_resources',
        'equestrian_facility_bookings',
        'equestrian_waiver_signatures',
        'equestrian_clinic_credits',
        'equestrian_host_requests',
    ];

    /** @var list<class-string> */
    private const array ACTIONS = [
        GenerateTourDaySlotsAction::class,
        QuoteTourDaySlotBookingAction::class,
        ValidateRiderHorseEligibilityAction::class,
        AllocateHorseToSlotAction::class,
        ReserveFacilityResourceAction::class,
        BuildFacilityReportAction::class,
        RecordHostRequestAction::class,
        BuildOpenSlotDemandHeatmapAction::class,
    ];

    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    public function passes(): bool
    {
        return $this->missingTables() === []
            && $this->unresolvableActions() === [];
    }

    /**
     * @return list<string>
     */
    public function missingTables(): array
    {
        return array_values(array_filter(
            self::REQUIRED_TABLES,
            static fn (string $table): bool => ! Schema::hasTable($table),
        ));
    }

    /**
     * @return list<class-string>
     */
    public function unresolvableActions(): array
    {
        return array_values(array_filter(
            self::ACTIONS,
            static fn (string $actionClass): bool => ! app()->make($actionClass) instanceof $actionClass,
        ));
    }
}
