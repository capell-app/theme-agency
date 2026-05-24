<?php

declare(strict_types=1);

use Capell\Admin\Testing\Filament\ReadsRawSchemaComponents;
use Capell\CampaignStudio\Filament\Extenders\Page\CampaignPageSchemaExtender;
use Capell\CampaignStudio\Filament\Resources\CampaignConversionGoals\CampaignConversionGoalResource;
use Capell\CampaignStudio\Filament\Resources\CampaignConversionGoals\Schemas\CampaignConversionGoalForm;
use Capell\CampaignStudio\Filament\Resources\CampaignCtaBlocks\CampaignCtaBlockResource;
use Capell\CampaignStudio\Filament\Resources\CampaignCtaBlocks\Schemas\CampaignCtaBlockForm;
use Capell\CampaignStudio\Filament\Resources\CampaignGroups\CampaignGroupResource;
use Capell\CampaignStudio\Filament\Resources\CampaignGroups\Schemas\CampaignGroupForm;
use Capell\CampaignStudio\Filament\Resources\CampaignLandingPages\CampaignLandingPageResource;
use Capell\CampaignStudio\Filament\Resources\CampaignLandingPages\Schemas\CampaignLandingPageForm;
use Capell\CampaignStudio\Models\CampaignConversionGoal;
use Capell\CampaignStudio\Models\CampaignCtaBlock;
use Capell\CampaignStudio\Models\CampaignGroup;
use Capell\CampaignStudio\Models\CampaignLandingPage;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

it('builds campaign studio resource form schemas', function (): void {
    $components = campaignStudioFormComponents(CampaignGroupForm::class);

    expect($components)
        ->toHaveCount(11)
        ->and($components[0])->toBeInstanceOf(TextInput::class)
        ->and($components[1])->toBeInstanceOf(TextInput::class)
        ->and($components[2])->toBeInstanceOf(Select::class)
        ->and(campaignStudioFormComponents(CampaignConversionGoalForm::class))
        ->toHaveCount(9)
        ->and(campaignStudioFormComponents(CampaignLandingPageForm::class))
        ->toHaveCount(7);

    $ctaComponents = campaignStudioFormComponents(CampaignCtaBlockForm::class);

    expect($ctaComponents)
        ->toHaveCount(4)
        ->each->toBeInstanceOf(Section::class);

    $ctaFields = collect($ctaComponents)
        ->flatMap(fn (Section $section): array => ReadsRawSchemaComponents::childComponents($section))
        ->values();

    expect($ctaFields)
        ->toHaveCount(9)
        ->and($ctaFields[0])->toBeInstanceOf(Select::class)
        ->and($ctaFields[6])->toBeInstanceOf(Repeater::class)
        ->and($ctaFields[7])->toBeInstanceOf(Fieldset::class)
        ->and($ctaFields[8])->toBeInstanceOf(Toggle::class);
});

it('declares campaign studio resource models navigation labels and pages', function (): void {
    expect(CampaignGroupResource::getModel())->toBe(CampaignGroup::class)
        ->and(CampaignGroupResource::getNavigationGroup())->toBeNull()
        ->and(CampaignGroupResource::getNavigationParentItem())->toBe((string) __('capell-admin::navigation.marketing_studio'))
        ->and(CampaignGroupResource::getNavigationLabel())->toBe('Campaign groups')
        ->and(CampaignGroupResource::getPluralModelLabel())->toBe('Campaign groups')
        ->and(CampaignGroupResource::getPages())->toHaveKeys(['index', 'create', 'edit'])
        ->and(CampaignCtaBlockResource::getModel())->toBe(CampaignCtaBlock::class)
        ->and(CampaignCtaBlockResource::getNavigationLabel())->toBe('CTA blocks')
        ->and(CampaignCtaBlockResource::shouldRegisterNavigation())->toBeFalse()
        ->and(CampaignCtaBlockResource::getPages())->toHaveKeys(['index', 'create', 'edit'])
        ->and(CampaignLandingPageResource::getModel())->toBe(CampaignLandingPage::class)
        ->and(CampaignLandingPageResource::getNavigationLabel())->toBe('Landing pages')
        ->and(CampaignLandingPageResource::getPages())->toHaveKeys(['index', 'create', 'edit'])
        ->and(CampaignConversionGoalResource::getModel())->toBe(CampaignConversionGoal::class)
        ->and(CampaignConversionGoalResource::getNavigationLabel())->toBe('Conversion goals')
        ->and(CampaignConversionGoalResource::shouldRegisterNavigation())->toBeFalse()
        ->and(CampaignConversionGoalResource::getPages())->toHaveKeys(['index', 'create', 'edit']);
});

it('uses container query columns for page campaign sidebar fields', function (): void {
    $components = (new CampaignPageSchemaExtender)->extendSidebarComponents(Schema::make());

    expect($components)
        ->toHaveCount(1)
        ->and($components[0])->toBeInstanceOf(Fieldset::class)
        ->and($components[0]->isGridContainer())->toBeTrue()
        ->and($components[0]->getColumns('default'))->toBe(1)
        ->and($components[0]->getColumns('lg'))->toBeNull()
        ->and($components[0]->getColumns('@xl'))->toBe(2);
});

/**
 * @param  class-string  $formConfigurator
 * @return array<int, object>
 */
function campaignStudioFormComponents(string $formConfigurator): array
{
    return $formConfigurator::configure(Schema::make())->getComponents();
}
