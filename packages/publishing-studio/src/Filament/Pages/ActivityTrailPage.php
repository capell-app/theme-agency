<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Filament\Pages;

use BackedEnum;
use Capell\PublishingStudio\Enums\PublishingStudioPermission;
use Capell\PublishingStudio\Filament\Pages\Tables\ActivityTrailTable;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Override;

class ActivityTrailPage extends Page implements HasActions, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;

    protected static ?string $slug = 'publishing-studio/reports/activity-trail';

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?int $navigationSort = 1;

    protected string $view = 'capell-admin::components.pages.table';

    #[Override]
    public static function getNavigationLabel(): string
    {
        return (string) __('capell-admin::navigation.activity_trail');
    }

    #[Override]
    public static function canAccess(): bool
    {
        return auth()->user()?->can(PublishingStudioPermission::ViewActivityTrailPage->value) ?? false;
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return (string) __('capell-admin::navigation.group_reports');
    }

    #[Override]
    public function getTitle(): string
    {
        return __('capell-publishing-studio::workflow.activity_trail');
    }

    public function table(Table $table): Table
    {
        return ActivityTrailTable::configure($table);
    }
}
