<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\EmailStudio\Actions\CheckEmailSuppressionAction;
use Capell\EmailStudio\Actions\DeliverEmailMessageAction;
use Capell\EmailStudio\Actions\RenderEmailTemplateAction;
use Capell\EmailStudio\Actions\SuppressEmailAddressAction;
use Capell\EmailStudio\Data\InboundEmailReplyData;
use Capell\EmailStudio\Data\ProviderWebhookEventData;
use Capell\EmailStudio\Enums\EmailProviderType;
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
            $missing[] = $this->translation('capell-email-studio::package.health.components.render_action');
        }

        if (! class_exists(EmailVariableRenderer::class)) {
            $missing[] = $this->translation('capell-email-studio::package.health.components.variable_renderer');
        }

        if (! Schema::hasTable((new EmailTemplate)->getTable())) {
            $missing[] = $this->translation('capell-email-studio::package.health.components.email_templates_table');
        }

        return new DoctorCheckResultData(
            label: $this->translation('capell-email-studio::package.health.template_rendering.label'),
            passed: $missing === [],
            message: $missing === []
                ? $this->translation('capell-email-studio::package.health.template_rendering.passed')
                : $this->translation('capell-email-studio::package.health.missing', ['components' => implode(', ', $missing)]),
            remediation: $missing === []
                ? null
                : $this->translation('capell-email-studio::package.health.template_rendering.remediation'),
        );
    }

    private function providerDeliveryCheck(): DoctorCheckResultData
    {
        $missing = [];

        if (! class_exists(DeliverEmailMessageAction::class)) {
            $missing[] = $this->translation('capell-email-studio::package.health.components.deliver_action');
        }

        if (! class_exists(SendEmailJob::class)) {
            $missing[] = $this->translation('capell-email-studio::package.health.components.send_job');
        }

        if (! Schema::hasTable((new EmailMessage)->getTable())) {
            $missing[] = $this->translation('capell-email-studio::package.health.components.email_messages_table');
        }

        if (! Schema::hasTable((new EmailRecipient)->getTable())) {
            $missing[] = $this->translation('capell-email-studio::package.health.components.email_recipients_table');
        }

        $registeredProviders = $this->registeredProviders();

        if ($registeredProviders === []) {
            $missing[] = $this->translation('capell-email-studio::package.health.components.provider_adapter');
        }

        return new DoctorCheckResultData(
            label: $this->translation('capell-email-studio::package.health.provider_delivery.label'),
            passed: $missing === [],
            message: $missing === []
                ? $this->translation('capell-email-studio::package.health.provider_delivery.passed', ['count' => count($registeredProviders)])
                : $this->translation('capell-email-studio::package.health.missing', ['components' => implode(', ', $missing)]),
            remediation: $missing === []
                ? null
                : $this->translation('capell-email-studio::package.health.provider_delivery.remediation'),
        );
    }

    private function suppressionEnforcementCheck(): DoctorCheckResultData
    {
        $missing = [];

        if (! class_exists(CheckEmailSuppressionAction::class)) {
            $missing[] = $this->translation('capell-email-studio::package.health.components.check_suppression_action');
        }

        if (! class_exists(SuppressEmailAddressAction::class)) {
            $missing[] = $this->translation('capell-email-studio::package.health.components.suppress_email_action');
        }

        if (! class_exists(EmailAddressNormalizer::class)) {
            $missing[] = $this->translation('capell-email-studio::package.health.components.address_normalizer');
        }

        if (! Schema::hasTable((new EmailSuppression)->getTable())) {
            $missing[] = $this->translation('capell-email-studio::package.health.components.email_suppressions_table');
        }

        return new DoctorCheckResultData(
            label: $this->translation('capell-email-studio::package.health.suppressions.label'),
            passed: $missing === [],
            message: $missing === []
                ? $this->translation('capell-email-studio::package.health.suppressions.passed')
                : $this->translation('capell-email-studio::package.health.missing', ['components' => implode(', ', $missing)]),
            remediation: $missing === []
                ? null
                : $this->translation('capell-email-studio::package.health.suppressions.remediation'),
        );
    }

    private function providerEventsCheck(): DoctorCheckResultData
    {
        $missing = [];

        if (! Schema::hasTable((new EmailEvent)->getTable())) {
            $missing[] = $this->translation('capell-email-studio::package.health.components.email_events_table');
        }

        if (! Schema::hasTable((new EmailReply)->getTable())) {
            $missing[] = $this->translation('capell-email-studio::package.health.components.email_replies_table');
        }

        if ($this->registeredProviders() === []) {
            $missing[] = $this->translation('capell-email-studio::package.health.components.provider_adapter');
        }

        foreach ($this->providersWithoutNormalization() as $provider) {
            $missing[] = $this->translation('capell-email-studio::package.health.components.provider_normalizer', ['provider' => $provider]);
        }

        return new DoctorCheckResultData(
            label: $this->translation('capell-email-studio::package.health.provider_events.label'),
            passed: $missing === [],
            message: $missing === []
                ? $this->translation('capell-email-studio::package.health.provider_events.passed')
                : $this->translation('capell-email-studio::package.health.missing', ['components' => implode(', ', $missing)]),
            remediation: $missing === []
                ? null
                : $this->translation('capell-email-studio::package.health.provider_events.remediation'),
        );
    }

    /**
     * @return array<int, string>
     */
    private function providersWithoutNormalization(): array
    {
        $missing = [];

        foreach ($this->registeredProviders() as $provider) {
            if (! $this->providerNormalizesEvents($provider)) {
                $missing[] = $provider;
            }
        }

        return $missing;
    }

    private function providerNormalizesEvents(string $provider): bool
    {
        $type = EmailProviderType::tryFrom($provider);

        if (! $type instanceof EmailProviderType) {
            return false;
        }

        try {
            $adapter = resolve(EmailProviderRegistry::class)->adapter($type);
            $webhook = $adapter->normalizeWebhookPayload([
                'event' => 'delivered',
                'message_id' => 'email-studio-health-message',
                'recipient' => 'health@example.com',
                'id' => 'email-studio-health-event',
            ]);
            $reply = $adapter->normalizeInboundReply([
                'message_id' => 'email-studio-health-message',
                'from_email' => 'reply@example.com',
                'subject' => 'Health check reply',
                'text' => 'Health check reply',
            ]);

            return $webhook instanceof ProviderWebhookEventData
                && $reply instanceof InboundEmailReplyData
                && $webhook->provider === $provider
                && $reply->provider === $provider;
        } catch (Throwable) {
            return false;
        }
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

    /**
     * @param  array<string, int|string>  $replace
     */
    private function translation(string $key, array $replace = []): string
    {
        return (string) __($key, $replace);
    }
}
