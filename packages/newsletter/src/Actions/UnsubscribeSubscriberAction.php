<?php

declare(strict_types=1);

namespace Capell\Newsletter\Actions;

use Capell\Newsletter\Data\ConsentEvidenceData;
use Capell\Newsletter\Enums\ConsentEventType;
use Capell\Newsletter\Enums\PublicTokenType;
use Capell\Newsletter\Enums\SubscriberStatus;
use Capell\Newsletter\Models\PublicToken;
use Capell\Newsletter\Models\Subscriber;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class UnsubscribeSubscriberAction
{
    use AsAction;

    public function handle(string $token, ?ConsentEvidenceData $evidence = null): ?Subscriber
    {
        return DB::transaction(function () use ($token, $evidence): ?Subscriber {
            /** @var PublicToken|null $publicToken */
            $publicToken = PublicToken::query()
                ->where('type', PublicTokenType::Unsubscribe)
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
                'status' => SubscriberStatus::Unsubscribed,
                'unsubscribed_at' => now(),
            ])->save();

            $publicToken->forceFill(['used_at' => now()])->save();

            RecordConsentEventAction::run(
                $subscriber,
                ConsentEventType::Unsubscribed,
                $evidence,
                SubscriberStatus::Unsubscribed,
            );

            QueueProviderSyncAction::run($subscriber);

            $subscriber->refresh();

            SyncNewsletterSubscriberContactAction::run($subscriber);

            return $subscriber;
        });
    }
}
