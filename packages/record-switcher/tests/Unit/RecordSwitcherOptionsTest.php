<?php

declare(strict_types=1);

use Capell\Admin\Filament\Resources\Pages\PageResource;
use Capell\Core\Models\Blueprint;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\RecordSwitcher\Filament\RecordSwitcherHeadingExtender;
use Capell\RecordSwitcher\Livewire\RecordSwitcher;
use Capell\RecordSwitcher\Tests\Fixtures\RecordSwitcherDisabledEditPage;
use Capell\RecordSwitcher\Tests\Fixtures\RecordSwitcherEmptySearchResource;
use Capell\RecordSwitcher\Tests\Fixtures\RecordSwitcherTestRecord;
use Capell\RecordSwitcher\Tests\Fixtures\RecordSwitcherTestRecordResource;
use Illuminate\Database\Schema\Blueprint as SchemaBlueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('returns page option labels as html strings for browser choices', function (): void {
    $pageType = Blueprint::factory()->page()->default()->create();
    $currentPage = Page::factory()->type($pageType)->create(['name' => 'Home']);
    Page::factory()->type($pageType)->create(['name' => 'Pricing']);

    $switcher = new RecordSwitcher;
    $switcher->resourceClass = PageResource::class;
    $switcher->recordKey = (string) $currentPage->getRouteKey();
    $switcher->label = 'Home';

    $options = $switcher->getOptions();

    expect($options)->toHaveCount(1)
        ->and($options[0]['label'])->toBe('Pricing')
        ->and(is_string($options[0]['label']))->toBeTrue();
});

it('searches generic resource options with declared searchable attributes', function (): void {
    recordSwitcherCreateFixtureRecordsTable();

    $currentRecord = RecordSwitcherTestRecord::query()->create(['name' => 'Current', 'code' => 'current']);
    RecordSwitcherTestRecord::query()->create(['name' => 'Launch Plan', 'code' => 'editorial']);
    RecordSwitcherTestRecord::query()->create(['name' => 'Archive', 'code' => 'old']);

    $switcher = new RecordSwitcher;
    $switcher->resourceClass = RecordSwitcherTestRecordResource::class;
    $switcher->recordKey = (string) $currentRecord->getRouteKey();
    $switcher->label = 'Current';

    $options = $switcher->getOptions('launch');

    expect($options)->toHaveCount(1)
        ->and($options[0]['label'])->toBe('Launch Plan')
        ->and($options[0]['value'])->toBe('/record-switcher-test-records/2/edit');
});

it('keeps empty searchable attributes from breaking generic option loading', function (): void {
    recordSwitcherCreateFixtureRecordsTable();

    $currentRecord = RecordSwitcherTestRecord::query()->create(['name' => 'Current', 'code' => 'current']);
    RecordSwitcherTestRecord::query()->create(['name' => 'Beta', 'code' => 'beta']);
    RecordSwitcherTestRecord::query()->create(['name' => 'Alpha', 'code' => 'alpha']);

    $switcher = new RecordSwitcher;
    $switcher->resourceClass = RecordSwitcherEmptySearchResource::class;
    $switcher->recordKey = (string) $currentRecord->getRouteKey();
    $switcher->label = 'Current';
    $switcher->limitResults = 1;

    $options = $switcher->getOptions('anything');

    expect($options)->toHaveCount(1)
        ->and($options[0]['label'])->toBe('Beta');
});

it('keeps page option rendering inside the manifest admin query budget', function (): void {
    $pageType = Blueprint::factory()->page()->default()->create();
    $site = Site::factory()->withTranslations(siteDomainData: [
        'domain' => 'example.test',
        'scheme' => 'https',
        'path' => '',
    ])->create(['name' => 'Primary site']);
    $currentPage = Page::factory()->site($site)->type($pageType)->create(['name' => 'Current']);
    $parentPage = Page::factory()->site($site)->type($pageType)->create(['name' => 'Section']);
    Page::factory()->site($site)->type($pageType)->parent($parentPage)->create(['name' => 'Pricing']);
    Page::query()->fixTree();

    $switcher = new RecordSwitcher;
    $switcher->resourceClass = PageResource::class;
    $switcher->recordKey = (string) $currentPage->getRouteKey();
    $switcher->label = 'Current';

    DB::flushQueryLog();
    DB::enableQueryLog();

    $options = $switcher->getOptions('pricing');
    $queryCount = count(DB::getQueryLog());

    DB::disableQueryLog();

    expect($options)->toHaveCount(1)
        ->and($options[0]['label'])->toContain('Section')
        ->and($queryCount)->toBeLessThanOrEqual(recordSwitcherAdminQueryBudget());
});

it('lets resources opt out of heading replacement', function (): void {
    expect((new RecordSwitcherHeadingExtender)->supports(new RecordSwitcherDisabledEditPage))->toBeFalse();
});

function recordSwitcherCreateFixtureRecordsTable(): void
{
    Schema::dropIfExists('record_switcher_test_records');
    Schema::create('record_switcher_test_records', static function (SchemaBlueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('code');
        $table->timestamps();
    });
}

function recordSwitcherAdminQueryBudget(): int
{
    $manifest = capell_json_file_array(__DIR__ . '/../../capell.json');
    $budget = data_get($manifest, 'performance.adminQueryBudget', 10);

    return is_int($budget) ? $budget : 10;
}
