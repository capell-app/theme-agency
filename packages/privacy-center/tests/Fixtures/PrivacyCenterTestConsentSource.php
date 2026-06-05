<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Tests\Fixtures;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PrivacyCenterTestConsentSource extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    protected $table = 'privacy_center_test_consent_sources';

    /** @var array<string> */
    protected $guarded = [];

    /**
     * @return BelongsTo<PrivacyCenterTestSubject, $this>
     */
    public function visit(): BelongsTo
    {
        return $this->belongsTo(PrivacyCenterTestSubject::class, 'visit_id');
    }
}
