<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Filament\Pages;

use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Capell\Admin\Filament\Concerns\HasNavigationBadge;
use Capell\SeoSuite\Filament\Pages\Tables\AiDiscoveryTable;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Override;

class AiDiscoveryPage extends Page implements HasActions, HasTable
{
    use HasNavigationBadge;
    use HasPageShield;
    use InteractsWithActions;
    use InteractsWithTable;

    protected static ?string $slug = 'seo-suite/ai-discovery';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Sparkles;

    protected static ?int $navigationSort = 13;

    protected string $view = 'capell-admin::components.pages.table';

    #[Override]
    public static function getNavigationLabel(): string
    {
        return (string) __('capell-seo-suite::generic.ai_discovery');
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return (string) __('capell-admin::navigation.group_monitoring');
    }

    #[Override]
    public static function getNavigationParentItem(): ?string
    {
        return (string) __('capell-seo-suite::generic.seo_audit');
    }

    public static function table(Table $table): Table
    {
        return AiDiscoveryTable::configure($table);
    }

    #[Override]
    public function getSubheading(): string|Htmlable|null
    {
        return __('capell-seo-suite::generic.ai_discovery_info');
    }

    #[Override]
    public function getTitle(): string|Htmlable
    {
        return __('capell-seo-suite::generic.ai_discovery');
    }
}
