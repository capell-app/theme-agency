<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Actions;

use Capell\EmailStudio\Models\EmailProfile;
use Lorisleiva\Actions\Concerns\AsAction;

final class ValidateProviderWebhookSignatureAction
{
    use AsAction;

    public function handle(EmailProfile $profile, string $payload, ?string $signature): bool
    {
        $secret = $profile->provider_settings['webhook_secret'] ?? null;

        if (! is_string($secret) || $secret === '') {
            return true;
        }

        if (! is_string($signature) || $signature === '') {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }
}
