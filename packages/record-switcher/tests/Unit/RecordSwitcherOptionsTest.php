<?php

declare(strict_types=1);

use Capell\Admin\Filament\Resources\Pages\PageResource;
use Capell\Core\Models\Blueprint;
use Capell\Core\Models\Page;
use Capell\RecordSwitcher\Livewire\RecordSwitcher;

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
