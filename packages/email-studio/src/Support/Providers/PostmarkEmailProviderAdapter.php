<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Support\Providers;

use Capell\EmailStudio\Models\EmailMessage;
use Override;

class PostmarkEmailProviderAdapter extends SmtpEmailProviderAdapter
{
    #[Override]
    protected function mailerName(EmailMessage $message): ?string
    {
        $mailerName = $message->profile->provider_settings['mailer'] ?? 'postmark';

        return is_string($mailerName) ? $mailerName : 'postmark';
    }

    #[Override]
    protected function providerKey(): string
    {
        return 'postmark';
    }
}
