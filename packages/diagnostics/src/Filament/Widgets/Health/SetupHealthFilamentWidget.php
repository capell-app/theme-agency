<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Filament\Widgets\Health;

use Capell\Admin\Contracts\CapellFilamentWidgetContract;
use Capell\Admin\Filament\Concerns\GatedByRoleAndSettings;
use Capell\Diagnostics\Actions\Dashboard\BuildSetupHealthAction;
use Capell\Diagnostics\Data\Dashboard\SetupHealthData;
use Filament\Widgets\Widget;
use Livewire\Attributes\Computed;
use Override;

final class SetupHealthFilamentWidget extends Widget implements CapellFilamentWidgetContract
{
    use GatedByRoleAndSettings;

    /** @var list<string> */
    protected static array $rolesConfigKeys = [];

    protected static string $settingsKey = 'setup_health';

    protected string $view = 'capell-diagnostics::widgets.setup-health';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 0;

    #[Override]
    public static function canView(): bool
    {
        if (! self::canViewCheck()) {
            return false;
        }

        return ! BuildSetupHealthAction::run()->allGreen;
    }

    #[Computed(persist: true, seconds: 300)]
    public function data(): SetupHealthData
    {
        return BuildSetupHealthAction::run();
    }
}
