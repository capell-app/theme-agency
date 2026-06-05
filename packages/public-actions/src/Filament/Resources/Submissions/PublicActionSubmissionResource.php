<?php

declare(strict_types=1);

namespace Capell\PublicActions\Filament\Resources\Submissions;

use BackedEnum;
use Capell\Admin\Support\SiteScope;
use Capell\Core\Facades\CapellCore;
use Capell\PublicActions\Enums\PublicActionSubmissionStatus;
use Capell\PublicActions\Filament\Resources\Concerns\PublicActionFilamentOptions;
use Capell\PublicActions\Filament\Resources\Submissions\Pages\ListPublicActionSubmissions;
use Capell\PublicActions\Models\PublicActionSubmission;
use Capell\PublicActions\Providers\PublicActionsServiceProvider;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

final class PublicActionSubmissionResource extends Resource
{
    use PublicActionFilamentOptions;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInbox;

    protected static ?int $navigationSort = 41;

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['action', 'site'])->latest('submitted_at'))
            ->columns([
                TextColumn::make('action.key')->label(__('capell-public-actions::filament.fields.action'))->searchable(),
                TextColumn::make('status')->label(__('capell-public-actions::filament.fields.status'))->badge()->sortable(),
                TextColumn::make('source_type')->label(__('capell-public-actions::filament.fields.source_type'))->toggleable(),
                TextColumn::make('submitted_at')->label(__('capell-public-actions::filament.fields.submitted_at'))->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('capell-public-actions::filament.fields.status'))
                    ->options(self::enumOptions(PublicActionSubmissionStatus::class)),
            ]);
    }

    /** @return class-string<PublicActionSubmission> */
    #[Override]
    public static function getModel(): string
    {
        return PublicActionSubmission::class;
    }

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return SiteScope::applyForCurrentActor(parent::getEloquentQuery());
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return (string) __('capell-admin::navigation.group_system');
    }

    #[Override]
    public static function getNavigationParentItem(): string
    {
        return __('capell-public-actions::filament.navigation_group');
    }

    #[Override]
    public static function shouldRegisterNavigation(): bool
    {
        return CapellCore::getPackage(PublicActionsServiceProvider::$packageName)->isInstalled();
    }

    #[Override]
    public static function getPages(): array
    {
        return ['index' => ListPublicActionSubmissions::route('/')];
    }
}
