<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Filament\Pages;

use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Capell\Admin\Filament\Concerns\HasNavigationBadge;
use Capell\SeoSuite\Filament\Pages\Tables\TranslationCoverageTable;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Override;

class TranslationCoveragePage extends Page implements HasActions, HasTable
{
    use HasNavigationBadge;
    use HasPageShield;
    use InteractsWithActions;
    use InteractsWithTable;

    protected static ?string $slug = 'seo-suite/translation-coverage';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLanguage;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Language;

    protected static ?int $navigationSort = 14;

    protected string $view = 'capell-admin::components.pages.table';

    #[Override]
    public static function getNavigationLabel(): string
    {
        return (string) (__('capell-admin::navigation.translation_coverage'));
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return (string) (__('capell-admin::navigation.group_monitoring'));
    }

    #[Override]
    public static function getNavigationParentItem(): ?string
    {
        return (string) __('capell-seo-suite::generic.seo_audit');
    }

    public static function table(Table $table): Table
    {
        return TranslationCoverageTable::configure($table);
    }

    #[Override]
    public function getSubheading(): string|Htmlable|null
    {
        return __('capell-admin::generic.translation_coverage_info');
    }

    #[Override]
    public function getTitle(): string|Htmlable
    {
        return __('capell-admin::heading.translation_coverage');
    }
}
