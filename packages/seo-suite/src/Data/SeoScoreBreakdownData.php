<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Data;

use Spatie\LaravelData\Data;

class SeoScoreBreakdownData extends Data
{
    /**
     * @param  list<SeoScoreCategoryData>  $categories
     */
    public function __construct(
        public readonly int $score,
        public readonly array $categories,
    ) {}

    public function category(string $key): ?SeoScoreCategoryData
    {
        foreach ($this->categories as $category) {
            if ($category->key === $key) {
                return $category;
            }
        }

        return null;
    }
}
