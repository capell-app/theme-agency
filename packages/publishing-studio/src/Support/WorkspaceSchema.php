<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Support;

use Capell\Core\Models\Page;
use Capell\PublishingStudio\Models\Version;
use Capell\PublishingStudio\Models\Workspace;
use Capell\PublishingStudio\Models\WorkspaceReviewAssignment;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class WorkspaceSchema
{
    /** @var array<string, bool> */
    private static array $tableExists = [];

    /** @var array<string, bool> */
    private static array $columnExists = [];

    public static function isReady(): bool
    {
        try {
            return self::hasWorkspaceTable()
                && self::hasTable((new Version)->getTable())
                && self::hasTable((new WorkspaceReviewAssignment)->getTable())
                && self::hasColumn((new Page)->getTable(), 'workspace_id');
        } catch (Throwable) {
            return false;
        }
    }

    public static function hasWorkspaceTable(): bool
    {
        return self::hasTable((new Workspace)->getTable());
    }

    public static function hasTable(string $table): bool
    {
        try {
            return self::$tableExists[$table] ??= Schema::hasTable($table);
        } catch (Throwable) {
            return self::$tableExists[$table] = false;
        }
    }

    public static function hasColumn(string $table, string $column): bool
    {
        $cacheKey = $table . ':' . $column;

        try {
            return self::$columnExists[$cacheKey] ??= Schema::hasColumn($table, $column);
        } catch (Throwable) {
            return self::$columnExists[$cacheKey] = false;
        }
    }
}
