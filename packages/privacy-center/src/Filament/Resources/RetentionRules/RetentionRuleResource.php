<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Filament\Resources\RetentionRules;

use BackedEnum;
use Capell\Core\Facades\CapellCore;
use Capell\PrivacyCenter\Actions\ApplyRetentionRuleAction;
use Capell\PrivacyCenter\Data\RetentionExecutionResultData;
use Capell\PrivacyCenter\Enums\RetentionAction;
use Capell\PrivacyCenter\Filament\Resources\RetentionRules\Pages\CreateRetentionRule;
use Capell\PrivacyCenter\Filament\Resources\RetentionRules\Pages\EditRetentionRule;
use Capell\PrivacyCenter\Filament\Resources\RetentionRules\Pages\ListRetentionRules;
use Capell\PrivacyCenter\Models\RetentionRule;
use Capell\PrivacyCenter\Providers\PrivacyCenterServiceProvider;
use Filament\Actions\Action;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

final class RetentionRuleResource extends Resource
{
    protected static ?string $slug = 'privacy-center/retention-rules';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $recordTitleAttribute = 'data_domain';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('site_id')->label(__('capell-privacy-center::privacy.admin.fields.site_id'))->numeric(),
            TextInput::make('data_domain')->label(__('capell-privacy-center::privacy.admin.fields.data_domain'))->required()->maxLength(120),
            TextInput::make('record_type')->label(__('capell-privacy-center::privacy.admin.fields.record_type'))->maxLength(255),
            TextInput::make('retention_days')->label(__('capell-privacy-center::privacy.admin.fields.retention_days'))->numeric()->required(),
            Select::make('action')->label(__('capell-privacy-center::privacy.admin.fields.action'))->options(self::actionOptions())->required(),
            TextInput::make('legal_basis')->label(__('capell-privacy-center::privacy.admin.fields.legal_basis'))->maxLength(255),
            Toggle::make('is_active')->label(__('capell-privacy-center::privacy.admin.fields.is_active'))->default(true),
            KeyValue::make('metadata')->label(__('capell-privacy-center::privacy.admin.fields.metadata'))->columnSpanFull(),
        ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('data_domain')->label(__('capell-privacy-center::privacy.admin.fields.data_domain'))->searchable()->sortable(),
            TextColumn::make('record_type')->label(__('capell-privacy-center::privacy.admin.fields.record_type'))->toggleable(),
            TextColumn::make('retention_days')->label(__('capell-privacy-center::privacy.admin.fields.retention_days'))->numeric()->sortable(),
            TextColumn::make('action')->label(__('capell-privacy-center::privacy.admin.fields.action'))->badge()->sortable(),
            IconColumn::make('is_active')->label(__('capell-privacy-center::privacy.admin.fields.is_active'))->boolean()->sortable(),
        ])->recordActions([
            self::dryRunRetentionAction(),
            self::runRetentionAction(),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return RetentionRule::class;
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return __('capell-privacy-center::privacy.admin.navigation_group');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-privacy-center::privacy.admin.resources.retention_rules');
    }

    #[Override]
    public static function shouldRegisterNavigation(): bool
    {
        return CapellCore::isPackageInstalled(PrivacyCenterServiceProvider::$packageName);
    }

    /**
     * @return array<string, PageRegistration>
     */
    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListRetentionRules::route('/'),
            'create' => CreateRetentionRule::route('/create'),
            'edit' => EditRetentionRule::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function actionOptions(): array
    {
        return collect(RetentionAction::cases())->mapWithKeys(fn (RetentionAction $action): array => [$action->value => $action->getLabel()])->all();
    }

    private static function dryRunRetentionAction(): Action
    {
        return Action::make('dry_run_retention')
            ->label(__('capell-privacy-center::privacy.admin.actions.dry_run_retention'))
            ->icon('heroicon-o-eye')
            ->color('gray')
            ->action(function (RetentionRule $record): void {
                $result = resolve(ApplyRetentionRuleAction::class)->handle($record, dryRun: true);

                self::sendRetentionResultNotification($result, dryRun: true);
            });
    }

    private static function runRetentionAction(): Action
    {
        return Action::make('run_retention')
            ->label(__('capell-privacy-center::privacy.admin.actions.run_retention'))
            ->icon('heroicon-o-play')
            ->color('warning')
            ->requiresConfirmation()
            ->action(function (RetentionRule $record): void {
                $result = ApplyRetentionRuleAction::run($record);

                self::sendRetentionResultNotification($result, dryRun: false);
            });
    }

    private static function sendRetentionResultNotification(RetentionExecutionResultData $result, bool $dryRun): void
    {
        Notification::make($dryRun ? 'privacy-retention-dry-run' : 'privacy-retention-run')
            ->title(__($dryRun
                ? 'capell-privacy-center::privacy.admin.messages.retention_dry_run'
                : 'capell-privacy-center::privacy.admin.messages.retention_run'))
            ->body(__('capell-privacy-center::privacy.admin.messages.retention_result', [
                'matched' => $result->matchedRecords,
                'affected' => $result->affectedRecords,
            ]))
            ->success()
            ->send();
    }
}
