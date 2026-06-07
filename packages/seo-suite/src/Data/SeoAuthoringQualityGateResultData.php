<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Data;

use Capell\SeoSuite\Enums\SeoCheckKeyEnum;
use Capell\SeoSuite\Enums\SeoCheckModeEnum;
use Spatie\LaravelData\Data;

final class SeoAuthoringQualityGateResultData extends Data
{
    public function __construct(
        public SeoCheckKeyEnum $key,
        public SeoCheckModeEnum $mode,
        public bool $passed,
        public string $message,
        public ?string $fieldPath = null,
    ) {}

    public function blocks(): bool
    {
        return ! $this->passed && $this->mode === SeoCheckModeEnum::Blocker;
    }

    public function warns(): bool
    {
        return ! $this->passed && $this->mode === SeoCheckModeEnum::Warning;
    }
}
