<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Filament\Widgets\Health;

use Capell\Admin\Contracts\CapellFilamentWidgetContract;
use Capell\Admin\Filament\Concerns\GatedByRoleAndSettings;
use Capell\Diagnostics\Actions\Dashboard\BuildTailwindBuildStatusAction;
use Capell\Diagnostics\Data\Dashboard\TailwindBuildStatusData;
use Filament\Widgets\Widget;
use Livewire\Attributes\Computed;

final class TailwindBuildStatusFilamentWidget extends Widget implements CapellFilamentWidgetContract
{
    use GatedByRoleAndSettings;

    /** @var list<string> */
    protected static array $rolesConfigKeys = ['super_admin'];

    protected static string $settingsKey = 'tailwind_build_status';

    protected string $view = 'capell-diagnostics::widgets.tailwind-build-status';

    /** @var int|string|array<string, int|null> */
    protected int|string|array $columnSpan = ['md' => 1];

    #[Computed(persist: true, seconds: 300)]
    public function data(): TailwindBuildStatusData
    {
        return BuildTailwindBuildStatusAction::run();
    }
}
