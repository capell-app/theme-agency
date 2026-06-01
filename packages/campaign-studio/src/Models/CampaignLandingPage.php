<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Models;

use Capell\CampaignStudio\Database\Factories\CampaignLandingPageFactory;
use Capell\Core\Models\Page;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property int $id
 * @property int $campaign_group_id
 * @property int $page_id
 * @property string|null $headline
 * @property string|null $utm_term
 * @property string|null $utm_content
 * @property bool $is_primary
 * @property int $conversions_count
 * @property-read CampaignGroup|null $campaignGroup
 */
class CampaignLandingPage extends Model
{
    /** @use HasFactory<CampaignLandingPageFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'campaign_group_id',
        'page_id',
        'headline',
        'primary_goal_id',
        'utm_content',
        'utm_term',
        'is_primary',
    ];

    protected static string $factory = CampaignLandingPageFactory::class;

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-campaign-studio.tables.landing_pages');

        return is_string($tableName) ? $tableName : 'campaign_landing_pages';
    }

    /**
     * @return BelongsTo<CampaignGroup, $this>
     */
    public function campaignGroup(): BelongsTo
    {
        return $this->belongsTo(CampaignGroup::class);
    }

    /**
     * @return BelongsTo<Page, $this>
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * @return BelongsTo<CampaignConversionGoal, $this>
     */
    public function primaryGoal(): BelongsTo
    {
        return $this->belongsTo(CampaignConversionGoal::class, 'primary_goal_id');
    }

    /**
     * @return HasMany<CampaignConversion, $this>
     */
    public function conversions(): HasMany
    {
        return $this->hasMany(CampaignConversion::class);
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }
}
