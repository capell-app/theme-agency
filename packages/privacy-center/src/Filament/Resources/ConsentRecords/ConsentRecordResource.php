<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Filament\Resources\ConsentRecords;

use BackedEnum;
use Capell\Core\Facades\CapellCore;
use Capell\PrivacyCenter\Enums\ConsentDecision;
use Capell\PrivacyCenter\Enums\CookieCategory;
use Capell\PrivacyCenter\Filament\Resources\ConsentRecords\Pages\ListConsentRecords;
use Capell\PrivacyCenter\Models\ConsentRecord;
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

final class ConsentRecordResource extends Resource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCheckCircle;

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('site_id')->label(__('capell-privacy-center::privacy.admin.fields.site_id'))->numeric()->disabled(),
            TextInput::make('subject_type')->label(__('capell-privacy-center::privacy.admin.fields.subject_type'))->disabled(),
            TextInput::make('subject_id')->label(__('capell-privacy-center::privacy.admin.fields.subject_id'))->disabled(),
            TextInput::make('policy_version')->label(__('capell-privacy-center::privacy.admin.fields.policy_version'))->disabled(),
            Select::make('category')->label(__('capell-privacy-center::privacy.admin.fields.category'))->options(self::categoryOptions())->disabled(),
            Select::make('decision')->label(__('capell-privacy-center::privacy.admin.fields.decision'))->options(self::decisionOptions())->disabled(),
            TextInput::make('jurisdiction')->label(__('capell-privacy-center::privacy.admin.fields.jurisdiction'))->disabled(),
            DateTimePicker::make('decided_at')->label(__('capell-privacy-center::privacy.admin.fields.decided_at'))->disabled(),
            DateTimePicker::make('expires_at')->label(__('capell-privacy-center::privacy.admin.fields.expires_at'))->disabled(),
            DateTimePicker::make('revoked_at')->label(__('capell-privacy-center::privacy.admin.fields.revoked_at'))->disabled(),
            KeyValue::make('metadata')->label(__('capell-privacy-center::privacy.admin.fields.metadata'))->disabled()->columnSpanFull(),
        ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('category')->label(__('capell-privacy-center::privacy.admin.fields.category'))->badge()->sortable(),
            TextColumn::make('decision')->label(__('capell-privacy-center::privacy.admin.fields.decision'))->badge()->sortable(),
            TextColumn::make('policy_version')->label(__('capell-privacy-center::privacy.admin.fields.policy_version'))->toggleable(),
            TextColumn::make('jurisdiction')->label(__('capell-privacy-center::privacy.admin.fields.jurisdiction'))->toggleable(),
            TextColumn::make('decided_at')->label(__('capell-privacy-center::privacy.admin.fields.decided_at'))->dateTime()->sortable(),
            TextColumn::make('expires_at')->label(__('capell-privacy-center::privacy.admin.fields.expires_at'))->dateTime()->toggleable(),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return ConsentRecord::class;
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('capell-privacy-center::privacy.admin.navigation_group');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-privacy-center::privacy.admin.resources.consent_records');
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
        return ['index' => ListConsentRecords::route('/')];
    }

    /**
     * @return array<string, string>
     */
    private static function categoryOptions(): array
    {
        return collect(CookieCategory::cases())->mapWithKeys(fn (CookieCategory $category): array => [$category->value => $category->getLabel()])->all();
    }

    /**
     * @return array<string, string>
     */
    private static function decisionOptions(): array
    {
        return collect(ConsentDecision::cases())->mapWithKeys(fn (ConsentDecision $decision): array => [$decision->value => $decision->getLabel()])->all();
    }
}
