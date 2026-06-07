<?php

declare(strict_types=1);

namespace Capell\Newsletter\Actions;

use Capell\Newsletter\Models\NewsletterSend;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static array<string, mixed> run(NewsletterSend $send)
 */
final class BuildNewsletterSendHandoffPayloadAction
{
    use AsAction;

    /**
     * @return array<string, mixed>
     */
    public function handle(NewsletterSend $send): array
    {
        $send = $send->fresh(['segment', 'providerAudience.providerConnection']) ?? $send;

        return [
            'strategy' => config('capell-newsletter.sends.delivery_strategy', 'external_handoff'),
            'send' => [
                'id' => $send->getKey(),
                'site_id' => $send->site_id,
                'name' => $send->name,
                'subject' => $send->subject,
                'preheader' => $send->preheader,
                'status' => $send->status->value,
                'scheduled_at' => $send->scheduled_at?->toISOString(),
            ],
            'audience' => [
                'segment_id' => $send->newsletter_segment_id,
                'segment_handle' => $send->segment?->handle,
                'provider_audience_id' => $send->newsletter_provider_audience_id,
                'provider_connection_id' => $send->providerAudience?->providerConnection?->getKey(),
                'provider' => $send->providerAudience?->providerConnection?->provider?->value,
                'remote_audience_id' => $send->providerAudience?->remote_id,
            ],
            'utm' => [
                'source' => $send->utm_source,
                'medium' => $send->utm_medium,
                'campaign' => $send->utm_campaign,
                'term' => $send->utm_term,
                'content' => $send->utm_content,
                'id' => $send->utm_id,
            ],
            'metadata' => $send->metadata ?? [],
        ];
    }
}
