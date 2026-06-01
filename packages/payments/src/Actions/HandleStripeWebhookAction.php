<?php

declare(strict_types=1);

namespace Capell\Payments\Actions;

use Capell\Payments\Data\CheckoutSessionData;
use Capell\Payments\Data\CreateCheckoutSessionData;
use Capell\Payments\Data\PaymentDisputeData;
use Capell\Payments\Data\PaymentIntentData;
use Capell\Payments\Data\PaymentRefundData;
use Capell\Payments\Data\SubscriptionData;
use Capell\Payments\Enums\CheckoutMode;
use Capell\Payments\Enums\CheckoutSessionStatus;
use Capell\Payments\Enums\PaymentDisputeStatus;
use Capell\Payments\Enums\PaymentIntentStatus;
use Capell\Payments\Enums\PaymentProvider;
use Capell\Payments\Enums\PaymentPurpose;
use Capell\Payments\Enums\PaymentRefundStatus;
use Capell\Payments\Enums\PaymentWebhookEventStatus;
use Capell\Payments\Enums\SubscriptionStatus;
use Capell\Payments\Exceptions\PaymentGatewayConfigurationException;
use Capell\Payments\Models\CheckoutSession;
use Capell\Payments\Models\PaymentWebhookEvent;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

final class HandleStripeWebhookAction
{
    use AsAction;

    public function handle(string $payload, ?string $signatureHeader): PaymentWebhookEvent
    {
        $endpointSecret = ResolvePaymentSettingAction::run('capell-payments.stripe.webhook_secret', 'stripe_webhook_secret');

        if (! is_string($endpointSecret) || $endpointSecret === '') {
            throw PaymentGatewayConfigurationException::missingStripeWebhookSecret();
        }

        $eventPayload = VerifyStripeWebhookSignatureAction::run($payload, $signatureHeader, $endpointSecret);
        $event = $this->recordEvent($eventPayload, $signatureHeader);

        if ($event->status === PaymentWebhookEventStatus::Processed || $event->status === PaymentWebhookEventStatus::Ignored) {
            return $event;
        }

        try {
            DB::transaction(function () use ($eventPayload, $event): void {
                $this->processEvent($eventPayload);

                $event->forceFill([
                    'status' => $this->isSupportedEvent($event->event_type)
                        ? PaymentWebhookEventStatus::Processed->value
                        : PaymentWebhookEventStatus::Ignored->value,
                    'processed_at' => CarbonImmutable::now(),
                    'failed_at' => null,
                    'error' => null,
                ])->save();
            });
        } catch (Throwable $exception) {
            $event->forceFill([
                'status' => PaymentWebhookEventStatus::Failed->value,
                'failed_at' => CarbonImmutable::now(),
                'error' => $exception->getMessage(),
            ])->save();

            throw $exception;
        }

        return $event->refresh();
    }

