<?php

declare(strict_types=1);

namespace Capell\UrlManager\Models;

use Capell\Core\Models\Language;
use Capell\Core\Models\Site;
use Capell\UrlManager\Enums\NotFoundOpportunityStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int|null $site_id
 * @property int|null $language_id
 * @property string $source_url
 * @property int $hit_count
 * @property CarbonInterface|null $first_seen_at
 * @property CarbonInterface|null $last_seen_at
 * @property string|null $suggested_target_url
 * @property NotFoundOpportunityStatus $status
 * @property array<string, mixed>|null $context
 */
class NotFoundOpportunity extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    protected $table = 'url_manager_not_found_opportunities';

    /** @var list<string> */
    protected $fillable = [
        'site_id',
        'language_id',
        'source_url',
        'source_hash',
        'hit_count',
        'first_seen_at',
        'last_seen_at',
        'suggested_target_url',
        'redirect_rule_id',
        'status',
        'context',
    ];

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * @return BelongsTo<Language, $this>
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * @return BelongsTo<RedirectRule, $this>
     */
    public function redirectRule(): BelongsTo
    {
        return $this->belongsTo(RedirectRule::class, 'redirect_rule_id');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'hit_count' => 'int',
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'redirect_rule_id' => 'int',
            'status' => NotFoundOpportunityStatus::class,
            'context' => 'array',
        ];
    }
}
