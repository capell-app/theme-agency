<?php

declare(strict_types=1);

namespace Capell\Payments\Models;

use Capell\Payments\Enums\PaymentIntentStatus;
use Capell\Payments\Enums\PaymentProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property PaymentProvider $provider
 * @property string $provider_payment_intent_id
 * @property int|null $payment_customer_id
 * @property string|null $provider_customer_id
 * @property string|null $provider_session_id
 * @property PaymentIntentStatus $status
 * @property int $amount
 * @property string $currency
 * @property array<string, mixed>|null $metadata
 * @property array<string, mixed>|null $provider_payload
 */
final class PaymentIntent extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'provider',
        'provider_payment_intent_id',
        'payment_customer_id',
        'provider_customer_id',
        'provider_session_id',
        'status',
        'amount',
        'currency',
        'metadata',
        'provider_payload',
    ];

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-payments.tables.payment_intents');

        return is_string($tableName) ? $tableName : 'payment_intents';
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
            'status' => PaymentIntentStatus::class,
            'amount' => 'integer',
            'metadata' => 'array',
            'provider_payload' => 'encrypted:array',
        ];
    }
}
