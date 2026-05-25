<?php

declare(strict_types=1);

namespace Capell\Comments\Support;

class VisitorHasher
{
    public function hash(?string $value, ?int $siteId = null): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $secret = config('capell-comments.visitor_hash_secret')
            ?: config('app.key')
            ?: 'capell-comments';

        return hash_hmac('sha256', $value, $secret . '|' . $siteId);
    }
}
