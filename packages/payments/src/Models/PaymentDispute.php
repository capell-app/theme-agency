<?php

declare(strict_types=1);

namespace Capell\Payments\Models;

use Capell\Payments\Enums\PaymentDisputeStatus;
use Capell\Payments\Enums\PaymentProvider;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property PaymentProvider $provider
 * @property string $provider_dispute_id
 * @property string|null $provider_payment_intent_id
 * @property string|null $provider_charge_id
 * @property PaymentDisputeStatus $status
 * @property int $amount
 * @property string $currency
 * @property string|null $reason
 * @property bool $is_charge_refundable
 * @property CarbonInterface|null $evidence_due_at
 * @property array<string, mixed>|null $provider_payload
 */
final class PaymentDispute extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'provider',
        'provider_dispute_id',
        'provider_payment_intent_id',
        'provider_charge_id',
        'status',
        'amount',
        'currency',
        'reason',
        'is_charge_refundable',
        'evidence_due_at',
        'provider_payload',
    ];

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-payments.tables.disputes');

        return is_string($tableName) ? $tableName : 'payment_disputes';
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'provider' => PaymentProvider::class,
            'status' => PaymentDisputeStatus::class,
            'amount' => 'integer',
            'is_charge_refundable' => 'boolean',
            'evidence_due_at' => 'datetime',
            'provider_payload' => 'encrypted:array',
        ];
    }
}
