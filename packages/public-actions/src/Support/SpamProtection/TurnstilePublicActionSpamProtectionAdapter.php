<?php

declare(strict_types=1);

namespace Capell\PublicActions\Support\SpamProtection;

use Capell\PublicActions\Contracts\PublicActionSpamProtectionAdapter;
use Capell\PublicActions\Data\PublicActionSpamProtectionResultData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

final class TurnstilePublicActionSpamProtectionAdapter implements PublicActionSpamProtectionAdapter
{
    /**
     * @param  array<string, mixed>  $input
     */
    public function check(array $input, ?Request $request = null): PublicActionSpamProtectionResultData
    {
        $secret = config('capell-public-actions.spam_protection.turnstile.secret');

        if (! is_string($secret) || $secret === '') {
            return new PublicActionSpamProtectionResultData(
                passed: false,
                field: 'cf-turnstile-response',
                message: __('capell-public-actions::generic.spam_detected'),
            );
        }

        $token = $input['cf-turnstile-response'] ?? null;

        if (! is_string($token) || $token === '') {
            return new PublicActionSpamProtectionResultData(
                passed: false,
                field: 'cf-turnstile-response',
                message: __('capell-public-actions::generic.spam_detected'),
            );
        }

        $response = Http::asForm()
            ->timeout(5)
            ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => $secret,
                'response' => $token,
                'remoteip' => $request?->ip(),
            ]);

        return new PublicActionSpamProtectionResultData(
            passed: $response->ok() && $response->json('success') === true,
            field: 'cf-turnstile-response',
            message: __('capell-public-actions::generic.spam_detected'),
        );
    }
}
