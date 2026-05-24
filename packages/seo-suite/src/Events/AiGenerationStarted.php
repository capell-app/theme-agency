<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Events;

class AiGenerationStarted
{
    /**
     * @param  array<array-key, mixed>  $args
     */
    public function __construct(public string $actionClass, public array $args) {}
}
