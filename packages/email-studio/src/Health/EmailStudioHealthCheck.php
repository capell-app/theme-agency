<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\EmailStudio\Actions\CheckEmailSuppressionAction;
use Capell\EmailStudio\Actions\DeliverEmailMessageAction;
use Capell\EmailStudio\Actions\RenderEmailTemplateAction;
use Capell\EmailStudio\Actions\SuppressEmailAddressAction;
use Capell\EmailStudio\Jobs\SendEmailJob;
use Capell\EmailStudio\Models\EmailEvent;
use Capell\EmailStudio\Models\EmailMessage;
use Capell\EmailStudio\Models\EmailRecipient;
use Capell\EmailStudio\Models\EmailReply;
use Capell\EmailStudio\Models\EmailSuppression;
use Capell\EmailStudio\Models\EmailTemplate;
use Capell\EmailStudio\Support\EmailAddressNormalizer;
use Capell\EmailStudio\Support\EmailProviderRegistry;
use Capell\EmailStudio\Support\EmailVariableRenderer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class EmailStudioHealthCheck implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    /**
     * @return Collection<int, DoctorCheckResultData>
     */
    public static function runDiagnostics(): Collection
    {
        $check = new self;

        return collect([
            $check->templateRenderingCheck(),
            $check->providerDeliveryCheck(),
            $check->suppressionEnforcementCheck(),
            $check->providerEventsCheck(),
        ]);
    }

    public static function passed(): bool
    {
        return self::runDiagnostics()
            ->every(fn (DoctorCheckResultData $result): bool => $result->passed);
    }

    private function templateRenderingCheck(): DoctorCheckResultData
    {
        $missing = [];

        if (! class_exists(RenderEmailTemplateAction::class)) {
            $missing[] = 'RenderEmailTemplateAction';
        }

        if (! class_exists(EmailVariableRenderer::class)) {
            $missing[] = 'EmailVariableRenderer';
        }

        if (! Schema::hasTable((new EmailTemplate)->getTable())) {
            $missing[] = 'email_templates table';
        }

        return new DoctorCheckResultData(
            label: 'Email templates render through typed template actions',
            passed: $missing === [],
            message: $missing === []
                ? 'Template render action, variable renderer, and templates table are available.'
                : 'Missing: ' . implode(', ', $missing) . '.',
            remediation: $missing === []
                ? null
                : 'Run Email Studio migrations and confirm the render action and renderer are present.',
        );
    }

    private function providerDeliveryCheck(): DoctorCheckResultData
    {
        $missing = [];

        if (! class_exists(DeliverEmailMessageAction::class)) {
            $missing[] = 'DeliverEmailMessageAction';
        }

        if (! class_exists(SendEmailJob::class)) {
            $missing[] = 'SendEmailJob';
        }

        if (! Schema::hasTable((new EmailMessage)->getTable())) {
            $missing[] = 'email_messages table';
        }

        if (! Schema::hasTable((new EmailRecipient)->getTable())) {
            $missing[] = 'email_recipients table';
        }

        $registeredProviders = $this->registeredProviders();

        if ($registeredProviders === []) {
            $missing[] = 'at least one registered provider adapter';
        }

        return new DoctorCheckResultData(
            label: 'Queued email delivery records provider results per recipient',
            passed: $missing === [],
            message: $missing === []
                ? sprintf(
                    'Delivery action, send job, message and recipient tables, and %d provider adapter(s) are available.',
                    count($registeredProviders),
                )
                : 'Missing: ' . implode(', ', $missing) . '.',
            remediation: $missing === []
                ? null
                : 'Run Email Studio migrations and confirm provider adapters are registered in EmailProviderRegistry.',
        );
    }

    private function suppressionEnforcementCheck(): DoctorCheckResultData
    {
        $missing = [];

        if (! class_exists(CheckEmailSuppressionAction::class)) {
            $missing[] = 'CheckEmailSuppressionAction';
        }

        if (! class_exists(SuppressEmailAddressAction::class)) {
            $missing[] = 'SuppressEmailAddressAction';
        }

        if (! class_exists(EmailAddressNormalizer::class)) {
            $missing[] = 'EmailAddressNormalizer';
        }

        if (! Schema::hasTable((new EmailSuppression)->getTable())) {
            $missing[] = 'email_suppressions table';
        }

        return new DoctorCheckResultData(
            label: 'Suppressions are enforced before provider handoff',
            passed: $missing === [],
            message: $missing === []
                ? 'Suppression check and capture actions, address normalizer, and suppressions table are available.'
                : 'Missing: ' . implode(', ', $missing) . '.',
            remediation: $missing === []
                ? null
                : 'Run Email Studio migrations and confirm the suppression actions and normalizer are present.',
        );
    }

    private function providerEventsCheck(): DoctorCheckResultData
    {
        $missing = [];

        if (! Schema::hasTable((new EmailEvent)->getTable())) {
            $missing[] = 'email_events table';
        }

        if (! Schema::hasTable((new EmailReply)->getTable())) {
            $missing[] = 'email_replies table';
        }

        return new DoctorCheckResultData(
            label: 'Provider webhook events and inbound replies normalize into local records',
            passed: $missing === [],
            message: $missing === []
                ? 'Adapters normalize webhook events and inbound replies, and the events and replies tables are available. Ingestion routes are not yet shipped.'
                : 'Missing: ' . implode(', ', $missing) . '.',
            remediation: $missing === []
                ? null
                : 'Run Email Studio migrations and confirm the provider adapter normalization methods are present.',
        );
    }

    /**
     * @return array<int, string>
     */
    private function registeredProviders(): array
    {
        try {
            return resolve(EmailProviderRegistry::class)->supportedProviders();
        } catch (Throwable) {
            return [];
        }
    }
}
