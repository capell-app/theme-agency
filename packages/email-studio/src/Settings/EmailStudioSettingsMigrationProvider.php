<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Settings;

final class EmailStudioSettingsMigrationProvider
{
    /**
     * @return list<string>
     */
    public function getSettingMigrations(): array
    {
        return [
            '2026_06_05_000001_create_email_studio_settings',
            '2026_06_13_000001_add_email_template_authoring_settings',
        ];
    }

    /**
     * @return list<string>
     */
    public function migrations(): array
    {
        return $this->getSettingMigrations();
    }

    public function path(): string
    {
        return dirname(__DIR__, 2) . '/database/settings';
    }
}
