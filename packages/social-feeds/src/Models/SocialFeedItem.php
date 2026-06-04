<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property int $connection_id
 * @property string $provider
 * @property string $external_id
 * @property string $type
 * @property string|null $text
 * @property string|null $permalink
 * @property string|null $media_url
 * @property string|null $thumbnail_url
 * @property string|null $author_name
 * @property string|null $author_avatar_url
 * @property CarbonImmutable|null $published_at
 */
final class SocialFeedItem extends Model
{
    protected $table = 'social_feed_items';

    protected $guarded = [];

    /**
     * @return BelongsTo<SocialFeedConnection, $this>
     */
    public function connection(): BelongsTo
    {
        return $this->belongsTo(SocialFeedConnection::class, 'connection_id');
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'raw' => 'array',
            'published_at' => 'immutable_datetime',
        ];
    }
}
