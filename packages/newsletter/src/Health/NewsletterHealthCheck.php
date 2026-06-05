<?php

declare(strict_types=1);

namespace Capell\Newsletter\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Newsletter\Actions\BuildNewsletterHealthDiagnosticsAction;
use Illuminate\Support\Collection;

final class NewsletterHealthCheck implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    /**
     * @return Collection<int, DoctorCheckResultData>
     */
    public static function runDiagnostics(?string $key = null): Collection
    {
        return BuildNewsletterHealthDiagnosticsAction::run($key);
    }

    public static function passed(): bool
    {
        return self::runDiagnostics()
            ->every(static fn (DoctorCheckResultData $result): bool => $result->passed);
    }
}
