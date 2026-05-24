<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Models;

use Capell\Core\Models\Site;
use Capell\SeoSuite\Enums\AiDiscoveryCrawlerDirectiveEnum;
use Capell\SeoSuite\Enums\AiDiscoveryCrawlerPurposeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

class AiDiscoveryCrawlerRule extends Model
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    protected $guarded = [];

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'purpose' => AiDiscoveryCrawlerPurposeEnum::class,
            'directive' => AiDiscoveryCrawlerDirectiveEnum::class,
            'enabled' => 'bool',
            'crawl_delay_seconds' => 'int',
        ];
    }
}
