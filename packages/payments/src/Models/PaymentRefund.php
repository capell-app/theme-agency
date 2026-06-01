<?php

declare(strict_types=1);

namespace Capell\Payments\Models;

use Capell\Payments\Enums\PaymentProvider;
use Capell\Payments\Enums\PaymentRefundStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property PaymentProvider $provider
 * @property string $provider_refund_id
 * @property string|null $provider_payment_intent_id
 * @property string|null $provider_charge_id
 * @property PaymentRefundStatus $status
 * @property int $amount
 * @property string $currency
 * @property string|null $reason
 * @property array<string, mixed>|null $metadata
 * @property array<string, mixed>|null $provider_payload
 */
final class PaymentRefund extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'provider',
        'provider_refund_id',
        'provider_payment_intent_id',
        'provider_charge_id',
        'status',
        'amount',
        'currency',
        'reason',
        'metadata',
        'provider_payload',
    ];

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-payments.tables.refunds');

        return is_string($tableName) ? $tableName : 'payment_refunds';
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'provider' => PaymentProvider::class,
            'status' => PaymentRefundStatus::class,
            'metadata' => 'array',
            'provider_payload' => 'encrypted:array',
        ];
    }
}
