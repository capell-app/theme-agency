<?php

declare(strict_types=1);

namespace Capell\PublicActions\Support\SpamProtection;

use Capell\PublicActions\Contracts\PublicActionSpamProtectionAdapter;
use Capell\PublicActions\Data\PublicActionSpamProtectionResultData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

final class HCaptchaPublicActionSpamProtectionAdapter implements PublicActionSpamProtectionAdapter
{
    /**
     * @param  array<string, mixed>  $input
     */
    public function check(array $input, ?Request $request = null): PublicActionSpamProtectionResultData
    {
        $secret = config('capell-public-actions.spam_protection.hcaptcha.secret');

        if (! is_string($secret) || $secret === '') {
            return $this->failed();
        }

        $token = $input['h-captcha-response'] ?? null;

        if (! is_string($token) || $token === '') {
            return $this->failed();
        }

        $response = Http::asForm()
            ->timeout(5)
            ->post('https://hcaptcha.com/siteverify', [
                'secret' => $secret,
                'response' => $token,
                'remoteip' => $request?->ip(),
            ]);

        return $response->ok() && $response->json('success') === true
            ? new PublicActionSpamProtectionResultData(passed: true)
            : $this->failed();
    }

    private function failed(): PublicActionSpamProtectionResultData
    {
        return new PublicActionSpamProtectionResultData(
            passed: false,
            field: 'h-captcha-response',
            message: __('capell-public-actions::generic.spam_detected'),
        );
    }
}
