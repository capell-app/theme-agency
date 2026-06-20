<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Filament\Widgets\Health;

use Capell\Admin\Contracts\CapellFilamentWidgetContract;
use Capell\Admin\Filament\Concerns\GatedByRoleAndSettings;
use Capell\Diagnostics\Actions\Dashboard\BuildMigrationsHealthAction;
use Capell\Diagnostics\Data\Dashboard\MigrationsHealthData;
use Filament\Widgets\Widget;
use Livewire\Attributes\Computed;

final class MigrationsHealthFilamentWidget extends Widget implements CapellFilamentWidgetContract
{
    use GatedByRoleAndSettings;

    /** @var list<string> */
    protected static array $rolesConfigKeys = ['super_admin'];

    protected static string $settingsKey = 'migrations_health';

    protected string $view = 'capell-diagnostics::widgets.migrations-health';

    /** @var int|string|array<string, int|null> */
    protected int|string|array $columnSpan = ['md' => 1];

    public static function getDescription(): string
    {
        return (string) __('capell-admin::dashboard.widget_migrations_health_description');
    }

    #[Computed(persist: true, seconds: 300)]
    public function data(): MigrationsHealthData
    {
        return BuildMigrationsHealthAction::run();
    }
}
