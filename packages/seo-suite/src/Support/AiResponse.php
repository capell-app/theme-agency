<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Support;

final class AiResponse
{
    /**
     * @param  array<array-key, mixed>  $metadata
     */
    public function __construct(
        public string $content,
        public int $tokensUsed,
        public string $model,
        public float $duration,
        public array $metadata = [],
    ) {}
}
