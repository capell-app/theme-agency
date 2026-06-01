<?php

declare(strict_types=1);

namespace Capell\Newsletter\Models;

use Capell\Core\Models\Site;
use Capell\Newsletter\Database\Factories\NewsletterSendFactory;
use Capell\Newsletter\Enums\NewsletterSendStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property NewsletterSendStatus $status
 * @property CarbonInterface|null $scheduled_at
 * @property CarbonInterface|null $sent_at
 * @property CarbonInterface|null $cancelled_at
 * @property CarbonInterface|null $failed_at
 * @property array<array-key, mixed>|null $metadata
 */
class NewsletterSend extends Model
{
    /** @use HasFactory<NewsletterSendFactory> */
    use HasFactory;

    protected $table = 'newsletter_sends';

    protected static string $factory = NewsletterSendFactory::class;

    /** @var list<string> */
    protected $fillable = [
        'site_id',
        'newsletter_segment_id',
        'newsletter_provider_audience_id',
        'name',
        'subject',
        'preheader',
        'status',
        'scheduled_at',
        'sent_at',
        'cancelled_at',
        'failed_at',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'utm_id',
        'metadata',
    ];

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * @return BelongsTo<Segment, $this>
     */
    public function segment(): BelongsTo
    {
        return $this->belongsTo(Segment::class, 'newsletter_segment_id');
    }

    /**
     * @return BelongsTo<ProviderAudience, $this>
     */
    public function providerAudience(): BelongsTo
    {
        return $this->belongsTo(ProviderAudience::class, 'newsletter_provider_audience_id');
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'status' => NewsletterSendStatus::class,
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'failed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}
