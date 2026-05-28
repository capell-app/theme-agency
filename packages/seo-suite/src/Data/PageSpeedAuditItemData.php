<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Data;

use Spatie\LaravelData\Data;

final class PageSpeedAuditItemData extends Data
{
    public function __construct(
        public readonly string $key,
        public readonly string $title,
        public readonly ?string $description = null,
        public readonly ?string $displayValue = null,
        public readonly ?int $score = null,
        public readonly ?float $numericValue = null,
    ) {}

    /**
     * @return array<string, int|float|string|null>
     */
    public function compact(): array
    {
        return [
            'key' => $this->key,
            'title' => $this->title,
            'description' => $this->description,
            'display_value' => $this->displayValue,
            'score' => $this->score,
            'numeric_value' => $this->numericValue,
        ];
    }
}
