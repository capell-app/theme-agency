<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Data;

use Spatie\LaravelData\Data;

final class PrivacyExportData extends Data
{
    /**
     * @param  array<int, array<string, mixed>>  $consentRecords
     * @param  array<int, array<string, mixed>>  $policyAcceptances
     * @param  array<int, array<string, mixed>>  $privacyRequests
     */
    public function __construct(
        public string $subjectType,
        public string|int|null $subjectId,
        public array $consentRecords,
        public array $policyAcceptances,
        public array $privacyRequests,
    ) {}

    public function isEmpty(): bool
    {
        return $this->consentRecords === []
            && $this->policyAcceptances === []
            && $this->privacyRequests === [];
    }
}
