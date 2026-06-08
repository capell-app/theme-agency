<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Filament\Resources\CampaignConversionGoals\Tables;

use Capell\Admin\Filament\Contracts\TableConfigurator;
use Capell\Admin\Support\SiteScope;
use Capell\CampaignStudio\Enums\ConversionGoalType;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class CampaignConversionGoalsTable implements TableConfigurator
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('capell-campaign-studio::form.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('campaignGroup.name')
                    ->label(__('capell-campaign-studio::form.campaign_group')),
                TextColumn::make('type')
                    ->label(__('capell-campaign-studio::form.type')),
                TextColumn::make('conversions_count')
                    ->label(__('capell-campaign-studio::generic.conversions'))
                    ->counts('conversions'),
            ])
            ->filters([
                SelectFilter::make('site_id')
                    ->label(__('capell-admin::form.site'))
                    ->searchable()
                    ->relationship(
                        name: 'site',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query): Builder => SiteScope::applyForCurrentActor($query, 'id'),
                    ),
                SelectFilter::make('type')
                    ->label(__('capell-campaign-studio::form.type'))
                    ->options(ConversionGoalType::class),
            ])
            ->recordActions([
                EditAction::make(),
                ActionGroup::make([
                    DeleteAction::make(),
                ])
                    ->color('gray'),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
