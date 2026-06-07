<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Actions;

use Capell\EmailStudio\Models\EmailProfile;
use Lorisleiva\Actions\Concerns\AsAction;

final class ResolveProviderWebhookProfileAction
{
    use AsAction;

    public function handle(string $token): ?EmailProfile
    {
        if ($token === '') {
            return null;
        }

        return EmailProfile::query()
            ->where('webhook_endpoint_token_hash', hash('sha256', $token))
            ->first();
    }
}
