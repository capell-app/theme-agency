<?php

declare(strict_types=1);

namespace Capell\Newsletter\Filament\Resources\ProviderConnections;

use BackedEnum;
use Capell\Admin\Filament\Components\Forms\SiteSelect;
use Capell\Newsletter\Enums\AuthType;
use Capell\Newsletter\Enums\ProviderType;
use Capell\Newsletter\Filament\Concerns\ScopesNewsletterResourcesToAssignedSites;
use Capell\Newsletter\Filament\Resources\ProviderConnections\Pages\CreateProviderConnection;
use Capell\Newsletter\Filament\Resources\ProviderConnections\Pages\EditProviderConnection;
use Capell\Newsletter\Filament\Resources\ProviderConnections\Pages\ListProviderConnections;
use Capell\Newsletter\Models\ProviderConnection;
use Capell\Newsletter\Support\Providers\FakeProviderGuard;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

class ProviderConnectionResource extends Resource
{
    use ScopesNewsletterResourcesToAssignedSites;

    protected static ?string $slug = 'newsletter/provider-connections';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static ?string $recordTitleAttribute = 'name';

    #[Override]
    public static function form(Schema $configurator): Schema
    {
        return $configurator->components([
            SiteSelect::make('site_id')->required(),
            TextInput::make('name')->label(__('capell-newsletter::form.name'))->required(),
            Select::make('provider')
                ->label(__('capell-newsletter::form.provider'))
                ->options(self::providerOptions())
                ->required(),
            Select::make('auth_type')
                ->label(__('capell-newsletter::form.auth_type'))
                ->options(self::authTypeOptions())
                ->required(),
            KeyValue::make('credentials')->label(__('capell-newsletter::form.credentials')),
            Toggle::make('is_enabled')->label(__('capell-newsletter::form.enabled'))->default(true),
        ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label(__('capell-newsletter::form.name'))->searchable()->sortable(),
                TextColumn::make('provider')->label(__('capell-newsletter::table.provider'))->badge()->sortable(),
                TextColumn::make('updated_at')->label(__('capell-newsletter::table.updated_at'))->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('site_id')
                    ->label(__('capell-admin::form.site'))
                    ->searchable()
                    ->relationship(
                        name: 'site',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query): Builder => self::applyNewsletterSiteScope($query, 'id'),
                    ),
                SelectFilter::make('provider')
                    ->label(__('capell-newsletter::form.provider'))
                    ->options(self::providerOptions()),
                TernaryFilter::make('is_enabled')
                    ->label(__('capell-newsletter::form.enabled')),
            ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return ProviderConnection::class;
    }

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return self::applyNewsletterSiteScope(parent::getEloquentQuery());
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('capell-admin::navigation.group_marketing');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-newsletter::navigation.provider_connections');
    }

    #[Override]
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListProviderConnections::route('/'),
            'create' => CreateProviderConnection::route('/create'),
            'edit' => EditProviderConnection::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function providerOptions(): array
    {
        $options = [];

        foreach (ProviderType::cases() as $provider) {
            if ($provider === ProviderType::Fake && ! FakeProviderGuard::isAllowed()) {
                continue;
            }

            $options[$provider->value] = $provider->getLabel();
        }

        return $options;
    }

    /**
     * @return array<array-key, mixed>
     */
    private static function authTypeOptions(): array
    {
        return collect(AuthType::cases())
            ->mapWithKeys(static fn (AuthType $authType): array => [$authType->value => $authType->getLabel()])
            ->all();
    }
}
