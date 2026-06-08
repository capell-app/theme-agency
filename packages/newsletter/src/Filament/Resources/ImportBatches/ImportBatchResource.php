<?php

declare(strict_types=1);

namespace Capell\Newsletter\Filament\Resources\ImportBatches;

use BackedEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Newsletter\Enums\ImportBatchStatus;
use Capell\Newsletter\Enums\ImportBatchType;
use Capell\Newsletter\Filament\Concerns\ScopesNewsletterResourcesToAssignedSites;
use Capell\Newsletter\Filament\Resources\ImportBatches\Pages\ListImportBatches;
use Capell\Newsletter\Models\ImportBatch;
use Capell\Newsletter\Providers\NewsletterServiceProvider;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

class ImportBatchResource extends Resource
{
    use ScopesNewsletterResourcesToAssignedSites;

    protected static ?string $slug = 'newsletter/import-batches';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    #[Override]
    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('filename')->label(__('capell-newsletter::form.name'))->searchable(),
                TextColumn::make('type')->badge()->sortable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('total_rows')->sortable(),
                TextColumn::make('valid_rows')->sortable(),
                TextColumn::make('invalid_rows')->sortable(),
                TextColumn::make('created_at')->label(__('capell-newsletter::table.created_at'))->dateTime()->sortable(),
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
                SelectFilter::make('type')
                    ->label(__('capell-newsletter::form.type'))
                    ->options(self::importBatchTypeOptions()),
                SelectFilter::make('status')
                    ->label(__('capell-newsletter::form.status'))
                    ->options(self::importBatchStatusOptions()),
            ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return ImportBatch::class;
    }

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return self::applyNewsletterSiteScope(parent::getEloquentQuery());
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return null;
    }

    #[Override]
    public static function getNavigationParentItem(): string
    {
        return __('capell-admin::navigation.marketing_studio');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-newsletter::navigation.import_batches');
    }

    #[Override]
    public static function shouldRegisterNavigation(): bool
    {
        return CapellCore::isPackageInstalled(NewsletterServiceProvider::$packageName);
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListImportBatches::route('/'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function importBatchTypeOptions(): array
    {
        return collect(ImportBatchType::cases())
            ->mapWithKeys(static fn (ImportBatchType $type): array => [$type->value => __('capell-newsletter::generic.import_batch_type.' . $type->value)])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private static function importBatchStatusOptions(): array
    {
        return collect(ImportBatchStatus::cases())
            ->mapWithKeys(static fn (ImportBatchStatus $status): array => [$status->value => __('capell-newsletter::generic.import_batch_status.' . $status->value)])
            ->all();
    }
}
