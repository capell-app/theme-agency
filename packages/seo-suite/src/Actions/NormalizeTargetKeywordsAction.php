<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Actions;

use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static list<string> run(mixed $keywords)
 */
final class NormalizeTargetKeywordsAction
{
    use AsAction;

    /**
     * @return list<string>
     */
    public function handle(mixed $keywords): array
    {
        if (is_array($keywords)) {
            $rawTerms = $keywords;
        } elseif (is_string($keywords)) {
            $rawTerms = preg_split('/[,\r\n]+/', $keywords) ?: [];
        } else {
            return [];
        }

        $normalized = [];

        foreach ($rawTerms as $term) {
            if (! is_scalar($term)) {
                continue;
            }

            $keyword = trim((string) $term);
            $keyword = preg_replace('/\s+/', ' ', $keyword) ?? $keyword;
            $keyword = trim(mb_strtolower($keyword));

            if ($keyword === '') {
                continue;
            }

            $normalized[$keyword] = $keyword;
        }

        return array_values($normalized);
    }
}
