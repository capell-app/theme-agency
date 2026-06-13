<?php

declare(strict_types=1);

namespace Capell\Bookings\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property int $id
 * @property string $provider
 * @property string $provider_event_id
 * @property string $type
 * @property string $status
 * @property array<string, mixed>|null $payload
 * @property CarbonImmutable $received_at
 * @property CarbonImmutable|null $processed_at
 */
class BookingWebhookEvent extends Model
{
    protected $table = 'booking_webhook_events';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'payload',
        'processed_at',
        'provider',
        'provider_event_id',
        'received_at',
        'status',
        'type',
    ];

    #[Override]
    protected function casts(): array
    {
        return [
            'payload' => 'json',
            'processed_at' => 'immutable_datetime',
            'received_at' => 'immutable_datetime',
        ];
    }
}
