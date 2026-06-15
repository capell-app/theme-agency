<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Models;

use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property int $id
 * @property int|null $site_id
 * @property string $status
 * @property string $requester_name
 * @property string $requester_email
 * @property string|null $requester_phone
 * @property string|null $venue_name
 * @property string|null $postal_code
 * @property string|null $preferred_region
 * @property string|null $lesson_type
 * @property string|null $skill_tier
 * @property int|null $expected_riders
 * @property string|null $message
 * @property array<string, mixed>|null $payload
 */
final class EquestrianHostRequest extends Model
{
    protected $table = 'equestrian_host_requests';

    protected $guarded = [];

    #[Override]
    protected function casts(): array
    {
        return [
            'expected_riders' => 'integer',
            'payload' => 'json',
        ];
    }
}
