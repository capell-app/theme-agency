<?php

declare(strict_types=1);

use Capell\CampaignStudio\Enums\CampaignStatus;
use Capell\CampaignStudio\Filament\Resources\CampaignConversionGoals\Tables\CampaignConversionGoalsTable;
use Capell\CampaignStudio\Filament\Resources\CampaignCtaBlocks\Tables\CampaignCtaBlocksTable;
use Capell\CampaignStudio\Filament\Resources\CampaignGroups\Tables\CampaignGroupsTable;
use Capell\CampaignStudio\Filament\Resources\CampaignLandingPages\Tables\CampaignLandingPagesTable;
use Capell\CampaignStudio\Models\CampaignConversion;
use Capell\CampaignStudio\Models\CampaignConversionGoal;
use Capell\CampaignStudio\Models\CampaignCtaBlock;
use Capell\CampaignStudio\Models\CampaignGroup;
use Capell\CampaignStudio\Models\CampaignLandingPage;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

it('builds campaign studio admin table configurators', function (): void {
    expect(CampaignGroupsTable::configure(campaignStudioTableForCoverage())->getColumns())->not->toBeEmpty()
        ->and(CampaignConversionGoalsTable::configure(campaignStudioTableForCoverage())->getColumns())->not->toBeEmpty()
        ->and(CampaignCtaBlocksTable::configure(campaignStudioTableForCoverage())->getColumns())->not->toBeEmpty()
        ->and(CampaignLandingPagesTable::configure(campaignStudioTableForCoverage())->getColumns())->not->toBeEmpty();
});

it('covers campaign studio model relationships and casts', function (): void {
    $conversion = (new CampaignConversion)->forceFill([
        'attribution' => ['utm_source' => 'newsletter'],
        'metadata' => ['form' => 'demo'],
    ]);
    $group = (new CampaignGroup)->forceFill([
        'status' => CampaignStatus::Active,
    ]);
    $cta = (new CampaignCtaBlock)->forceFill([
        'actions' => [['label' => 'Book demo', 'url' => '/demo']],
    ]);
    $attribution = $conversion->attribution;

    throw_if($attribution === null, RuntimeException::class, 'Expected campaign conversion attribution data.');

    expect($conversion->campaignGroup()->getRelated())->toBeInstanceOf(CampaignGroup::class)
        ->and($conversion->landingPage()->getRelated())->toBeInstanceOf(CampaignLandingPage::class)
        ->and($conversion->goal()->getRelated())->toBeInstanceOf(CampaignConversionGoal::class)
        ->and($attribution->toArray())->toMatchArray(['utm_source' => 'newsletter'])
        ->and($group->landingPages()->getRelated())->toBeInstanceOf(CampaignLandingPage::class)
        ->and($group->conversionGoals()->getRelated())->toBeInstanceOf(CampaignConversionGoal::class)
        ->and($group->conversions()->getRelated())->toBeInstanceOf(CampaignConversion::class)
        ->and($group->getAttribute('status'))->toBe(CampaignStatus::Active)
        ->and($cta->campaignGroup()->getRelated())->toBeInstanceOf(CampaignGroup::class)
        ->and($cta->getAttribute('actions')->toArray()[0])->toMatchArray(['label' => 'Book demo', 'url' => '/demo']);
});

function campaignStudioTableForCoverage(): Table
{
    $livewire = Mockery::mock(HasTable::class);
    $livewire->shouldReceive('makeFilamentTranslatableContentDriver')->andReturn(null);

    return Table::make($livewire);
}
