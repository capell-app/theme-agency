<?php

declare(strict_types=1);

namespace Capell\Comments\Support;

use Illuminate\Support\Str;

class CommentBodySanitizer
{
    public function sanitize(string $body): string
    {
        $plainText = trim(strip_tags($body));
        $plainText = preg_replace('/[ \t]+/', ' ', $plainText);
        $plainText = preg_replace('/\R{3,}/', PHP_EOL . PHP_EOL, (string) $plainText);

        return Str::limit((string) $plainText, 5000, '');
    }

    public function linkCount(string $body): int
    {
        preg_match_all('/https?:\/\//i', $body, $matches);

        return count($matches[0]);
    }
}
