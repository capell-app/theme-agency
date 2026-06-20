<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Filament\Widgets\Health;

use Capell\Admin\Contracts\CapellFilamentWidgetContract;
use Capell\Admin\Contracts\Dashboard\ContentHealthDataProvider;
use Capell\Admin\Data\Dashboard\ContentHealthData;
use Capell\Admin\Filament\Concerns\GatedByRoleAndSettings;
use Filament\Widgets\Widget;
use Livewire\Attributes\Computed;

final class ContentHealthFilamentWidget extends Widget implements CapellFilamentWidgetContract
{
    use GatedByRoleAndSettings;

    /** @var list<string> */
    protected static array $rolesConfigKeys = ['editor', 'admin', 'super_admin'];

    protected static string $settingsKey = 'content_health';

    protected string $view = 'capell-diagnostics::widgets.content-health';

    /** @var int|string|array<string, int|null> */
    protected int|string|array $columnSpan = ['md' => 1];

    public static function getDescription(): string
    {
        return (string) __('capell-admin::dashboard.widget_content_health_description');
    }

    #[Computed(persist: true, seconds: 300)]
    public function data(): ContentHealthData
    {
        return resolve(ContentHealthDataProvider::class)->build();
    }
}
