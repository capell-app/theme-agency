<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Data;

use Capell\PrivacyCenter\Enums\PrivacyRequestType;
use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;

final class PrivacyRequestData extends Data
{
    /**
     * @param  array<string, mixed>  $workflowPayload
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public PrivacyRequestType $type,
        public ?int $siteId = null,
        public ?string $email = null,
        public ?CarbonInterface $submittedAt = null,
        public ?CarbonInterface $dueAt = null,
        public array $workflowPayload = [],
        public array $metadata = [],
    ) {}
}
