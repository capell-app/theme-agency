<?php

declare(strict_types=1);

namespace Capell\Comments\Support;

use Illuminate\Support\Str;

class CommentBodySanitizer
{
    public function sanitize(string $body): string
    {
        $plainText = trim(strip_tags($body));
        $plainText = (string) preg_replace('/\p{Cf}+/u', '', $plainText);
        $plainText = (string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', '', $plainText);
        $plainText = preg_replace('/[ \t]+/', ' ', $plainText);
        $plainText = preg_replace('/\R{3,}/', PHP_EOL . PHP_EOL, (string) $plainText);

        return Str::limit((string) $plainText, 5000, '');
    }

    public function linkCount(string $body): int
    {
        preg_match_all(
            '/(?:https?:\/\/|www\.)[^\s<]+|(?<![@\w.-])(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}(?::\d{2,5})?(?:\/[^\s<]*)?/iu',
            $body,
            $matches,
        );

        return count($matches[0]);
    }
}
