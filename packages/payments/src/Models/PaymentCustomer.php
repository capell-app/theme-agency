<?php

declare(strict_types=1);

namespace Capell\Payments\Models;

use Capell\Payments\Enums\PaymentProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property PaymentProvider $provider
 * @property string|null $provider_customer_id
 * @property int|null $site_id
 * @property string|null $billable_type
 * @property string|null $billable_id
 * @property string|null $email
 * @property string|null $name
 * @property array<string, mixed>|null $metadata
 * @property array<string, mixed>|null $provider_payload
 */
final class PaymentCustomer extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'provider',
        'provider_customer_id',
        'site_id',
        'billable_type',
        'billable_id',
        'email',
        'name',
        'metadata',
        'provider_payload',
    ];

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-payments.tables.customers');

        return is_string($tableName) ? $tableName : 'payment_customers';
    }

    /**
     * @return HasMany<CheckoutSession, $this>
     */
    public function checkoutSessions(): HasMany
    {
        return $this->hasMany(CheckoutSession::class);
    }

    /**
     * @return HasMany<PaymentIntent, $this>
     */
    public function paymentIntents(): HasMany
    {
        return $this->hasMany(PaymentIntent::class);
    }

    /**
     * @return HasMany<Subscription, $this>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'provider' => PaymentProvider::class,
            'email' => 'encrypted',
            'metadata' => 'array',
            'provider_payload' => 'encrypted:array',
        ];
    }
}
