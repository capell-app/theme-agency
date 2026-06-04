<?php

declare(strict_types=1);

use Awcodes\Curator\CuratorServiceProvider;
use Capell\Admin\Filament\Resources\Pages\Pages\EditPage;
use Capell\Core\Models\Language;
use Capell\Core\Models\Layout;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\FilamentPeek\Actions\FindPagePreviewSnapshotAction;
use Capell\FilamentPeek\Data\PagePreviewSnapshotData;
use Filament\Facades\Filament;
use Illuminate\Support\Str;
use Livewire\Livewire;

it('creates the unsaved preview snapshot when the header action is clicked', function (): void {
    $user = $this->createUserWithRole('super_admin');
    $this->actingAs($user);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->app->register(CuratorServiceProvider::class);
    $this->registerAndMigrateSettings(
        ['2026_05_10_190871_01_create_ai-orchestrator_settings'],
        __DIR__ . '/../../../seo-suite/database/settings',
    );
    config()->set('settings.migrations_paths.capell-seo-suite', __DIR__ . '/../../../seo-suite/database/settings');

    $language = Language::factory()->create();
    $site = Site::factory()->withTranslations($language)->language($language)->create();
    $layout = Layout::factory()->site($site)->default()->create(['containers' => []]);
    $page = Page::factory()
        ->site($site)
        ->layout($layout)
        ->withTranslations($language, [
            'title' => 'Saved title',
            'content' => '<p>Saved body</p>',
        ])
        ->create(['name' => 'Saved page name']);

    $component = Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
        ->set('data.translations.0.title', 'Unsaved preview title')
        ->callAction('peekPagePreview')
        ->assertDispatched('open-preview-modal');

    $dispatches = $component->effects['dispatches'] ?? [];

    if (! is_array($dispatches)) {
        $dispatches = [];
    }

    $event = collect($dispatches)
        ->firstWhere('name', 'open-preview-modal');

    expect($event)->not->toBeNull();

    $iframeUrl = $event['params']['iframeUrl'] ?? null;

    expect($iframeUrl)->toBeString()
        ->and($iframeUrl)->toContain('/capell-filament-peek/preview/');

    $token = Str::between((string) $iframeUrl, '/capell-filament-peek/preview/', '?');
    $snapshot = FindPagePreviewSnapshotAction::run($token);

    throw_unless($snapshot instanceof PagePreviewSnapshotData, RuntimeException::class, 'Expected page preview snapshot to be stored.');

    expect($snapshot)->not->toBeNull()
        ->and($snapshot->formState['name'])->toBe('Unsaved preview title')
        ->and($snapshot->formState['translations'][0]['title'])->toBe('Unsaved preview title');

    $page->refresh();

    expect($page->name)->toBe('Saved page name')
        ->and($page->translation->title)->toBe('Saved title');
});
