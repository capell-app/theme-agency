<?php

declare(strict_types=1);

namespace Capell\Newsletter\Actions;

use Capell\Newsletter\Data\ConsentEvidenceData;
use Capell\Newsletter\Enums\ConsentEventType;
use Capell\Newsletter\Enums\PublicTokenType;
use Capell\Newsletter\Models\PublicToken;
use Capell\Newsletter\Models\Subscriber;
use Capell\Newsletter\Notifications\ConfirmNewsletterSubscriptionNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static PublicToken run(Subscriber $subscriber, ?ConsentEvidenceData $evidence = null)
 */
class RequestDoubleOptInAction
{
    use AsAction;

    public function handle(Subscriber $subscriber, ?ConsentEvidenceData $evidence = null): PublicToken
    {
        $rawToken = Str::random(64);
        $expiresAt = now()->addHours(config('capell-newsletter.double_opt_in.token_expiry_hours', 72));

        $publicToken = PublicToken::query()->create([
            'subscriber_id' => $subscriber->getKey(),
            'type' => PublicTokenType::Confirm,
            'token_hash' => hash('sha256', $rawToken),
            'expires_at' => $expiresAt,
        ]);

        RecordConsentEventAction::run(
            $subscriber,
            ConsentEventType::DoubleOptInRequested,
            $evidence,
            $subscriber->status,
        );

        Notification::route('mail', $subscriber->email)
            ->notify(new ConfirmNewsletterSubscriptionNotification($rawToken));

        return $publicToken;
    }
}
