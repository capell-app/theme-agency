<?php

declare(strict_types=1);

namespace Capell\Newsletter\Actions;

use Capell\Newsletter\Enums\PublicTokenType;
use Capell\Newsletter\Models\PublicToken;
use Capell\Newsletter\Models\Subscriber;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static string run(Subscriber $subscriber)
 */
class CreatePreferenceCenterTokenAction
{
    use AsAction;

    public function handle(Subscriber $subscriber): string
    {
        $rawToken = Str::random(64);
        $expiresAt = now()->addHours($this->integerConfig('capell-newsletter.public_tokens.token_expiry_hours', 72));

        PublicToken::query()->create([
            'subscriber_id' => $subscriber->getKey(),
            'type' => PublicTokenType::PreferenceCenter,
            'token_hash' => hash('sha256', $rawToken),
            'expires_at' => $expiresAt,
        ]);

        return $rawToken;
    }

    private function integerConfig(string $key, int $fallback): int
    {
        $value = config($key, $fallback);

        if (is_int($value)) {
            return $value;
        }

        return is_string($value) && is_numeric($value) ? (int) $value : $fallback;
    }
}
