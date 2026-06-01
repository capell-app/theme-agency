<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Filament\Configurators\Blocks;

use Capell\Admin\Support\SiteScope;
use Capell\CampaignStudio\Models\CampaignCtaBlock;
use Capell\LayoutBuilder\Filament\Configurators\Blocks\DefaultBlockConfigurator;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Tabs\Tab;
use Override;

final class CampaignCtaBlockBlockConfigurator extends DefaultBlockConfigurator
{
    #[Override]
    protected function detailsTab(): Tab
    {
        return Tab::make('campaign_cta')
            ->label(__('capell-campaign-studio::generic.cta_block'))
            ->schema([
                Select::make('meta.cta_block_id')
                    ->label(__('capell-campaign-studio::form.cta_block'))
                    ->options(fn (): array => SiteScope::applyForCurrentActor(CampaignCtaBlock::query())
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->preload(),
            ]);
    }
}
