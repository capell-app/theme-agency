<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property int $id
 * @property int|null $site_id
 * @property int|null $portal_account_id
 * @property string $name
 * @property string|null $email
 * @property CarbonImmutable|null $date_of_birth
 * @property string|null $emergency_contact_name
 * @property string|null $emergency_contact_phone
 * @property string|null $medical_disclosures
 * @property array<int, string>|null $skill_tiers
 * @property string|null $guardian_name
 * @property string|null $guardian_email
 * @property CarbonImmutable|null $cash_approved_at
 * @property bool $active
 * @property-read Collection<int, EquestrianWaiverSignature> $waiverSignatures
 * @property-read Collection<int, EquestrianSlotBooking> $slotBookings
 * @property-read Collection<int, EquestrianSlotWaitlistEntry> $waitlistEntries
 * @property-read Collection<int, EquestrianCompetitionResult> $competitionResults
 */
final class EquestrianRiderProfile extends Model
{
    protected $table = 'equestrian_rider_profiles';

    protected $guarded = [];

    /**
     * @return HasMany<EquestrianWaiverSignature, $this>
     */
    public function waiverSignatures(): HasMany
    {
        return $this->hasMany(EquestrianWaiverSignature::class, 'rider_profile_id');
    }

    /**
     * @return HasMany<EquestrianSlotBooking, $this>
     */
    public function slotBookings(): HasMany
    {
        return $this->hasMany(EquestrianSlotBooking::class, 'rider_profile_id');
    }

    /**
     * @return HasMany<EquestrianSlotWaitlistEntry, $this>
     */
    public function waitlistEntries(): HasMany
    {
        return $this->hasMany(EquestrianSlotWaitlistEntry::class, 'rider_profile_id');
    }

    /**
     * @return HasMany<EquestrianCompetitionResult, $this>
     */
    public function competitionResults(): HasMany
    {
        return $this->hasMany(EquestrianCompetitionResult::class, 'rider_profile_id');
    }

    public function hasSkillTier(string $skillTier): bool
    {
        return in_array($skillTier, $this->skill_tiers ?? [], true);
    }

    public function isCashApproved(): bool
    {
        return $this->cash_approved_at instanceof CarbonImmutable;
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'cash_approved_at' => 'immutable_datetime',
            'date_of_birth' => 'immutable_date',
            'skill_tiers' => 'json',
        ];
    }
}
