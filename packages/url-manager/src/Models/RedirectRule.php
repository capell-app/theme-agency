<?php

declare(strict_types=1);

namespace Capell\UrlManager\Models;

use Capell\Core\Models\Language;
use Capell\Core\Models\Site;
use Capell\UrlManager\Enums\RedirectMatchType;
use Capell\UrlManager\Enums\RedirectRuleStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property int|null $site_id
 * @property int|null $language_id
 * @property string $source_url
 * @property string $target_url
 * @property int $status_code
 * @property RedirectMatchType $match_type
 * @property RedirectRuleStatus $status
 * @property int $priority
 * @property bool $preserve_query
 * @property int $hit_count
 * @property CarbonInterface|null $last_hit_at
 * @property string|null $notes
 */
class RedirectRule extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    protected $table = 'url_manager_redirect_rules';

    /** @var list<string> */
    protected $fillable = [
        'site_id',
        'language_id',
        'source_url',
        'source_hash',
        'target_url',
        'target_hash',
        'status_code',
        'match_type',
        'status',
        'priority',
        'preserve_query',
        'notes',
        'hit_count',
        'last_hit_at',
        'created_by_user_id',
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
     * @return HasMany<RedirectHit, $this>
     */
    public function hits(): HasMany
    {
        return $this->hasMany(RedirectHit::class, 'redirect_rule_id');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'match_type' => RedirectMatchType::class,
            'status' => RedirectRuleStatus::class,
            'priority' => 'int',
            'preserve_query' => 'bool',
            'hit_count' => 'int',
            'last_hit_at' => 'datetime',
        ];
    }
}
