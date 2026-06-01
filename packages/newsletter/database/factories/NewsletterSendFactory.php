<?php

declare(strict_types=1);

namespace Capell\Newsletter\Database\Factories;

use Capell\Core\Models\Site;
use Capell\Newsletter\Enums\NewsletterSendStatus;
use Capell\Newsletter\Models\NewsletterSend;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NewsletterSend>
 */
class NewsletterSendFactory extends Factory
{
    protected $model = NewsletterSend::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'newsletter_segment_id' => null,
            'newsletter_provider_audience_id' => null,
            'name' => $this->faker->sentence(3),
            'subject' => $this->faker->sentence(5),
            'preheader' => null,
            'status' => NewsletterSendStatus::Draft,
            'scheduled_at' => null,
            'sent_at' => null,
            'cancelled_at' => null,
            'failed_at' => null,
            'utm_source' => null,
            'utm_medium' => null,
            'utm_campaign' => null,
            'utm_term' => null,
            'utm_content' => null,
            'utm_id' => null,
            'metadata' => [],
        ];
    }

    public function scheduled(): self
    {
        return $this->state([
            'status' => NewsletterSendStatus::Scheduled,
            'scheduled_at' => now()->addDay(),
        ]);
    }
}
