<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property int $rider_profile_id
 * @property string $waiver_version
 * @property string $signer_name
 * @property string|null $signer_email
 * @property CarbonImmutable $signed_at
 * @property array<string, mixed>|null $snapshot
 * @property-read EquestrianRiderProfile $riderProfile
 */
final class EquestrianWaiverSignature extends Model
{
    protected $table = 'equestrian_waiver_signatures';

    protected $guarded = [];

    /**
     * @return BelongsTo<EquestrianRiderProfile, $this>
     */
    public function riderProfile(): BelongsTo
    {
        return $this->belongsTo(EquestrianRiderProfile::class, 'rider_profile_id');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'signed_at' => 'immutable_datetime',
            'snapshot' => 'json',
        ];
    }
}
