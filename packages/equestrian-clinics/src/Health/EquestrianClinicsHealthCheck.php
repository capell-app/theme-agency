<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\EquestrianClinics\Actions\AllocateHorseToSlotAction;
use Capell\EquestrianClinics\Actions\BuildFacilityReportAction;
use Capell\EquestrianClinics\Actions\BuildOpenSlotDemandHeatmapAction;
use Capell\EquestrianClinics\Actions\BuildSlotBookingCheckoutSessionDataAction;
use Capell\EquestrianClinics\Actions\BuildStaffCareWorklistAction;
use Capell\EquestrianClinics\Actions\CancelSlotBookingAction;
use Capell\EquestrianClinics\Actions\ClaimWaitlistOfferAction;
use Capell\EquestrianClinics\Actions\CompleteHorseCareTaskAction;
use Capell\EquestrianClinics\Actions\ConfirmSlotBookingPaymentAction;
use Capell\EquestrianClinics\Actions\CreateBillingEntryAction;
use Capell\EquestrianClinics\Actions\CreateCommercialProductAction;
use Capell\EquestrianClinics\Actions\CreateHorseCareTaskAction;
use Capell\EquestrianClinics\Actions\CreateHorseHealthRecordAction;
use Capell\EquestrianClinics\Actions\ExpireSlotBookingHoldsAction;
use Capell\EquestrianClinics\Actions\ExpireWaitlistOffersAction;
use Capell\EquestrianClinics\Actions\GenerateTourDaySlotsAction;
use Capell\EquestrianClinics\Actions\JoinSlotWaitlistAction;
use Capell\EquestrianClinics\Actions\MarkBillingEntryExportedAction;
use Capell\EquestrianClinics\Actions\PromoteWaitlistEntryAction;
use Capell\EquestrianClinics\Actions\QuoteTourDaySlotBookingAction;
use Capell\EquestrianClinics\Actions\RecordCoachBroadcastAction;
use Capell\EquestrianClinics\Actions\RecordCompetitionResultAction;
use Capell\EquestrianClinics\Actions\RecordHostRequestAction;
use Capell\EquestrianClinics\Actions\RequestSlotBookingAction;
use Capell\EquestrianClinics\Actions\ReserveFacilityResourceAction;
use Capell\EquestrianClinics\Actions\ValidateRiderHorseEligibilityAction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

final class EquestrianClinicsHealthCheck implements ChecksExtensionHealth
{
    /** @var list<string> */
    private const array REQUIRED_TABLES = [
        'equestrian_venues',
        'equestrian_tour_days',
        'equestrian_tour_day_slots',
        'equestrian_staff_members',
        'equestrian_rider_profiles',
        'equestrian_horse_profiles',
        'equestrian_slot_bookings',
        'equestrian_slot_waitlist_entries',
        'equestrian_horse_care_tasks',
        'equestrian_horse_health_records',
        'equestrian_competition_results',
        'equestrian_facility_resources',
        'equestrian_facility_bookings',
        'equestrian_waiver_signatures',
        'equestrian_clinic_credits',
        'equestrian_commercial_products',
        'equestrian_billing_entries',
        'equestrian_communication_logs',
        'equestrian_host_requests',
    ];

    /** @var list<class-string> */
    private const array ACTIONS = [
        GenerateTourDaySlotsAction::class,
        QuoteTourDaySlotBookingAction::class,
        BuildSlotBookingCheckoutSessionDataAction::class,
        RequestSlotBookingAction::class,
        ConfirmSlotBookingPaymentAction::class,
        ExpireSlotBookingHoldsAction::class,
        CancelSlotBookingAction::class,
        JoinSlotWaitlistAction::class,
        PromoteWaitlistEntryAction::class,
        ClaimWaitlistOfferAction::class,
        ExpireWaitlistOffersAction::class,
        ValidateRiderHorseEligibilityAction::class,
        AllocateHorseToSlotAction::class,
        CreateHorseCareTaskAction::class,
        CompleteHorseCareTaskAction::class,
        CreateHorseHealthRecordAction::class,
        RecordCompetitionResultAction::class,
        BuildStaffCareWorklistAction::class,
        ReserveFacilityResourceAction::class,
        BuildFacilityReportAction::class,
        CreateCommercialProductAction::class,
        CreateBillingEntryAction::class,
        MarkBillingEntryExportedAction::class,
        RecordCoachBroadcastAction::class,
        RecordHostRequestAction::class,
        BuildOpenSlotDemandHeatmapAction::class,
    ];

    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    /**
     * @return Collection<int, DoctorCheckResultData>
     */
    public static function runDiagnostics(): Collection
    {
        $healthCheck = new self;
        $missingTables = $healthCheck->missingTables();
        $unresolvableActions = $healthCheck->unresolvableActions();

        return collect([
            new DoctorCheckResultData(
                label: 'Equestrian Clinics tables',
                passed: $missingTables === [],
                message: $missingTables === []
                    ? 'Required Equestrian Clinics tables are present.'
                    : sprintf('Missing Equestrian Clinics table(s): %s.', implode(', ', $missingTables)),
                remediation: $missingTables === [] ? null : 'Run the Equestrian Clinics migrations.',
            ),
            new DoctorCheckResultData(
                label: 'Equestrian Clinics actions',
                passed: $unresolvableActions === [],
                message: $unresolvableActions === []
                    ? 'Equestrian Clinics actions resolve from the container.'
                    : sprintf('Unresolvable Equestrian Clinics action(s): %s.', implode(', ', $unresolvableActions)),
                remediation: $unresolvableActions === [] ? null : 'Refresh Composer autoloading and verify package provider registration.',
            ),
        ]);
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
