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
            if (self::$tableExists[$table] ?? false) {
                return true;
            }

            $exists = Schema::hasTable($table);

            if ($exists) {
                self::$tableExists[$table] = true;
            }

            return $exists;
        } catch (Throwable) {
            return false;
        }
    }

    public static function hasColumn(string $table, string $column): bool
    {
        $cacheKey = $table . ':' . $column;

        try {
            if (self::$columnExists[$cacheKey] ?? false) {
                return true;
            }

            $exists = Schema::hasColumn($table, $column);

            if ($exists) {
                self::$columnExists[$cacheKey] = true;
            }

            return $exists;
        } catch (Throwable) {
            return false;
        }
    }
}
