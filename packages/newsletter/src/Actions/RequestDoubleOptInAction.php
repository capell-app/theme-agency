<?php

declare(strict_types=1);

namespace Capell\Newsletter\Actions;

use Capell\Newsletter\Data\ConsentEvidenceData;
use Capell\Newsletter\Enums\ConsentEventType;
use Capell\Newsletter\Enums\PublicTokenType;
use Capell\Newsletter\Enums\SubscriberStatus;
use Capell\Newsletter\Models\PublicToken;
use Capell\Newsletter\Models\Subscriber;
use Capell\Newsletter\Notifications\ConfirmNewsletterSubscriptionNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static PublicToken|null run(Subscriber $subscriber, ?ConsentEvidenceData $evidence = null)
 */
final class RequestDoubleOptInAction
{
    use AsAction;

    /**
     * Mint a confirmation token and send a double opt-in email for a newly-Pending
     * subscriber. Returns null (no email sent) when the subscriber is not Pending,
     * or when an unused confirmation token was issued within the resend cooldown —
     * in which case the existing outstanding token is reused rather than reissued.
     */
    public function handle(Subscriber $subscriber, ?ConsentEvidenceData $evidence = null): ?PublicToken
    {
        // Only a genuinely Pending subscriber should ever receive a confirmation email.
        // An already-confirmed (or suppressed/unsubscribed) subscriber must not be
        // emailed a fresh double opt-in by an anonymous public request.
        if ($subscriber->status !== SubscriberStatus::Pending) {
            return null;
        }

        $existingUsableToken = $this->existingUsableConfirmToken($subscriber);

        if ($existingUsableToken instanceof PublicToken && $this->withinResendCooldown($existingUsableToken)) {
            // A valid confirmation token was issued very recently: reuse it silently
            // instead of minting a new one and re-sending, to prevent email bombing.
            return $existingUsableToken;
        }

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

    private function existingUsableConfirmToken(Subscriber $subscriber): ?PublicToken
    {
        /** @var PublicToken|null $publicToken */
        $publicToken = $subscriber->publicTokens()
            ->where('type', PublicTokenType::Confirm)
            ->whereNull('used_at')
            ->where(function ($query): void {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->latest('created_at')
            ->first();

        return $publicToken;
    }

    private function withinResendCooldown(PublicToken $publicToken): bool
    {
        $cooldownMinutes = (int) config('capell-newsletter.double_opt_in.resend_cooldown_minutes', 10);

        if ($cooldownMinutes <= 0) {
            return false;
        }

        $createdAt = $publicToken->getAttribute('created_at');

        if ($createdAt === null) {
            return false;
        }

        return $createdAt->copy()->addMinutes($cooldownMinutes)->isFuture();
    }
}
