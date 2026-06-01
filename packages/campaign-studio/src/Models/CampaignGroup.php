<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Models;

use Capell\CampaignStudio\Database\Factories\CampaignGroupFactory;
use Capell\CampaignStudio\Enums\CampaignStatus;
use Capell\Core\Models\Site;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int|null $site_id
 * @property CampaignStatus $status
 * @property string|null $utm_source
 * @property string|null $utm_medium
 * @property string|null $utm_campaign
 */
class CampaignGroup extends Model
{
    /** @use HasFactory<CampaignGroupFactory> */
    use HasFactory;

    use SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'site_id',
        'name',
        'slug',
        'status',
        'starts_at',
        'ends_at',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'budget_amount',
        'notes',
    ];

    protected static string $factory = CampaignGroupFactory::class;

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-campaign-studio.tables.groups');

        return is_string($tableName) ? $tableName : 'campaign_groups';
    }

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * @return HasMany<CampaignLandingPage, $this>
     */
    public function landingPages(): HasMany
    {
        return $this->hasMany(CampaignLandingPage::class);
    }

    /**
     * @return HasMany<CampaignCtaBlock, $this>
     */
    public function ctaBlocks(): HasMany
    {
        return $this->hasMany(CampaignCtaBlock::class);
    }

    /**
     * @return HasMany<CampaignConversionGoal, $this>
     */
    public function conversionGoals(): HasMany
    {
        return $this->hasMany(CampaignConversionGoal::class);
    }

    /**
     * @return HasMany<CampaignConversion, $this>
     */
    public function conversions(): HasMany
    {
        return $this->hasMany(CampaignConversion::class);
    }

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    protected function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('status', CampaignStatus::Active)
            ->where(function (Builder $dateWindowQuery): void {
                $dateWindowQuery
                    ->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $dateWindowQuery): void {
                $dateWindowQuery
                    ->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            });
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'status' => CampaignStatus::class,
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
            'budget_amount' => 'decimal:2',
        ];
    }
}
