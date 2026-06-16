<?php

declare(strict_types=1);

namespace Capell\PublicActions\Support\SpamProtection;

use Capell\PublicActions\Contracts\PublicActionSpamProtectionAdapter;
use Capell\PublicActions\Data\PublicActionSpamProtectionResultData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

final class ReCaptchaPublicActionSpamProtectionAdapter implements PublicActionSpamProtectionAdapter
{
    /**
     * @param  array<string, mixed>  $input
     */
    public function check(array $input, ?Request $request = null): PublicActionSpamProtectionResultData
    {
        $secret = config('capell-public-actions.spam_protection.recaptcha.secret');

        if (! is_string($secret) || $secret === '') {
            return $this->failed();
        }

        $token = $input['g-recaptcha-response'] ?? null;

        if (! is_string($token) || $token === '') {
            return $this->failed();
        }

        $response = Http::asForm()
            ->timeout(5)
            ->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secret,
                'response' => $token,
                'remoteip' => $request?->ip(),
            ]);

        if (! $response->ok() || $response->json('success') !== true) {
            return $this->failed();
        }

        if ($this->score($response->json('score')) < $this->minimumScore()) {
            return $this->failed();
        }

        $expectedAction = config('capell-public-actions.spam_protection.recaptcha.action');

        if (is_string($expectedAction) && $expectedAction !== '' && $response->json('action') !== $expectedAction) {
            return $this->failed();
        }

        return new PublicActionSpamProtectionResultData(passed: true);
    }

    private function failed(): PublicActionSpamProtectionResultData
    {
        return new PublicActionSpamProtectionResultData(
            passed: false,
            field: 'g-recaptcha-response',
            message: __('capell-public-actions::generic.spam_detected'),
        );
    }

    private function score(mixed $score): float
    {
        if (is_float($score) || is_int($score)) {
            return (float) $score;
        }

        if (is_string($score) && is_numeric($score)) {
            return (float) $score;
        }

        return 0.0;
    }

    private function minimumScore(): float
    {
        $score = config('capell-public-actions.spam_protection.recaptcha.minimum_score', 0.5);

        if (is_float($score) || is_int($score)) {
            return (float) $score;
        }

        return is_string($score) && is_numeric($score) ? (float) $score : 0.5;
    }
}
