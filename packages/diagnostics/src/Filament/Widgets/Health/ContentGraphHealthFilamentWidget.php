<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Filament\Widgets\Health;

use Capell\Admin\Contracts\CapellFilamentWidgetContract;
use Capell\Admin\Filament\Concerns\GatedByRoleAndSettings;
use Capell\Diagnostics\Actions\Dashboard\BuildContentGraphHealthAction;
use Capell\Diagnostics\Data\Dashboard\ContentGraphHealthData;
use Filament\Widgets\Widget;
use Livewire\Attributes\Computed;

final class ContentGraphHealthFilamentWidget extends Widget implements CapellFilamentWidgetContract
{
    use GatedByRoleAndSettings;

    /** @var list<string> */
    protected static array $rolesConfigKeys = ['super_admin'];

    protected static string $settingsKey = 'content_graph_health';

    protected string $view = 'capell-diagnostics::widgets.content-graph-health';

    /** @var int|string|array<string, int|null> */
    protected int|string|array $columnSpan = ['md' => 1];

    public static function getDescription(): string
    {
        return (string) __('capell-diagnostics::package.widget_content_graph_health_description');
    }

    #[Computed(persist: true, seconds: 300)]
    public function data(): ContentGraphHealthData
    {
        return BuildContentGraphHealthAction::run();
    }
}
