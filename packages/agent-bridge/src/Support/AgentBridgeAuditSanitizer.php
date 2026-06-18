<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Support;

final class AgentBridgeAuditSanitizer
{
    private const string REDACTED = '[redacted]';

    /**
     * Opaque strings at or beyond this length are treated as potential secrets and masked,
     * even when they live under an unrecognised key. Normal prose and short identifiers
     * (names, slugs, ids) stay well below this threshold.
     */
    private const int OPAQUE_STRING_LENGTH_THRESHOLD = 80;

    /**
     * @param  array<array-key, mixed>  $payload
     * @return array<array-key, mixed>
     */
    public function sanitize(array $payload): array
    {
        return $this->sanitizeArray($payload);
    }

    /**
     * @param  array<array-key, mixed>  $payload
     * @return array<array-key, mixed>
     */
    private function sanitizeArray(array $payload): array
    {
        if ($this->isPrivatePromptPayload($payload)) {
            return [self::REDACTED];
        }

        foreach ($payload as $key => $value) {
            if ($this->isSensitiveKey($key)) {
                $payload[$key] = self::REDACTED;

                continue;
            }

            $payload[$key] = $this->sanitizeValue($value);
        }

        return $payload;
    }

    private function sanitizeValue(mixed $value): mixed
    {
        if (is_array($value)) {
            return $this->sanitizeArray($value);
        }

        if (is_string($value) && $this->isSensitiveString($value)) {
            return self::REDACTED;
        }

        if (is_string($value) && $this->isOpaqueSecretLikeString($value)) {
            return $this->maskOpaqueString($value);
        }

        return $value;
    }

    /**
     * A long, single-token string with no whitespace is almost never legitimate audit prose:
     * it is far more likely to be an API key, JWT, signed URL, or other opaque secret that
     * slipped through under an unrecognised key. Such values are masked as a backstop so the
     * allow-by-default key/string matching cannot leak cleartext secrets.
     */
    private function isOpaqueSecretLikeString(string $value): bool
    {
        $trimmed = trim($value);

        if (mb_strlen($trimmed) < self::OPAQUE_STRING_LENGTH_THRESHOLD) {
            return false;
        }

        return ! str_contains($trimmed, ' ') && ! str_contains($trimmed, "\n");
    }

    /**
     * Replace an opaque secret-like string with a stable, non-reversible fingerprint so audit
     * entries remain correlatable across requests without persisting the cleartext value.
     */
    private function maskOpaqueString(string $value): string
    {
        return self::REDACTED . ':' . substr(hash('sha256', $value), 0, 12);
    }

    private function isSensitiveKey(mixed $key): bool
    {
        if (! is_string($key) && ! is_int($key)) {
            return false;
        }

        $normalized = $this->normalizeKey((string) $key);

        if (in_array($normalized, [
            'authorization',
            'api_key',
            'bearer',
            'client_secret',
            'password',
            'plain_text_token',
            'private_key',
            'secret',
            'signed_admin_url',
            'signed_url',
            'token',
            'token_hash',
        ], true)) {
            return true;
        }

        if (str_ends_with($normalized, '_password')
            || str_ends_with($normalized, '_secret')
            || str_ends_with($normalized, '_api_key')
            || str_ends_with($normalized, '_private_key')
            || str_ends_with($normalized, '_secret_key')
        ) {
            return true;
        }

        return str_ends_with($normalized, '_token') && ! str_ends_with($normalized, '_token_id');
    }

    private function isSensitiveString(string $value): bool
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            return false;
        }

        if (str_starts_with(strtolower($trimmed), 'bearer ')) {
            return true;
        }

        $tokenPrefix = config('capell-agent-bridge.token_prefix', 'cagent-bridge_');

        if (is_string($tokenPrefix) && str_starts_with($trimmed, $tokenPrefix)) {
            return true;
        }

        return $this->isSignedAdminUrl($trimmed);
    }

    private function isSignedAdminUrl(string $value): bool
    {
        $parts = parse_url($value);

        if (! is_array($parts)) {
            return false;
        }

        $path = (string) ($parts['path'] ?? '');
        $query = (string) ($parts['query'] ?? '');

        if (! str_contains($path, '/admin') || $query === '') {
            return false;
        }

        parse_str($query, $parameters);

        return array_key_exists('signature', $parameters);
    }

    /**
     * @param  array<array-key, mixed>  $payload
     */
    private function isPrivatePromptPayload(array $payload): bool
    {
        $hasPrompt = false;
        $isPrivate = false;

        foreach ($payload as $key => $value) {
            $normalized = $this->normalizeKey((string) $key);

            if (in_array($normalized, ['prompt', 'system_prompt', 'user_prompt'], true)) {
                $hasPrompt = true;
            }

            if (in_array($normalized, ['private', 'is_private', 'private_prompt'], true) && $value === true) {
                $isPrivate = true;
            }
        }

        return $hasPrompt && $isPrivate;
    }

    private function normalizeKey(string $key): string
    {
        $key = preg_replace('/(?<!^)[A-Z]/', '_$0', $key) ?? $key;

        return strtolower(str_replace(['-', ' '], '_', $key));
    }
}
