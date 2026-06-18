<?php

declare(strict_types=1);

namespace Capell\Newsletter\Actions;

use Capell\Newsletter\Data\ConsentEvidenceData;
use Capell\Newsletter\Data\SubscriberData;
use Capell\Newsletter\Enums\ConsentEventType;
use Capell\Newsletter\Enums\ResubscribePolicy;
use Capell\Newsletter\Enums\SubscriberStatus;
use Capell\Newsletter\Models\Subscriber;
use Capell\Newsletter\Support\NewsletterSettingsResolver;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static Subscriber run(SubscriberData $data, ?ConsentEvidenceData $evidence = null, ConsentEventType $eventType = ConsentEventType::FormCapture, bool $recordConsentEvent = true)
 */
class UpsertSubscriberAction
{
    use AsAction;

    public function handle(
        SubscriberData $data,
        ?ConsentEvidenceData $evidence = null,
        ConsentEventType $eventType = ConsentEventType::FormCapture,
        bool $recordConsentEvent = true,
    ): Subscriber {
        return DB::transaction(function () use ($data, $evidence, $eventType, $recordConsentEvent): Subscriber {
            /** @var Subscriber|null $subscriber */
            $subscriber = Subscriber::query()
                ->forEmail($data->siteId, $data->email)
                ->lockForUpdate()
                ->first();

            $status = $this->resolveStatus($subscriber, $data->status, $evidence);
            $timestampAttributes = $this->timestampAttributes($status);

            if (! $subscriber instanceof Subscriber) {
                $subscriber = new Subscriber;
                $subscriber->email_hash = Subscriber::emailHash($data->email);
                $subscriber->pending_at = $status === SubscriberStatus::Pending ? now() : null;
            }

            $subscriber->forceFill(array_merge([
                'site_id' => $data->siteId,
                'email' => trim($data->email),
                'first_name' => $data->firstName,
                'last_name' => $data->lastName,
                'profile' => $data->profile,
                'status' => $status,
                'source_form_id' => $data->sourceFormId,
                'source_form_handle' => $data->sourceFormHandle,
            ], $timestampAttributes))->save();

            if ($recordConsentEvent && $evidence instanceof ConsentEvidenceData) {
                RecordConsentEventAction::run($subscriber, $eventType, $evidence, $status);
            }

            $subscriber = $subscriber->refresh();

            SyncNewsletterSubscriberContactAction::run($subscriber);

            return $subscriber;
        });
    }

    private function resolveStatus(
        ?Subscriber $existingSubscriber,
        SubscriberStatus $requestedStatus,
        ?ConsentEvidenceData $evidence,
    ): SubscriberStatus {
        // Never downgrade an already-confirmed subscriber to Pending. A public,
        // anonymous request (which always asks for Pending under double opt-in) must
        // not be able to silently strip a confirmed subscriber from sends and
        // re-trigger a double opt-in email. Keep them Subscribed.
        if (
            $requestedStatus === SubscriberStatus::Pending
            && $existingSubscriber instanceof Subscriber
            && $existingSubscriber->status === SubscriberStatus::Subscribed
        ) {
            return SubscriberStatus::Subscribed;
        }

        if ($requestedStatus !== SubscriberStatus::Subscribed) {
            return $requestedStatus;
        }

        if (! $existingSubscriber instanceof Subscriber) {
            return $evidence instanceof ConsentEvidenceData ? SubscriberStatus::Subscribed : SubscriberStatus::Pending;
        }

        if ($existingSubscriber->status->isGloballySuppressed()) {
            return $existingSubscriber->status;
        }

        // An already-confirmed subscriber must never be silently downgraded by an
        // anonymous public request that arrives without consent evidence. Doing so
        // would remove a confirmed subscriber from sends and re-trigger double opt-in.
        if ($existingSubscriber->status === SubscriberStatus::Subscribed) {
            return SubscriberStatus::Subscribed;
        }

        // An existing Pending subscriber stays Pending unless genuine consent evidence
        // upgrades them. Repeated anonymous requests must not reset their established state.
        if ($existingSubscriber->status === SubscriberStatus::Pending) {
            return $evidence instanceof ConsentEvidenceData ? SubscriberStatus::Subscribed : SubscriberStatus::Pending;
        }

        if (! in_array($existingSubscriber->status, [
            SubscriberStatus::Unsubscribed,
            SubscriberStatus::Suppressed,
            SubscriberStatus::Bounced,
            SubscriberStatus::Complained,
        ], true)) {
            return $evidence instanceof ConsentEvidenceData ? SubscriberStatus::Subscribed : SubscriberStatus::Pending;
        }

        return match ($this->resubscribePolicy($existingSubscriber->site_id)) {
            ResubscribePolicy::AllowWithConsent => $evidence instanceof ConsentEvidenceData
                ? SubscriberStatus::Subscribed
                : SubscriberStatus::Pending,
            ResubscribePolicy::BlockSuppressedOnly => in_array($existingSubscriber->status, [
                SubscriberStatus::Suppressed,
                SubscriberStatus::Bounced,
                SubscriberStatus::Complained,
            ], true)
                ? $existingSubscriber->status
                : ($evidence instanceof ConsentEvidenceData ? SubscriberStatus::Subscribed : SubscriberStatus::Pending),
            ResubscribePolicy::RequireDoubleOptIn => SubscriberStatus::Pending,
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function timestampAttributes(SubscriberStatus $status): array
    {
        return match ($status) {
            SubscriberStatus::Pending => ['pending_at' => now()],
            SubscriberStatus::Subscribed => ['subscribed_at' => now()],
            SubscriberStatus::Unsubscribed => ['unsubscribed_at' => now()],
            SubscriberStatus::Suppressed => ['suppressed_at' => now()],
            SubscriberStatus::Bounced => ['bounced_at' => now()],
            SubscriberStatus::Complained => ['complained_at' => now()],
        };
    }

    private function resubscribePolicy(int $siteId): ResubscribePolicy
    {
        return resolve(NewsletterSettingsResolver::class)->resubscribePolicyForSite($siteId);
    }
}
