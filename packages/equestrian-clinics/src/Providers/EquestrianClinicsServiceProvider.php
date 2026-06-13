<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Providers;

use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Capell\EquestrianClinics\Models\EquestrianBillingEntry;
use Capell\EquestrianClinics\Models\EquestrianClinicCredit;
use Capell\EquestrianClinics\Models\EquestrianCommercialProduct;
use Capell\EquestrianClinics\Models\EquestrianCommunicationLog;
use Capell\EquestrianClinics\Models\EquestrianCompetitionResult;
use Capell\EquestrianClinics\Models\EquestrianFacilityBooking;
use Capell\EquestrianClinics\Models\EquestrianFacilityResource;
use Capell\EquestrianClinics\Models\EquestrianHorseCareTask;
use Capell\EquestrianClinics\Models\EquestrianHorseHealthRecord;
use Capell\EquestrianClinics\Models\EquestrianHorseProfile;
use Capell\EquestrianClinics\Models\EquestrianHostRequest;
use Capell\EquestrianClinics\Models\EquestrianRiderProfile;
use Capell\EquestrianClinics\Models\EquestrianSlotBooking;
use Capell\EquestrianClinics\Models\EquestrianSlotWaitlistEntry;
use Capell\EquestrianClinics\Models\EquestrianStaffMember;
use Capell\EquestrianClinics\Models\EquestrianTourDay;
use Capell\EquestrianClinics\Models\EquestrianTourDaySlot;
use Capell\EquestrianClinics\Models\EquestrianVenue;
use Capell\EquestrianClinics\Models\EquestrianWaiverSignature;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Spatie\LaravelPackageTools\Package;

final class EquestrianClinicsServiceProvider extends AbstractPackageServiceProvider
{
    public static string $name = 'capell-equestrian-clinics';

    public static string $packageName = 'capell-app/equestrian-clinics';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasConfigFile(self::$name)
            ->hasViews(self::$name)
            ->hasTranslations()
            ->hasMigration('2026_06_13_000001_create_equestrian_clinics_tables');
    }

    public function registeringPackage(): void
    {
        $this->registerMorphMap();
        $this->registerProtectedTables();
    }

    public function bootingPackage(): void
    {
        RateLimiter::for('capell-equestrian-clinics-host-request', static function (Request $request): Limit {
            $email = $request->input('requester_email');
            $normalizedEmail = is_string($email) ? strtolower($email) : '';

            return Limit::perMinute(5)
                ->by(hash('sha256', $normalizedEmail . '|' . ($request->ip() ?? 'unknown')));
        });
    }

    public function packageBooted(): void
    {
        if (! $this->isPackageInstalled()) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
    }

    private function registerMorphMap(): void
    {
        Relation::enforceMorphMap([
            Str::snake(class_basename(EquestrianVenue::class)) => EquestrianVenue::class,
            Str::snake(class_basename(EquestrianTourDay::class)) => EquestrianTourDay::class,
            Str::snake(class_basename(EquestrianTourDaySlot::class)) => EquestrianTourDaySlot::class,
            Str::snake(class_basename(EquestrianStaffMember::class)) => EquestrianStaffMember::class,
            Str::snake(class_basename(EquestrianRiderProfile::class)) => EquestrianRiderProfile::class,
            Str::snake(class_basename(EquestrianHorseProfile::class)) => EquestrianHorseProfile::class,
            Str::snake(class_basename(EquestrianSlotBooking::class)) => EquestrianSlotBooking::class,
            Str::snake(class_basename(EquestrianSlotWaitlistEntry::class)) => EquestrianSlotWaitlistEntry::class,
            Str::snake(class_basename(EquestrianHorseCareTask::class)) => EquestrianHorseCareTask::class,
            Str::snake(class_basename(EquestrianHorseHealthRecord::class)) => EquestrianHorseHealthRecord::class,
            Str::snake(class_basename(EquestrianCompetitionResult::class)) => EquestrianCompetitionResult::class,
            Str::snake(class_basename(EquestrianFacilityResource::class)) => EquestrianFacilityResource::class,
            Str::snake(class_basename(EquestrianFacilityBooking::class)) => EquestrianFacilityBooking::class,
            Str::snake(class_basename(EquestrianWaiverSignature::class)) => EquestrianWaiverSignature::class,
            Str::snake(class_basename(EquestrianClinicCredit::class)) => EquestrianClinicCredit::class,
            Str::snake(class_basename(EquestrianCommercialProduct::class)) => EquestrianCommercialProduct::class,
            Str::snake(class_basename(EquestrianBillingEntry::class)) => EquestrianBillingEntry::class,
            Str::snake(class_basename(EquestrianCommunicationLog::class)) => EquestrianCommunicationLog::class,
            Str::snake(class_basename(EquestrianHostRequest::class)) => EquestrianHostRequest::class,
        ]);
    }

    private function registerProtectedTables(): void
    {
        foreach ([
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
        ] as $table) {
            CapellCore::registerProtectedTable($table);
        }
    }
}
