<?php

declare(strict_types=1);

namespace Capell\Payments\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $checkout_session_id
 * @property int|null $site_id
 * @property string $download_key
 * @property string $download_name
 * @property string $disk
 * @property string $path
 * @property string|null $file_name
 * @property string|null $email
 * @property CarbonInterface|null $expires_at
 * @property CarbonInterface|null $fulfilled_at
 * @property int $download_count
 * @property CarbonInterface|null $last_downloaded_at
 * @property array<string, mixed>|null $metadata
 */
final class PaymentDownloadEntitlement extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'checkout_session_id',
        'site_id',
        'download_key',
        'download_name',
        'disk',
        'path',
        'file_name',
        'email',
        'expires_at',
        'fulfilled_at',
        'download_count',
        'last_downloaded_at',
        'metadata',
    ];

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-payments.tables.download_entitlements');

        return is_string($tableName) ? $tableName : 'payment_download_entitlements';
    }

    /**
     * @return BelongsTo<CheckoutSession, $this>
     */
    public function checkoutSession(): BelongsTo
    {
        return $this->belongsTo(CheckoutSession::class, 'checkout_session_id');
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'email' => 'encrypted',
            'expires_at' => 'datetime',
            'fulfilled_at' => 'datetime',
            'last_downloaded_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}
