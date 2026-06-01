<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Enums;

enum DiagnosticsPermission: string
{
    case AccessDiagnostics = 'accessDiagnostics';
    case ViewDiagnostics = 'viewDiagnostics';
    case ViewPermissionAuditPage = 'View:PermissionAuditPage';
    case ViewCommandPalettePage = 'View:CommandPalettePage';
    case ViewQueueHealthPage = 'View:QueueHealthPage';
    case ManageQueueHealthPage = 'Manage:QueueHealthPage';

    /**
     * @return list<string>
     */
    public static function names(): array
    {
        return array_map(
            fn (self $permission): string => $permission->value,
            self::cases(),
        );
    }
}
