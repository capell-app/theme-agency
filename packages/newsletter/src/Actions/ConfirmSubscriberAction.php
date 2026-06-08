<?php

declare(strict_types=1);

namespace Capell\Newsletter\Actions;

use Capell\Newsletter\Data\ConsentEvidenceData;
use Capell\Newsletter\Enums\ConsentEventType;
use Capell\Newsletter\Enums\PublicTokenType;
use Capell\Newsletter\Enums\SubscriberStatus;
use Capell\Newsletter\Events\SubscriberConfirmed;
use Capell\Newsletter\Models\PublicToken;
use Capell\Newsletter\Models\Subscriber;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class ConfirmSubscriberAction
{
    use AsAction;

    public function handle(string $token, ?ConsentEvidenceData $evidence = null): ?Subscriber
    {
        return DB::transaction(function () use ($token, $evidence): ?Subscriber {
            /** @var PublicToken|null $publicToken */
            $publicToken = PublicToken::query()
                ->where('type', PublicTokenType::Confirm)
                ->where('token_hash', hash('sha256', $token))
                ->lockForUpdate()
                ->first();

            if (! $publicToken instanceof PublicToken || ! $publicToken->isUsable()) {
                return null;
            }

            $subscriber = $publicToken->subscriber;
            if (! $subscriber instanceof Subscriber) {
                return null;
            }

            $subscriber->forceFill([
                'status' => SubscriberStatus::Subscribed,
                'confirmed_at' => now(),
                'subscribed_at' => now(),
            ])->save();

            $publicToken->forceFill(['used_at' => now()])->save();

            RecordConsentEventAction::run(
                $subscriber,
                ConsentEventType::DoubleOptInConfirmed,
                $evidence,
                SubscriberStatus::Subscribed,
            );

            QueueProviderSyncAction::run($subscriber);

            $subscriber->refresh();

            SyncNewsletterSubscriberContactAction::run($subscriber);

            event(new SubscriberConfirmed($subscriber));

            return $subscriber;
        });
    }
}
