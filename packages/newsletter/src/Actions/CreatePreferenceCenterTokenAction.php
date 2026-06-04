<?php

declare(strict_types=1);

namespace Capell\Newsletter\Actions;

use Capell\Newsletter\Enums\PublicTokenType;
use Capell\Newsletter\Models\PublicToken;
use Capell\Newsletter\Models\Subscriber;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class CreatePreferenceCenterTokenAction
{
    use AsAction;

    public function handle(Subscriber $subscriber): string
    {
        $rawToken = Str::random(64);
        $expiresAt = now()->addHours((int) config('capell-newsletter.public_tokens.token_expiry_hours', 72));

        PublicToken::query()->create([
            'subscriber_id' => $subscriber->getKey(),
            'type' => PublicTokenType::PreferenceCenter,
            'token_hash' => hash('sha256', $rawToken),
            'expires_at' => $expiresAt,
        ]);

        return $rawToken;
    }
}
