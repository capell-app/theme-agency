<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Filament\Resources\AutomationRuns;

use BackedEnum;
use Capell\AutomationStudio\Filament\Resources\AutomationRuns\Pages\ListAutomationRuns;
use Capell\AutomationStudio\Models\AutomationRun;
use Capell\AutomationStudio\Providers\AutomationStudioServiceProvider;
use Capell\Core\Facades\CapellCore;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

final class AutomationRunResource extends Resource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedListBullet;

    protected static ?string $recordTitleAttribute = 'rule_key';

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('rule_key')->label(__('capell-automation-studio::generic.fields.rule'))->searchable()->sortable(),
            TextColumn::make('action_key')->label(__('capell-automation-studio::generic.fields.action'))->searchable()->toggleable(),
            TextColumn::make('idempotency_key')->label(__('capell-automation-studio::generic.fields.idempotency_key'))->searchable()->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('trigger_type')->label(__('capell-automation-studio::generic.fields.trigger'))->badge()->sortable(),
            TextColumn::make('action_type')->label(__('capell-automation-studio::generic.fields.action_type'))->badge()->sortable(),
            TextColumn::make('status')->label(__('capell-automation-studio::generic.fields.status'))->badge()->sortable(),
            TextColumn::make('message')->label(__('capell-automation-studio::generic.fields.message'))->limit(80)->toggleable(),
            TextColumn::make('started_at')->label(__('capell-automation-studio::generic.fields.started_at'))->dateTime()->sortable(),
            TextColumn::make('finished_at')->label(__('capell-automation-studio::generic.fields.finished_at'))->dateTime()->sortable(),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return AutomationRun::class;
    }

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('rule')->latest('started_at');
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('capell-automation-studio::generic.navigation.group');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-automation-studio::generic.resources.runs');
    }

    #[Override]
    public static function shouldRegisterNavigation(): bool
    {
        return CapellCore::isPackageInstalled(AutomationStudioServiceProvider::$packageName);
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListAutomationRuns::route('/'),
        ];
    }
}
