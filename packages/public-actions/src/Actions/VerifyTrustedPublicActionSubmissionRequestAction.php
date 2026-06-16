<?php

declare(strict_types=1);

namespace Capell\PublicActions\Actions;

use Capell\PublicActions\Models\PublicAction;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

final class VerifyTrustedPublicActionSubmissionRequestAction
{
    use AsAction;

    public function handle(PublicAction $action, Request $request): bool
    {
        $secret = $this->secret($action);

        if ($secret === null) {
            return false;
        }

        if ($this->hasValidBearerToken($request, $secret)) {
            return true;
        }

        return $this->hasValidSignature($request, $secret);
    }

    private function secret(PublicAction $action): ?string
    {
        $settingsSecret = data_get($action->settings, 'trusted_submission_secret');

        if (is_string($settingsSecret) && $settingsSecret !== '') {
            return $settingsSecret;
        }

        $configuredSecret = data_get(config('capell-public-actions.trusted_submissions.secrets', []), $action->key);

        return is_string($configuredSecret) && $configuredSecret !== '' ? $configuredSecret : null;
    }

    private function hasValidBearerToken(Request $request, string $secret): bool
    {
        $token = $request->bearerToken();

        return is_string($token) && hash_equals($secret, $token);
    }

    private function hasValidSignature(Request $request, string $secret): bool
    {
        $timestamp = $request->header('X-Capell-Timestamp');
        $signature = $this->normalizedSignature($request->header('X-Capell-Signature'));

        if (! is_string($timestamp) || ! ctype_digit($timestamp) || $signature === null) {
            return false;
        }

        if (! $this->timestampIsFresh((int) $timestamp)) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp . '.' . $request->getContent(), $secret);

        return hash_equals($expected, $signature);
    }

    private function normalizedSignature(?string $signature): ?string
    {
        if (! is_string($signature) || $signature === '') {
            return null;
        }

        if (str_starts_with($signature, 'sha256=')) {
            $signature = substr($signature, 7);
        }

        return preg_match('/^[a-f0-9]{64}$/i', $signature) === 1 ? strtolower($signature) : null;
    }

    private function timestampIsFresh(int $timestamp): bool
    {
        $tolerance = config('capell-public-actions.trusted_submissions.timestamp_tolerance_seconds', 300);
        $tolerance = is_numeric($tolerance) && (int) $tolerance > 0 ? (int) $tolerance : 300;

        return abs(now()->timestamp - $timestamp) <= $tolerance;
    }
}
