<?php

declare(strict_types=1);

namespace Capell\Payments\Models;

use Capell\Payments\Enums\CheckoutMode;
use Capell\Payments\Enums\CheckoutSessionStatus;
use Capell\Payments\Enums\PaymentProvider;
use Capell\Payments\Enums\PaymentPurpose;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property PaymentProvider $provider
 * @property string $provider_session_id
 * @property int|null $payment_customer_id
 * @property int|null $site_id
 * @property CheckoutMode $mode
 * @property PaymentPurpose $purpose
 * @property CheckoutSessionStatus $status
 * @property string|null $url
 * @property string|null $currency
 * @property int|null $amount_subtotal
 * @property int|null $amount_total
 * @property string|null $provider_customer_id
 * @property string|null $provider_payment_intent_id
 * @property string|null $provider_subscription_id
 * @property string|null $billable_type
 * @property string|null $billable_id
 * @property string|null $payable_type
 * @property string|null $payable_id
 * @property string|null $source_type
 * @property string|null $source_id
 * @property string|null $reference_id
 * @property array<string, mixed>|null $metadata
 * @property array<string, mixed>|null $provider_payload
 * @property CarbonInterface|null $expires_at
 * @property CarbonInterface|null $completed_at
 */
final class CheckoutSession extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'provider',
        'provider_session_id',
        'payment_customer_id',
        'site_id',
        'mode',
        'purpose',
        'status',
        'url',
        'currency',
        'amount_subtotal',
        'amount_total',
        'provider_customer_id',
        'provider_payment_intent_id',
        'provider_subscription_id',
        'billable_type',
        'billable_id',
        'payable_type',
        'payable_id',
        'source_type',
        'source_id',
        'reference_id',
        'metadata',
        'provider_payload',
        'expires_at',
        'completed_at',
    ];

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-payments.tables.checkout_sessions');

        return is_string($tableName) ? $tableName : 'payment_checkout_sessions';
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
            'mode' => CheckoutMode::class,
            'purpose' => PaymentPurpose::class,
            'status' => CheckoutSessionStatus::class,
            'metadata' => 'array',
            'provider_payload' => 'encrypted:array',
            'expires_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
}
