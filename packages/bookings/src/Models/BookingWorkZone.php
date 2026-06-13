<?php

declare(strict_types=1);

namespace Capell\Bookings\Models;

use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property int $id
 * @property bool $active
 * @property list<string> $postal_code_prefixes
 * @property string|null $service_area
 */
class BookingWorkZone extends Model
{
    protected $table = 'booking_work_zones';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'active',
        'meta',
        'name',
        'postal_code_prefixes',
        'service_area',
    ];

    #[Override]
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'meta' => 'json',
            'postal_code_prefixes' => 'json',
        ];
    }
}
