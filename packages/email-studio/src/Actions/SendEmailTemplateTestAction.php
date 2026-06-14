<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Actions;

use Capell\EmailStudio\Data\EmailAddressData;
use Capell\EmailStudio\Data\EmailHeaderData;
use Capell\EmailStudio\Data\EmailTemplateDefinitionData;
use Capell\EmailStudio\Data\SendEmailData;
use Capell\EmailStudio\Models\EmailMessage;
use Capell\EmailStudio\Support\EmailTemplateRegistry;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\LaravelData\DataCollection;

class SendEmailTemplateTestAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>|null  $variables
     */
    public function handle(
        string $templateKey,
        string $recipientEmail,
        ?string $recipientName = null,
        ?array $variables = null,
        ?int $siteId = null,
        string $siteScopeKey = 'global',
        ?string $locale = null,
        ?int $emailProfileId = null,
    ): EmailMessage {
        $definition = resolve(EmailTemplateRegistry::class)->findDefinition($templateKey, $locale ?? app()->getLocale());

        return SendEmailAction::run(new SendEmailData(
            templateKey: $templateKey,
            to: new DataCollection(EmailAddressData::class, [new EmailAddressData($recipientEmail, $recipientName)]),
            cc: new DataCollection(EmailAddressData::class, []),
            bcc: new DataCollection(EmailAddressData::class, []),
            siteId: $siteId,
            siteScopeKey: $siteScopeKey,
            emailProfileId: $emailProfileId,
            variables: $variables ?? ($definition instanceof EmailTemplateDefinitionData ? $definition->sampleVariables() : []),
            headers: new DataCollection(EmailHeaderData::class, []),
            triggeredByType: 'email_template_test',
            triggeredById: null,
            queue: false,
            locale: $locale,
        ));
    }
}
