<?php

declare(strict_types=1);

namespace Capell\Payments\Models;

use Capell\Payments\Enums\PaymentProvider;
use Capell\Payments\Enums\PaymentWebhookEventStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property PaymentProvider $provider
 * @property string $provider_event_id
 * @property string $event_type
 * @property bool $livemode
 * @property string|null $api_version
 * @property PaymentWebhookEventStatus $status
 * @property string|null $signature_header_hash
 * @property array<string, mixed> $payload
 * @property CarbonInterface|null $received_at
 * @property CarbonInterface|null $processed_at
 * @property CarbonInterface|null $failed_at
 * @property string|null $error
 */
final class PaymentWebhookEvent extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'provider',
        'provider_event_id',
        'event_type',
        'livemode',
        'api_version',
        'status',
        'signature_header_hash',
        'payload',
        'received_at',
        'processed_at',
        'failed_at',
        'error',
    ];

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-payments.tables.webhook_events');

        return is_string($tableName) ? $tableName : 'payment_webhook_events';
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'provider' => PaymentProvider::class,
            'livemode' => 'boolean',
            'status' => PaymentWebhookEventStatus::class,
            'payload' => 'encrypted:array',
            'received_at' => 'datetime',
            'processed_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }
}
