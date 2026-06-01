<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Data;

use Capell\CustomerPortal\Enums\SupportRequestPriority;
use Spatie\LaravelData\Data;

class SupportRequestData extends Data
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        public string $subject,
        public string $message,
        public SupportRequestPriority $priority = SupportRequestPriority::Normal,
        public ?string $requesterEmail = null,
        public ?string $source = null,
        public ?string $externalReference = null,
        public array $context = [],
    ) {}
}
