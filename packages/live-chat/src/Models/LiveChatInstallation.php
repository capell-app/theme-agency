<?php

declare(strict_types=1);

namespace Capell\LiveChat\Models;

use Capell\Core\Models\Site;
use Capell\LiveChat\Enums\LiveChatSourcePolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Override;

/**
 * @property int $site_id
 * @property string $uuid
 * @property string $name
 * @property string $public_key
 * @property list<string>|null $allowed_domains
 * @property string $timezone
 * @property array<string, mixed>|null $widget_settings
 * @property LiveChatSourcePolicy $source_policy
 * @property bool $is_active
 * @property array<string, mixed>|null $metadata
 * @property-read Site $site
 */
class LiveChatInstallation extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'allowed_domains',
        'is_active',
        'metadata',
        'name',
        'public_key',
        'site_id',
        'source_policy',
        'timezone',
        'uuid',
        'widget_settings',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_active' => true,
        'source_policy' => 'manual',
        'timezone' => 'UTC',
    ];

    public static function makePublicKey(): string
    {
        return 'lci_' . Str::random(40);
    }

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-live-chat.tables.installations');

        return is_string($tableName) ? $tableName : 'live_chat_installations';
    }

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * @return HasMany<LiveChatConversation, $this>
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(LiveChatConversation::class, 'installation_id');
    }

    #[Override]
    protected static function booted(): void
    {
        static::creating(function (LiveChatInstallation $installation): void {
            if (! is_string($installation->uuid) || $installation->uuid === '') {
                $installation->uuid = (string) Str::uuid();
            }

            if (! is_string($installation->public_key) || $installation->public_key === '') {
                $installation->public_key = self::makePublicKey();
            }
        });
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'allowed_domains' => 'encrypted:array',
            'is_active' => 'boolean',
            'metadata' => 'encrypted:array',
            'source_policy' => LiveChatSourcePolicy::class,
            'widget_settings' => 'encrypted:array',
        ];
    }
}
