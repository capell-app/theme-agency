<?php

declare(strict_types=1);

namespace Capell\AccessGate\Filament\Resources\ClaimTokens;

use BackedEnum;
use Capell\AccessGate\Enums\ClaimTokenStatus;
use Capell\AccessGate\Filament\Resources\ClaimTokens\Pages\ListClaimTokens;
use Capell\AccessGate\Filament\Resources\Concerns\AccessGateFilamentOptions;
use Capell\AccessGate\Models\ClaimToken;
use Capell\AccessGate\Providers\AccessGateServiceProvider;
use Capell\Core\Facades\CapellCore;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

final class ClaimTokenResource extends Resource
{
    use AccessGateFilamentOptions;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Link;

    protected static ?int $navigationSort = 33;

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['area', 'registration', 'grant'])->latest('created_at'))
            ->columns([
                TextColumn::make('area.key')
                    ->label(__('capell-access-gate::filament.fields.area'))
                    ->searchable(),
                TextColumn::make('registration.email')
                    ->label(__('capell-access-gate::filament.fields.email'))
                    ->searchable(),
                TextColumn::make('status')
                    ->label(__('capell-access-gate::filament.fields.status'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->label(__('capell-access-gate::filament.fields.expires_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('consumed_at')
                    ->label(__('capell-access-gate::filament.fields.consumed_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('capell-access-gate::filament.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('access_area_id')
                    ->label(__('capell-access-gate::filament.fields.area'))
                    ->relationship('area', 'key'),
                SelectFilter::make('status')
                    ->label(__('capell-access-gate::filament.fields.status'))
                    ->options(self::enumOptions(ClaimTokenStatus::class, 'capell-access-gate::filament.claim_token_status')),
            ]);
    }

    /** @return class-string<ClaimToken> */
    #[Override]
    public static function getModel(): string
    {
        return ClaimToken::class;
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return (string) __('capell-admin::navigation.group_websites');
    }

    #[Override]
    public static function getNavigationParentItem(): string
    {
        return __('capell-access-gate::filament.navigation_group');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-access-gate::filament.resources.claim_tokens');
    }

    #[Override]
    public static function shouldRegisterNavigation(): bool
    {
        return CapellCore::getPackage(AccessGateServiceProvider::$packageName)->isInstalled();
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListClaimTokens::route('/'),
        ];
    }
}
