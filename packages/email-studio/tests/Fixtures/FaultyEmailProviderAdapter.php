<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Tests\Fixtures;

use Capell\EmailStudio\Contracts\EmailProviderAdapter;
use Capell\EmailStudio\Data\InboundEmailReplyData;
use Capell\EmailStudio\Data\ProviderSendResultData;
use Capell\EmailStudio\Data\ProviderWebhookEventData;
use Capell\EmailStudio\Models\EmailMessage;

final class FaultyEmailProviderAdapter implements EmailProviderAdapter
{
    public function send(EmailMessage $message): ProviderSendResultData
    {
        return new ProviderSendResultData(successful: false, failureReason: 'Faulty health-test adapter.');
    }

    public function normalizeWebhookPayload(array $payload, array $headers = []): ProviderWebhookEventData
    {
        return new ProviderWebhookEventData(provider: 'wrong-provider', eventType: 'failed', payload: $payload);
    }

    public function normalizeInboundReply(array $payload, array $headers = []): InboundEmailReplyData
    {
        return new InboundEmailReplyData(provider: 'wrong-provider', providerMessageId: null, fromEmail: 'reply@example.com', payload: $payload);
    }
}
