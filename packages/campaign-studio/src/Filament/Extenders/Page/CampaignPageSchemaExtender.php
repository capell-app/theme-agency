<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Filament\Extenders\Page;

use Capell\Admin\Contracts\Extenders\PageSchemaExtender;
use Capell\Admin\Enums\PageTranslationSchemaHookEnum;
use Capell\Admin\Support\SiteScope;
use Capell\CampaignStudio\Models\CampaignConversionGoal;
use Capell\CampaignStudio\Models\CampaignGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema as SchemaFacade;

final class CampaignPageSchemaExtender implements PageSchemaExtender
{
    public function extendTranslationComponentsForHook(Schema $schema, PageTranslationSchemaHookEnum $hook): array
    {
        return [];
    }

    public function extendRelationManagers(Model $record, array $relationManagers): array
    {
        return $relationManagers;
    }

    public function extendTabs(Schema $schema, array $tabs): array
    {
        $tabs[] = Tab::make(__('capell-campaign-studio::generic.campaign'))
            ->key('campaign')
            ->icon(Heroicon::OutlinedMegaphone)
            ->schema([
                $this->campaignFields(),
            ]);

        return $tabs;
    }

    /**
     * @return array<int, Component>
     */
    public function extendSidebarComponents(Schema $schema): array
    {
        return [];
    }

    private function campaignFields(): Fieldset
    {
        return Fieldset::make(__('capell-campaign-studio::generic.campaign'))
            ->statePath('meta.campaign')
            ->gridContainer()
            ->columns(['default' => 1, 'lg' => null, '@xl' => 2])
            ->schema([
                Select::make('campaign_group_id')
                    ->label(__('capell-campaign-studio::form.campaign_group'))
                    ->options(fn (): array => $this->campaignGroupOptions())
                    ->searchable(),
                Toggle::make('is_landing_page')
                    ->label(__('capell-campaign-studio::generic.landing_page')),
                Select::make('primary_goal_id')
                    ->label(__('capell-campaign-studio::form.primary_goal'))
                    ->options(fn (): array => $this->campaignConversionGoalOptions())
                    ->searchable(),
                TextInput::make('utm_content')
                    ->label(__('capell-campaign-studio::form.utm_content')),
                TextInput::make('utm_term')
                    ->label(__('capell-campaign-studio::form.utm_term')),
            ]);
    }

    /**
     * @return array<int|string, string>
     */
    private function campaignGroupOptions(): array
    {
        if (! SchemaFacade::hasTable((new CampaignGroup)->getTable())) {
            return [];
        }

        return SiteScope::applyForCurrentActor(CampaignGroup::query())->pluck('name', 'id')->toArray();
    }

    /**
     * @return array<int|string, string>
     */
    private function campaignConversionGoalOptions(): array
    {
        if (! SchemaFacade::hasTable((new CampaignConversionGoal)->getTable())) {
            return [];
        }

        return SiteScope::applyForCurrentActor(CampaignConversionGoal::query())->pluck('name', 'id')->toArray();
    }
}
