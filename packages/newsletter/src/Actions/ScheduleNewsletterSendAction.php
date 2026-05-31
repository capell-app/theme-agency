<?php

declare(strict_types=1);

namespace Capell\Newsletter\Actions;

use Capell\Newsletter\Enums\NewsletterSendStatus;
use Capell\Newsletter\Models\NewsletterSend;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\Concerns\AsAction;

final class ScheduleNewsletterSendAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function handle(
        int $siteId,
        string $name,
        string $subject,
        CarbonInterface|string $scheduledAt,
        ?int $segmentId = null,
        ?int $providerAudienceId = null,
        ?string $preheader = null,
        ?string $utmSource = null,
        ?string $utmMedium = null,
        ?string $utmCampaign = null,
        ?string $utmTerm = null,
        ?string $utmContent = null,
        ?string $utmId = null,
        array $metadata = [],
    ): NewsletterSend {
        $scheduledAt = $scheduledAt instanceof CarbonInterface
            ? CarbonImmutable::instance($scheduledAt)
            : CarbonImmutable::parse($scheduledAt);

        $attributes = [
            'site_id' => $siteId,
            'newsletter_segment_id' => $segmentId,
            'newsletter_provider_audience_id' => $providerAudienceId,
            'name' => $name,
            'subject' => $subject,
            'preheader' => $preheader,
            'scheduled_at' => $scheduledAt,
            'utm_source' => $utmSource,
            'utm_medium' => $utmMedium,
            'utm_campaign' => $utmCampaign,
            'utm_term' => $utmTerm,
            'utm_content' => $utmContent,
            'utm_id' => $utmId,
            'metadata' => $metadata,
        ];

        Validator::make($attributes, [
            'site_id' => ['required', 'integer', Rule::exists('sites', 'id')],
            'newsletter_segment_id' => ['nullable', 'integer', Rule::exists('newsletter_segments', 'id')->where('site_id', $siteId)],
            'newsletter_provider_audience_id' => ['nullable', 'integer', Rule::exists('newsletter_provider_audiences', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'preheader' => ['nullable', 'string', 'max:255'],
            'scheduled_at' => ['required', 'date'],
            'utm_source' => ['nullable', 'string', 'max:255'],
            'utm_medium' => ['nullable', 'string', 'max:255'],
            'utm_campaign' => ['nullable', 'string', 'max:255'],
            'utm_term' => ['nullable', 'string', 'max:255'],
            'utm_content' => ['nullable', 'string', 'max:255'],
            'utm_id' => ['nullable', 'string', 'max:255'],
            'metadata' => ['array'],
        ])->validate();

        return NewsletterSend::query()->create([
            ...$attributes,
            'status' => NewsletterSendStatus::Scheduled,
        ]);
    }
}
