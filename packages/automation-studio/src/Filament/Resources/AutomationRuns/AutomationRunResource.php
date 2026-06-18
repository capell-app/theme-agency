<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Filament\Resources\AutomationRuns;

use BackedEnum;
use Capell\Admin\Support\SiteScope;
use Capell\AutomationStudio\Actions\ReplayAutomationRunAction;
use Capell\AutomationStudio\Data\AutomationActionResultData;
use Capell\AutomationStudio\Filament\Resources\AutomationRuns\Pages\ListAutomationRuns;
use Capell\AutomationStudio\Models\AutomationRun;
use Capell\AutomationStudio\Providers\AutomationStudioServiceProvider;
use Capell\Core\Facades\CapellCore;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Override;

final class AutomationRunResource extends Resource
{
    protected static ?string $slug = 'automation-studio/automation-runs';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedListBullet;

    protected static ?string $recordTitleAttribute = 'rule_key';

    #[Override]
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('rule_key')->label(__('capell-automation-studio::generic.fields.rule'))->searchable()->sortable(),
                TextColumn::make('action_key')->label(__('capell-automation-studio::generic.fields.action'))->searchable()->toggleable(),
                TextColumn::make('idempotency_key')->label(__('capell-automation-studio::generic.fields.idempotency_key'))->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('trigger_type')->label(__('capell-automation-studio::generic.fields.trigger'))->badge()->sortable(),
                TextColumn::make('action_type')->label(__('capell-automation-studio::generic.fields.action_type'))->badge()->sortable(),
                TextColumn::make('status')->label(__('capell-automation-studio::generic.fields.status'))->badge()->sortable(),
                TextColumn::make('message')->label(__('capell-automation-studio::generic.fields.message'))->limit(80)->toggleable(),
                TextColumn::make('started_at')->label(__('capell-automation-studio::generic.fields.started_at'))->dateTime()->sortable(),
                TextColumn::make('finished_at')->label(__('capell-automation-studio::generic.fields.finished_at'))->dateTime()->sortable(),
            ])
            ->recordActions([
                Action::make('replay')
                    ->label(__('capell-automation-studio::generic.replay.action'))
                    ->icon(Heroicon::ArrowPath)
                    ->authorize('update')
                    ->visible(fn (AutomationRun $record): bool => ReplayAutomationRunAction::make()->canReplay($record))
                    ->requiresConfirmation()
                    ->action(function (AutomationRun $record): void {
                        $results = ReplayAutomationRunAction::run($record);
                        $success = collect($results)->every(
                            static fn (mixed $result): bool => $result instanceof AutomationActionResultData && $result->success,
                        );

                        $notification = Notification::make('automation-studio-run-replayed')
                            ->title($success
                                ? __('capell-automation-studio::generic.replay.succeeded')
                                : __('capell-automation-studio::generic.replay.failed'));

                        ($success ? $notification->success() : $notification->danger())->send();
                    }),
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
        return SiteScope::applyForCurrentActor(parent::getEloquentQuery())->with('rule')->latest('started_at');
    }

    #[Override]
    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof Authenticatable && Gate::forUser($user)->allows('viewAny', AutomationRun::class);
    }

    #[Override]
    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user instanceof Authenticatable && Gate::forUser($user)->allows('viewAny', AutomationRun::class);
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