    /**
     * @param  array<string, mixed>  $eventPayload
     */
    private function recordEvent(array $eventPayload, ?string $signatureHeader): PaymentWebhookEvent
    {
        $providerEventId = $this->stringValue($eventPayload['id'] ?? null);
        $eventType = $this->stringValue($eventPayload['type'] ?? null);

        if ($providerEventId === null || $eventType === null) {
            throw new InvalidArgumentException('Stripe webhook payload is missing an event id or type.');
        }

        return PaymentWebhookEvent::query()->firstOrCreate([
            'provider' => PaymentProvider::Stripe->value,
            'provider_event_id' => $providerEventId,
        ], [
            'event_type' => $eventType,
            'livemode' => (bool) ($eventPayload['livemode'] ?? false),
            'api_version' => $this->stringValue($eventPayload['api_version'] ?? null),
            'status' => PaymentWebhookEventStatus::Received->value,
            'signature_header_hash' => is_string($signatureHeader) ? hash('sha256', $signatureHeader) : null,
            'payload' => $eventPayload,
            'received_at' => CarbonImmutable::now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $eventPayload
     */
    private function processEvent(array $eventPayload): void
    {
        $eventType = $this->stringValue($eventPayload['type'] ?? null);
        $object = $this->eventObject($eventPayload);

        match ($eventType) {
            'checkout.session.completed',
            'checkout.session.async_payment_succeeded' => $this->recordAndFulfillCheckoutSession($object),
            'checkout.session.expired' => $this->recordCheckoutSession($object),
            'payment_intent.amount_capturable_updated',
            'payment_intent.canceled',
            'payment_intent.created',
            'payment_intent.partially_funded',
            'payment_intent.payment_failed',
            'payment_intent.processing',
            'payment_intent.requires_action',
            'payment_intent.succeeded' => $this->recordPaymentIntent($object),
            'customer.subscription.created',
            'customer.subscription.deleted',
            'customer.subscription.paused',
            'customer.subscription.pending_update_applied',
            'customer.subscription.pending_update_expired',
            'customer.subscription.resumed',
            'customer.subscription.trial_will_end',
            'customer.subscription.updated' => $this->recordSubscription($object),
            'refund.created',
            'refund.updated',
            'refund.failed' => $this->recordRefund($object),
            'charge.refunded' => $this->recordChargeRefunds($object),
            'charge.dispute.closed',
            'charge.dispute.created',
            'charge.dispute.funds_reinstated',
            'charge.dispute.funds_withdrawn',
            'charge.dispute.updated' => $this->recordDispute($object),
            default => null,
        };
    }

    private function isSupportedEvent(string $eventType): bool
    {
        return str_starts_with($eventType, 'checkout.session.')
            || str_starts_with($eventType, 'payment_intent.')
            || str_starts_with($eventType, 'customer.subscription.')
            || str_starts_with($eventType, 'refund.')
            || $eventType === 'charge.refunded'
            || str_starts_with($eventType, 'charge.dispute.');
    }

    /**
     * @param  array<string, mixed>  $eventPayload
     * @return array<string, mixed>
     */
    private function eventObject(array $eventPayload): array
    {
        $object = data_get($eventPayload, 'data.object');

        return is_array($object) ? $object : [];
    }

    /**
     * @param  array<string, mixed>  $object
     */
    private function recordAndFulfillCheckoutSession(array $object): void
    {
        $checkoutSession = $this->recordCheckoutSession($object);

        if ($checkoutSession !== null) {
            FulfillCompletedCheckoutSessionAction::run($checkoutSession);
        }
    }

    /**
     * @param  array<string, mixed>  $object
     */
    private function recordCheckoutSession(array $object): ?CheckoutSession
    {
        $providerSessionId = $this->stringValue($object['id'] ?? null);

        if ($providerSessionId === null) {
            return null;
        }

        $metadata = $this->metadata($object);

        return RecordCheckoutSessionAction::run(new CheckoutSessionData(
            provider: PaymentProvider::Stripe,
            providerSessionId: $providerSessionId,
            status: $this->checkoutStatus($object['status'] ?? null),
            mode: $this->checkoutMode($object['mode'] ?? null),
            purpose: $this->paymentPurpose($metadata['capell_purpose'] ?? null),
            url: $this->stringValue($object['url'] ?? null),
            currency: $this->stringValue($object['currency'] ?? null),
            amountSubtotal: $this->integerValue($object['amount_subtotal'] ?? null),
            amountTotal: $this->integerValue($object['amount_total'] ?? null),
            providerCustomerId: $this->stringValue($object['customer'] ?? null),
            customerEmail: $this->stringValue(data_get($object, 'customer_details.email')),
            customerName: $this->stringValue(data_get($object, 'customer_details.name')),
            providerPaymentIntentId: $this->stringValue($object['payment_intent'] ?? null),
            providerSubscriptionId: $this->stringValue($object['subscription'] ?? null),
            expiresAt: $this->timestamp($object['expires_at'] ?? null),
            completedAt: $this->timestamp($object['completed_at'] ?? null),
            metadata: $metadata,
            providerPayload: $object,
        ), new CreateCheckoutSessionData(
            successUrl: '',
            cancelUrl: '',
            lineItems: [],
            purpose: $this->paymentPurpose($metadata['capell_purpose'] ?? null),
            mode: $this->checkoutMode($object['mode'] ?? null),
            provider: PaymentProvider::Stripe,
            siteId: $this->integerValue($metadata['capell_site_id'] ?? null),
            providerCustomerId: $this->stringValue($object['customer'] ?? null),
            customerEmail: $this->stringValue(data_get($object, 'customer_details.email')),
            customerName: $this->stringValue(data_get($object, 'customer_details.name')),
            billableType: $this->stringValue($metadata['capell_billable_type'] ?? null),
            billableId: $this->stringValue($metadata['capell_billable_id'] ?? null),
            payableType: $this->stringValue($metadata['capell_payable_type'] ?? null),
            payableId: $this->stringValue($metadata['capell_payable_id'] ?? null),
            sourceType: $this->stringValue($metadata['capell_source_type'] ?? null),
            sourceId: $this->stringValue($metadata['capell_source_id'] ?? null),
            referenceId: $this->stringValue($object['client_reference_id'] ?? null),
            metadata: $metadata,
        ));
    }

    /**
     * @param  array<string, mixed>  $object
     */
    private function recordPaymentIntent(array $object): void
    {
        $providerPaymentIntentId = $this->stringValue($object['id'] ?? null);
        $amount = $this->integerValue($object['amount'] ?? null);
        $currency = $this->stringValue($object['currency'] ?? null);

        if ($providerPaymentIntentId === null || $amount === null || $currency === null) {
            return;
        }

        RecordPaymentIntentAction::run(new PaymentIntentData(
            provider: PaymentProvider::Stripe,
            providerPaymentIntentId: $providerPaymentIntentId,
            status: $this->paymentIntentStatus($object['status'] ?? null),
            amount: $amount,
            currency: $currency,
            providerCustomerId: $this->stringValue($object['customer'] ?? null),
            providerSessionId: $this->stringValue(data_get($object, 'metadata.capell_checkout_session_id')),
            metadata: $this->metadata($object),
            providerPayload: $object,
        ));
    }

    /**
     * @param  array<string, mixed>  $object
     */
    private function recordSubscription(array $object): void
    {
        $providerSubscriptionId = $this->stringValue($object['id'] ?? null);

        if ($providerSubscriptionId === null) {
            return;
        }

        RecordSubscriptionAction::run(new SubscriptionData(
            provider: PaymentProvider::Stripe,
            providerSubscriptionId: $providerSubscriptionId,
            status: $this->subscriptionStatus($object['status'] ?? null),
            providerCustomerId: $this->stringValue($object['customer'] ?? null),
            providerSessionId: $this->stringValue(data_get($object, 'metadata.capell_checkout_session_id')),
            trialEndsAt: $this->timestamp($object['trial_end'] ?? null),
            currentPeriodStartsAt: $this->timestamp($object['current_period_start'] ?? null),
            currentPeriodEndsAt: $this->timestamp($object['current_period_end'] ?? null),
            cancelAt: $this->timestamp($object['cancel_at'] ?? null),
            canceledAt: $this->timestamp($object['canceled_at'] ?? null),
            metadata: $this->metadata($object),
            providerPayload: $object,
        ));
    }

    /**
     * @param  array<string, mixed>  $object
     */
    private function recordChargeRefunds(array $object): void
    {
        $refunds = data_get($object, 'refunds.data');

        if (! is_array($refunds)) {
            return;
        }

        foreach ($refunds as $refund) {
            if (! is_array($refund)) {
                continue;
            }

            $refund['charge'] ??= $object['id'] ?? null;
            $refund['payment_intent'] ??= $object['payment_intent'] ?? null;

            $this->recordRefund($refund);
        }
    }

    /**
     * @param  array<string, mixed>  $object
     */
    private function recordRefund(array $object): void
    {
        $providerRefundId = $this->stringValue($object['id'] ?? null);
        $amount = $this->integerValue($object['amount'] ?? null);
        $currency = $this->stringValue($object['currency'] ?? null);

        if ($providerRefundId === null || $amount === null || $currency === null) {
            return;
        }

        RecordPaymentRefundAction::run(new PaymentRefundData(
            provider: PaymentProvider::Stripe,
            providerRefundId: $providerRefundId,
            status: $this->refundStatus($object['status'] ?? null),
            amount: $amount,
            currency: $currency,
            providerPaymentIntentId: $this->stringValue($object['payment_intent'] ?? null),
            providerChargeId: $this->stringValue($object['charge'] ?? null),
            reason: $this->stringValue($object['reason'] ?? null),
            metadata: $this->metadata($object),
            providerPayload: $object,
        ));
    }

    /**
     * @param  array<string, mixed>  $object
     */
    private function recordDispute(array $object): void
    {
        $providerDisputeId = $this->stringValue($object['id'] ?? null);
        $amount = $this->integerValue($object['amount'] ?? null);
        $currency = $this->stringValue($object['currency'] ?? null);

        if ($providerDisputeId === null || $amount === null || $currency === null) {
            return;
        }

        RecordPaymentDisputeAction::run(new PaymentDisputeData(
            provider: PaymentProvider::Stripe,
            providerDisputeId: $providerDisputeId,
            status: $this->disputeStatus($object['status'] ?? null),
            amount: $amount,
            currency: $currency,
            providerPaymentIntentId: $this->stringValue($object['payment_intent'] ?? null),
            providerChargeId: $this->stringValue($object['charge'] ?? null),
            reason: $this->stringValue($object['reason'] ?? null),
            isChargeRefundable: (bool) ($object['is_charge_refundable'] ?? false),
            evidenceDueAt: $this->timestamp(data_get($object, 'evidence_details.due_by')),
            providerPayload: $object,
        ));
    }

    private function checkoutStatus(mixed $status): CheckoutSessionStatus
    {
        return match ($status) {
            'open' => CheckoutSessionStatus::Open,
            'complete' => CheckoutSessionStatus::Complete,
            'expired' => CheckoutSessionStatus::Expired,
            default => CheckoutSessionStatus::Unknown,
        };
    }

    private function checkoutMode(mixed $mode): CheckoutMode
    {
        return match ($mode) {
            'subscription' => CheckoutMode::Subscription,
            'setup' => CheckoutMode::Setup,
            default => CheckoutMode::Payment,
        };
    }

    private function paymentPurpose(mixed $purpose): PaymentPurpose
    {
        return match ($purpose) {
            'subscription' => PaymentPurpose::Subscription,
            'donation' => PaymentPurpose::Donation,
            'paid_download' => PaymentPurpose::PaidDownload,
            'gated_access' => PaymentPurpose::GatedAccess,
            'form_payment' => PaymentPurpose::FormPayment,
            default => PaymentPurpose::OneOff,
        };
    }

    private function paymentIntentStatus(mixed $status): PaymentIntentStatus
    {
        return match ($status) {
            'requires_payment_method' => PaymentIntentStatus::RequiresPaymentMethod,
            'requires_confirmation' => PaymentIntentStatus::RequiresConfirmation,
            'requires_action' => PaymentIntentStatus::RequiresAction,
            'processing' => PaymentIntentStatus::Processing,
            'requires_capture' => PaymentIntentStatus::RequiresCapture,
            'canceled' => PaymentIntentStatus::Canceled,
            'succeeded' => PaymentIntentStatus::Succeeded,
            default => PaymentIntentStatus::Unknown,
        };
    }

    private function subscriptionStatus(mixed $status): SubscriptionStatus
    {
        return match ($status) {
            'incomplete' => SubscriptionStatus::Incomplete,
            'incomplete_expired' => SubscriptionStatus::IncompleteExpired,
            'trialing' => SubscriptionStatus::Trialing,
            'active' => SubscriptionStatus::Active,
            'past_due' => SubscriptionStatus::PastDue,
            'canceled' => SubscriptionStatus::Canceled,
            'unpaid' => SubscriptionStatus::Unpaid,
            'paused' => SubscriptionStatus::Paused,
            default => SubscriptionStatus::Unknown,
        };
    }

    private function refundStatus(mixed $status): PaymentRefundStatus
    {
        return match ($status) {
            'pending' => PaymentRefundStatus::Pending,
            'requires_action' => PaymentRefundStatus::RequiresAction,
            'succeeded' => PaymentRefundStatus::Succeeded,
            'failed' => PaymentRefundStatus::Failed,
            'canceled' => PaymentRefundStatus::Canceled,
            default => PaymentRefundStatus::Unknown,
        };
    }

    private function disputeStatus(mixed $status): PaymentDisputeStatus
    {
        return match ($status) {
            'warning_needs_response' => PaymentDisputeStatus::WarningNeedsResponse,
            'warning_under_review' => PaymentDisputeStatus::WarningUnderReview,
            'warning_closed' => PaymentDisputeStatus::WarningClosed,
            'needs_response' => PaymentDisputeStatus::NeedsResponse,
            'under_review' => PaymentDisputeStatus::UnderReview,
            'won' => PaymentDisputeStatus::Won,
            'lost' => PaymentDisputeStatus::Lost,
            default => PaymentDisputeStatus::Unknown,
        };
    }

    private function timestamp(mixed $timestamp): ?CarbonImmutable
    {
        if (! is_int($timestamp)) {
            return null;
        }

        return CarbonImmutable::createFromTimestamp($timestamp);
    }

    private function stringValue(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }

    private function integerValue(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        return is_numeric($value) ? (int) $value : null;
    }

    /**
     * @param  array<string, mixed>  $object
     * @return array<string, mixed>
     */
    private function metadata(array $object): array
    {
        $metadata = $object['metadata'] ?? [];

        return is_array($metadata) ? $metadata : [];
    }
}
