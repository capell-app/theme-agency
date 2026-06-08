<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Filament\Resources\ConsentPolicies;

use BackedEnum;
use Capell\Core\Facades\CapellCore;
use Capell\PrivacyCenter\Enums\PolicyType;
use Capell\PrivacyCenter\Filament\Resources\ConsentPolicies\Pages\CreateConsentPolicy;
use Capell\PrivacyCenter\Filament\Resources\ConsentPolicies\Pages\EditConsentPolicy;
use Capell\PrivacyCenter\Filament\Resources\ConsentPolicies\Pages\ListConsentPolicies;
use Capell\PrivacyCenter\Models\ConsentPolicy;
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

final class ConsentPolicyResource extends Resource
{
    protected static ?string $slug = 'privacy-center/consent-policies';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $recordTitleAttribute = 'title';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('site_id')->label(__('capell-privacy-center::privacy.admin.fields.site_id'))->numeric(),
            TextInput::make('key')->label(__('capell-privacy-center::privacy.admin.fields.key'))->required()->maxLength(120),
            TextInput::make('version')->label(__('capell-privacy-center::privacy.admin.fields.version'))->required()->maxLength(120),
            TextInput::make('title')->label(__('capell-privacy-center::privacy.admin.fields.title'))->required()->maxLength(255),
            Select::make('type')->label(__('capell-privacy-center::privacy.admin.fields.type'))->options(self::policyTypeOptions())->required(),
            TextInput::make('content_hash')->label(__('capell-privacy-center::privacy.admin.fields.content_hash'))->maxLength(64),
            DateTimePicker::make('effective_at')->label(__('capell-privacy-center::privacy.admin.fields.effective_at')),
            DateTimePicker::make('published_at')->label(__('capell-privacy-center::privacy.admin.fields.published_at')),
            DateTimePicker::make('retired_at')->label(__('capell-privacy-center::privacy.admin.fields.retired_at')),
            KeyValue::make('metadata')->label(__('capell-privacy-center::privacy.admin.fields.metadata'))->columnSpanFull(),
        ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->label(__('capell-privacy-center::privacy.admin.fields.title'))->searchable()->sortable(),
            TextColumn::make('key')->label(__('capell-privacy-center::privacy.admin.fields.key'))->searchable()->toggleable(),
            TextColumn::make('version')->label(__('capell-privacy-center::privacy.admin.fields.version'))->sortable(),
            TextColumn::make('type')->label(__('capell-privacy-center::privacy.admin.fields.type'))->badge()->sortable(),
            TextColumn::make('published_at')->label(__('capell-privacy-center::privacy.admin.fields.published_at'))->dateTime()->sortable(),
            TextColumn::make('retired_at')->label(__('capell-privacy-center::privacy.admin.fields.retired_at'))->dateTime()->sortable()->toggleable(),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return ConsentPolicy::class;
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('capell-privacy-center::privacy.admin.navigation_group');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-privacy-center::privacy.admin.resources.consent_policies');
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
        return [
            'index' => ListConsentPolicies::route('/'),
            'create' => CreateConsentPolicy::route('/create'),
            'edit' => EditConsentPolicy::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function policyTypeOptions(): array
    {
        return collect(PolicyType::cases())
            ->mapWithKeys(fn (PolicyType $type): array => [$type->value => $type->getLabel()])
            ->all();
    }
}
