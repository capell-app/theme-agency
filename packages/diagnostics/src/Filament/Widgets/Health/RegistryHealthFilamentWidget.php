<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Filament\Widgets\Health;

use Capell\Admin\Contracts\CapellFilamentWidgetContract;
use Capell\Admin\Filament\Concerns\GatedByRoleAndSettings;
use Capell\Diagnostics\Actions\Dashboard\BuildRegistryHealthAction;
use Capell\Diagnostics\Data\Dashboard\RegistryHealthData;
use Filament\Widgets\Widget;
use Livewire\Attributes\Computed;

final class RegistryHealthFilamentWidget extends Widget implements CapellFilamentWidgetContract
{
    use GatedByRoleAndSettings;

    /** @var list<string> */
    protected static array $rolesConfigKeys = ['super_admin'];

    protected static string $settingsKey = 'registry_health';

    protected string $view = 'capell-diagnostics::widgets.registry-health';

    /** @var int|string|array<string, int|null> */
    protected int|string|array $columnSpan = ['md' => 1];

    public static function getDescription(): string
    {
        return (string) __('capell-admin::dashboard.widget_registry_health_description');
    }

    #[Computed(persist: true, seconds: 300)]
    public function data(): RegistryHealthData
    {
        return BuildRegistryHealthAction::run();
    }
}
