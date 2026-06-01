<?php

declare(strict_types=1);

namespace Capell\Payments\Models;

use Capell\Payments\Enums\PaymentProvider;
use Capell\Payments\Enums\SubscriptionStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property PaymentProvider $provider
 * @property string $provider_subscription_id
 * @property int|null $payment_customer_id
 * @property string|null $provider_customer_id
 * @property string|null $provider_session_id
 * @property SubscriptionStatus $status
 * @property array<string, mixed>|null $metadata
 * @property array<string, mixed>|null $provider_payload
 * @property CarbonInterface|null $trial_ends_at
 * @property CarbonInterface|null $current_period_starts_at
 * @property CarbonInterface|null $current_period_ends_at
 * @property CarbonInterface|null $cancel_at
 * @property CarbonInterface|null $canceled_at
 */
final class Subscription extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'provider',
        'provider_subscription_id',
        'payment_customer_id',
        'provider_customer_id',
        'provider_session_id',
        'status',
        'metadata',
        'provider_payload',
        'trial_ends_at',
        'current_period_starts_at',
        'current_period_ends_at',
        'cancel_at',
        'canceled_at',
    ];

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-payments.tables.subscriptions');

        return is_string($tableName) ? $tableName : 'payment_subscriptions';
    }

    /**
     * @return BelongsTo<PaymentCustomer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(PaymentCustomer::class, 'payment_customer_id');
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'provider' => PaymentProvider::class,
            'status' => SubscriptionStatus::class,
            'metadata' => 'array',
            'provider_payload' => 'encrypted:array',
            'trial_ends_at' => 'datetime',
            'current_period_starts_at' => 'datetime',
            'current_period_ends_at' => 'datetime',
            'cancel_at' => 'datetime',
            'canceled_at' => 'datetime',
        ];
    }
}
