<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Override;

/**
 * @property string|null $email
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $phone
 * @property int $connection_id
 * @property string $shopify_gid
 * @property CarbonImmutable|null $synced_at
 * @property-read ShopifyConnection|null $connection
 */
final class ShopifyCustomer extends Model
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    protected $table = 'shopify_customers';

    protected $guarded = [];

    public static function emailHash(?string $email): ?string
    {
        if (! is_string($email) || trim($email) === '') {
            return null;
        }

        return hash('sha256', Str::lower(trim($email)));
    }

    /**
     * @return BelongsTo<ShopifyConnection, $this>
     */
    public function connection(): BelongsTo
    {
        return $this->belongsTo(ShopifyConnection::class, 'connection_id');
    }

    #[Override]
    protected static function booted(): void
    {
        self::saving(function (ShopifyCustomer $customer): void {
            $customer->email_hash = self::emailHash($customer->email);
        });
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'email' => 'encrypted',
            'first_name' => 'encrypted',
            'last_name' => 'encrypted',
            'phone' => 'encrypted',
            'accepts_marketing' => 'bool',
            'orders_count' => 'integer',
            'total_spent_amount' => 'decimal:6',
            'raw_snapshot' => 'array',
            'synced_at' => 'immutable_datetime',
        ];
    }
}
