<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property string $provider
 * @property string $state
 * @property CarbonImmutable $expires_at
 */
final class SocialFeedOAuthState extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    protected $table = 'social_feed_oauth_states';

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'expires_at' => 'immutable_datetime',
        ];
    }
}
