<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Data\Health;

use Capell\Diagnostics\Enums\HealthCheckImplementationStatus;
use Spatie\LaravelData\Data;

/**
 * The outcome of resolving and (where possible) executing a single declared
 * health-check entry from a package manifest.
 */
final class HealthCheckResultData extends Data
{
    /**
     * @param  list<string>  $coverage
     */
    public function __construct(
        public readonly string $packageName,
        public readonly string $key,
        public readonly string $label,
        public readonly string $className,
        public readonly string $severity,
        public readonly string $surface,
        public readonly array $coverage,
        public readonly HealthCheckImplementationStatus $implementationStatus,
        public readonly ?bool $passed = null,
        public readonly ?string $message = null,
    ) {}

    /**
     * Whether this declared check actually executed and produced a pass/fail outcome.
     */
    public function wasExecuted(): bool
    {
        return $this->implementationStatus === HealthCheckImplementationStatus::Implemented
            && $this->passed !== null;
    }

    /**
     * Whether this declared check executed and reported a failure.
     */
    public function failed(): bool
    {
        return $this->wasExecuted() && $this->passed === false;
    }
}
