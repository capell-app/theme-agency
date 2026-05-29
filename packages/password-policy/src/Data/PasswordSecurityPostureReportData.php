<?php

declare(strict_types=1);

namespace Capell\PasswordPolicy\Data;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class PasswordSecurityPostureReportData extends Data
{
    /**
     * @param  array<string, string>  $forcedChangeUrls
     */
    public function __construct(
        public bool $forceChangeEnabled,
        public bool $passwordExpiryEnabled,
        public bool $passwordHistoryEnabled,
        public bool $compromisedPasswordChecksEnabled,
        public bool $userColumnsInstalled,
        public bool $historyTableInstalled,
        public array $forcedChangeUrls,
    ) {}
}
