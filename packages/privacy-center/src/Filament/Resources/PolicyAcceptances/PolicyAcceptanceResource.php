<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Filament\Resources\PolicyAcceptances;

use BackedEnum;
use Capell\Core\Facades\CapellCore;
use Capell\PrivacyCenter\Enums\PolicyType;
use Capell\PrivacyCenter\Filament\Resources\PolicyAcceptances\Pages\ListPolicyAcceptances;
use Capell\PrivacyCenter\Models\PolicyAcceptance;
use Capell\PrivacyCenter\Providers\PrivacyCenterServiceProvider;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

final class PolicyAcceptanceResource extends Resource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('site_id')->label(__('capell-privacy-center::privacy.admin.fields.site_id'))->numeric()->disabled(),
            TextInput::make('subject_type')->label(__('capell-privacy-center::privacy.admin.fields.subject_type'))->disabled(),
            TextInput::make('subject_id')->label(__('capell-privacy-center::privacy.admin.fields.subject_id'))->disabled(),
            Select::make('policy_type')->label(__('capell-privacy-center::privacy.admin.fields.policy_type'))->options(self::policyTypeOptions())->disabled(),
            TextInput::make('policy_key')->label(__('capell-privacy-center::privacy.admin.fields.policy_key'))->disabled(),
            TextInput::make('policy_version')->label(__('capell-privacy-center::privacy.admin.fields.policy_version'))->disabled(),
            TextInput::make('context')->label(__('capell-privacy-center::privacy.admin.fields.context'))->disabled(),
            DateTimePicker::make('accepted_at')->label(__('capell-privacy-center::privacy.admin.fields.accepted_at'))->disabled(),
            KeyValue::make('metadata')->label(__('capell-privacy-center::privacy.admin.fields.metadata'))->disabled()->columnSpanFull(),
        ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('policy_key')->label(__('capell-privacy-center::privacy.admin.fields.policy_key'))->searchable()->sortable(),
            TextColumn::make('policy_version')->label(__('capell-privacy-center::privacy.admin.fields.policy_version'))->sortable(),
            TextColumn::make('policy_type')->label(__('capell-privacy-center::privacy.admin.fields.policy_type'))->badge()->sortable(),
            TextColumn::make('context')->label(__('capell-privacy-center::privacy.admin.fields.context'))->toggleable(),
            TextColumn::make('accepted_at')->label(__('capell-privacy-center::privacy.admin.fields.accepted_at'))->dateTime()->sortable(),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return PolicyAcceptance::class;
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('capell-privacy-center::privacy.admin.navigation_group');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-privacy-center::privacy.admin.resources.policy_acceptances');
    }

    #[Override]
    public static function shouldRegisterNavigation(): bool
    {
        return CapellCore::isPackageInstalled(PrivacyCenterServiceProvider::$packageName);
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    public static function getPages(): array
    {
        return ['index' => ListPolicyAcceptances::route('/')];
    }

    /**
     * @return array<string, string>
     */
    private static function policyTypeOptions(): array
    {
        return collect(PolicyType::cases())->mapWithKeys(fn (PolicyType $type): array => [$type->value => $type->getLabel()])->all();
    }
}
