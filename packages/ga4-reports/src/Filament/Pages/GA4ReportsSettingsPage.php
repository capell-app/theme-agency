<?php

declare(strict_types=1);

namespace Capell\GA4Reports\Filament\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Capell\Admin\Filament\Pages\AbstractPackageSettingsPage;

final class GA4ReportsSettingsPage extends AbstractPackageSettingsPage
{
    use HasPageShield;

    protected static string $settingsGroup = 'ga4_reports';

    protected static ?string $slug = 'extensions/ga4-reports/settings';
}
