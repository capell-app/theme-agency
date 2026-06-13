<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;
use Capell\EquestrianClinics\Models\EquestrianClinicCredit;
use Capell\EquestrianClinics\Models\EquestrianFacilityBooking;
use Capell\EquestrianClinics\Models\EquestrianFacilityResource;
use Capell\EquestrianClinics\Models\EquestrianHorseProfile;
use Capell\EquestrianClinics\Models\EquestrianHostRequest;
use Capell\EquestrianClinics\Models\EquestrianRiderProfile;
use Capell\EquestrianClinics\Models\EquestrianTourDay;
use Capell\EquestrianClinics\Models\EquestrianTourDaySlot;
use Capell\EquestrianClinics\Models\EquestrianVenue;
use Capell\EquestrianClinics\Models\EquestrianWaiverSignature;

final class EquestrianClinicsModelsContribution implements ExtensionContribution
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    /**
     * @return list<class-string>
     */
    public function modelClasses(): array
    {
        return [
            EquestrianVenue::class,
            EquestrianTourDay::class,
            EquestrianTourDaySlot::class,
            EquestrianRiderProfile::class,
            EquestrianHorseProfile::class,
            EquestrianFacilityResource::class,
            EquestrianFacilityBooking::class,
            EquestrianWaiverSignature::class,
            EquestrianClinicCredit::class,
            EquestrianHostRequest::class,
        ];
    }
}
