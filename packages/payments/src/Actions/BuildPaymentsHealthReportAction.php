<?php

declare(strict_types=1);

namespace Capell\Payments\Actions;

use Capell\Payments\Data\PaymentsHealthReportData;
use Capell\Payments\Enums\PaymentDisputeStatus;
use Capell\Payments\Enums\PaymentWebhookEventStatus;
use Capell\Payments\Models\CheckoutSession;
use Capell\Payments\Models\PaymentDispute;
use Capell\Payments\Models\PaymentWebhookEvent;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Schema;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static PaymentsHealthReportData run(int $freshnessHours = 48)
 */
final class BuildPaymentsHealthReportAction
{
    use AsAction;

    public function handle(int $freshnessHours = 48): PaymentsHealthReportData
    {
        $freshnessHours = $this->integerSetting('capell-payments.stripe.webhook_freshness_hours', 'webhook_freshness_hours', $freshnessHours);
        $stripeSecretConfigured = $this->configured('capell-payments.stripe.secret_key', 'stripe_secret_key');
        $stripeWebhookSecretConfigured = $this->configured('capell-payments.stripe.webhook_secret', 'stripe_webhook_secret');
        $recordedWebhookEvents = 0;
        $failedWebhookEvents = 0;
        $failedFulfillmentResults = 0;
        $unresolvedDisputes = 0;
        $latestWebhookReceivedAt = null;
        $webhooksFresh = false;
        $issues = [];

        if (! $stripeSecretConfigured) {
            $issues[] = 'Stripe secret key is not configured.';
        }

        if (! $stripeWebhookSecretConfigured) {
            $issues[] = 'Stripe webhook secret is not configured.';
        }

        if ($this->webhookEventsTableExists()) {
            $recordedWebhookEvents = PaymentWebhookEvent::query()->count();
            $failedWebhookEvents = PaymentWebhookEvent::query()
                ->where('status', PaymentWebhookEventStatus::Failed->value)
                ->count();
            $latestWebhookReceivedAt = PaymentWebhookEvent::query()
                ->latest('received_at')
                ->value('received_at');

            $webhooksFresh = $latestWebhookReceivedAt !== null
                && CarbonImmutable::parse($latestWebhookReceivedAt)->gte(CarbonImmutable::now()->subHours($freshnessHours));
        } else {
            $issues[] = 'Payments webhook event table has not been migrated.';
        }

        if ($this->checkoutSessionsTableExists()) {
            $failedFulfillmentResults = CheckoutSession::query()
                ->get()
                ->filter(fn (CheckoutSession $checkoutSession): bool => (bool) data_get($checkoutSession->metadata, 'fulfillment_failed', false))
                ->count();

            if ($failedFulfillmentResults > 0) {
                $issues[] = 'Payment checkout fulfilment failures need review.';
            }
        }

        if ($this->disputesTableExists()) {
            $unresolvedDisputes = PaymentDispute::query()
                ->whereIn('status', [
                    PaymentDisputeStatus::WarningNeedsResponse->value,
                    PaymentDisputeStatus::NeedsResponse->value,
                ])
                ->count();
        } else {
            $issues[] = 'Payments dispute table has not been migrated.';
        }

        if ($recordedWebhookEvents === 0) {
            $issues[] = 'No Stripe webhook events have been recorded yet.';
        } elseif (! $webhooksFresh) {
            $issues[] = sprintf('No Stripe webhook event has been recorded in the last %d hours.', $freshnessHours);
        }

        if ($failedWebhookEvents > 0) {
            $issues[] = sprintf('%d Stripe webhook event(s) failed processing.', $failedWebhookEvents);
        }

        if ($unresolvedDisputes > 0) {
            $issues[] = sprintf('%d payment dispute(s) need a response.', $unresolvedDisputes);
        }

        return new PaymentsHealthReportData(
            status: $this->status($stripeSecretConfigured, $stripeWebhookSecretConfigured, $failedWebhookEvents, $failedFulfillmentResults, $unresolvedDisputes, $issues),
            stripeSecretConfigured: $stripeSecretConfigured,
            stripeWebhookSecretConfigured: $stripeWebhookSecretConfigured,
            recordedWebhookEvents: $recordedWebhookEvents,
            failedWebhookEvents: $failedWebhookEvents,
            failedFulfillmentResults: $failedFulfillmentResults,
            unresolvedDisputes: $unresolvedDisputes,
            latestWebhookReceivedAt: $latestWebhookReceivedAt === null ? null : CarbonImmutable::parse($latestWebhookReceivedAt),
            webhooksFresh: $webhooksFresh,
            issues: $issues,
        );
    }

    private function configured(string $configKey, string $settingsKey): bool
    {
        $value = ResolvePaymentSettingAction::run($configKey, $settingsKey);

        return is_string($value) && trim($value) !== '';
    }

    private function integerSetting(string $configKey, string $settingsKey, int $fallback): int
    {
        $value = ResolvePaymentSettingAction::run($configKey, $settingsKey, $fallback);

        return is_numeric($value) ? (int) $value : $fallback;
    }

    private function webhookEventsTableExists(): bool
    {
        $tableName = config('capell-payments.tables.webhook_events', 'payment_webhook_events');

        return is_string($tableName) && Schema::hasTable($tableName);
    }

    private function checkoutSessionsTableExists(): bool
    {
        $tableName = config('capell-payments.tables.checkout_sessions', 'payment_checkout_sessions');

        return is_string($tableName) && Schema::hasTable($tableName);
    }

    private function disputesTableExists(): bool
    {
        $tableName = config('capell-payments.tables.disputes', 'payment_disputes');

        return is_string($tableName) && Schema::hasTable($tableName);
    }

    /**
     * @param  list<string>  $issues
     */
    private function status(
        bool $stripeSecretConfigured,
        bool $stripeWebhookSecretConfigured,
        int $failedWebhookEvents,
        int $failedFulfillmentResults,
        int $unresolvedDisputes,
        array $issues,
    ): string {
        if (! $stripeSecretConfigured || ! $stripeWebhookSecretConfigured || $failedWebhookEvents > 0 || $failedFulfillmentResults > 0 || $unresolvedDisputes > 0) {
            return 'failed';
        }

        return $issues === [] ? 'passed' : 'warning';
    }
}
