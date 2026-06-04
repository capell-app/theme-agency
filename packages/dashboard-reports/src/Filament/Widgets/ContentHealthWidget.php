<?php

declare(strict_types=1);

namespace Capell\DashboardReports\Filament\Widgets;

use Capell\Admin\Contracts\CapellWidgetContract;
use Capell\Admin\Contracts\Dashboard\ContentHealthDataProvider;
use Capell\Admin\Data\Dashboard\ContentHealthData;
use Capell\Admin\Filament\Concerns\GatedByRoleAndSettings;
use Filament\Widgets\Widget;
use Livewire\Attributes\Computed;
use Override;

final class ContentHealthWidget extends Widget implements CapellWidgetContract
{
    use GatedByRoleAndSettings;

    /**
     * Container key under which the content health build is memoised for the current
     * request so a single dashboard render does not rebuild data once for canView()
     * and again for data().
     */
    private const string REQUEST_MEMO_KEY = 'capell-dashboard-reports.content-health.request-memo';

    /** @var list<string> */
    protected static array $rolesConfigKeys = ['editor', 'admin', 'super_admin'];

    protected static string $settingsKey = 'content_health';

    protected string $view = 'capell-dashboard-reports::widgets.content-health';

    /** @var int|string|array<string, int|null> */
    protected int|string|array $columnSpan = ['md' => 1];

    protected static ?int $sort = 2;

    #[Override]
    public static function canView(): bool
    {
        return self::canViewCheck() && self::resolveContentHealthData()->issues->count() > 0;
    }

    #[Computed(persist: true, seconds: 300)]
    public function data(): ContentHealthData
    {
        return self::resolveContentHealthData();
    }

    private static function resolveContentHealthData(): ContentHealthData
    {
        app()->scopedIf(
            self::REQUEST_MEMO_KEY,
            static fn (): ContentHealthData => resolve(ContentHealthDataProvider::class)->build(),
        );

        /** @var ContentHealthData $data */
        $data = app()->make(self::REQUEST_MEMO_KEY);

        return $data;
    }
}
