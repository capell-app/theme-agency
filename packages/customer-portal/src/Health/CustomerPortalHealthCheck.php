<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\CustomerPortal\Models\PortalAccount;
use Capell\CustomerPortal\Models\PortalSupportRequest;
use Capell\CustomerPortal\Models\PortalSupportRequestReply;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class CustomerPortalHealthCheck implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    /**
     * @return Collection<int, DoctorCheckResultData>
     */
    public static function runDiagnostics(): Collection
    {
        return collect([
            self::checkRequiredTables(),
            self::checkModelsResolvable(),
        ]);
    }

    public static function passed(): bool
    {
        return self::runDiagnostics()
            ->every(fn (DoctorCheckResultData $result): bool => $result->passed);
    }

    private static function checkRequiredTables(): DoctorCheckResultData
    {
        $requiredTables = [
            (new PortalAccount)->getTable(),
            (new PortalSupportRequest)->getTable(),
            (new PortalSupportRequestReply)->getTable(),
        ];

        try {
            $missingTables = collect($requiredTables)
                ->reject(fn (string $table): bool => Schema::hasTable($table));
        } catch (Throwable $throwable) {
            return new DoctorCheckResultData(
                label: 'Customer Portal database tables',
                passed: false,
                message: 'Unable to check Customer Portal database tables.',
                remediation: $throwable->getMessage(),
            );
        }

        if ($missingTables->isNotEmpty()) {
            return new DoctorCheckResultData(
                label: 'Customer Portal database tables',
                passed: false,
                message: 'Missing tables: ' . $missingTables->implode(', ') . '.',
                remediation: 'Run: php artisan migrate',
            );
        }

        return new DoctorCheckResultData(
            label: 'Customer Portal database tables',
            passed: true,
            message: 'Required portal_accounts, portal_support_requests, and portal_support_request_replies tables are present.',
        );
    }

    private static function checkModelsResolvable(): DoctorCheckResultData
    {
        try {
            new PortalAccount;
            new PortalSupportRequest;
            new PortalSupportRequestReply;
        } catch (Throwable $throwable) {
            return new DoctorCheckResultData(
                label: 'Customer Portal models',
                passed: false,
                message: 'Unable to resolve Customer Portal models.',
                remediation: $throwable->getMessage(),
            );
        }

        return new DoctorCheckResultData(
            label: 'Customer Portal models',
            passed: true,
            message: 'PortalAccount, PortalSupportRequest, and PortalSupportRequestReply models are resolvable.',
        );
    }
}
