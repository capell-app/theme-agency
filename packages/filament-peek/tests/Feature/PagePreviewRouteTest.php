<?php

declare(strict_types=1);

use Capell\Core\Database\Factories\MediaFactory;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Enums\MediaCollectionEnum;
use Capell\Core\Models\Language;
use Capell\Core\Models\Layout;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\FilamentPeek\Actions\CreatePagePreviewSnapshotAction;
use Capell\Frontend\Contracts\FrontendResponseRenderer;
use Capell\Frontend\Data\FrontendRenderContextData;
use Capell\Frontend\Support\Render\FrontendResponseRendererRegistry;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

it('rejects unsigned preview URLs', function (): void {
    $user = $this->createUserWithRole('super_admin');
    $this->actingAs($user);

    $page = Page::factory()->create();
    $snapshot = CreatePagePreviewSnapshotAction::run($page, ['name' => 'Preview'])['snapshot'];

    $this->get('/capell-filament-peek/preview/' . $snapshot->token)->assertForbidden();
});

it('rejects snapshots owned by another user', function (): void {
    $owner = $this->createUserWithRole('super_admin');
    $otherUser = $this->createUserWithRole('super_admin', ['email' => 'other@example.test']);
    $this->actingAs($owner);

    $page = Page::factory()->create();
    $snapshot = CreatePagePreviewSnapshotAction::run($page, ['name' => 'Preview'])['snapshot'];

    $this->actingAs($otherUser);

    $this->get(URL::signedRoute('capell-filament-peek.preview', ['token' => $snapshot->token]))
        ->assertForbidden();
});

it('rejects expired signed preview URLs before loading snapshot data', function (): void {
    $user = $this->createUserWithRole('super_admin');
    $this->actingAs($user);

    $page = Page::factory()->create();
    $snapshot = CreatePagePreviewSnapshotAction::run($page, ['name' => 'Preview'])['snapshot'];
    $url = URL::temporarySignedRoute('capell-filament-peek.preview', now()->subMinute(), ['token' => $snapshot->token]);

    $this->get($url)->assertForbidden();
});

it('renders unsaved page fields through a private signed preview without saving them', function (): void {
    $user = $this->createUserWithRole('super_admin');
    $this->actingAs($user);
    registerPreviewTestRenderer();

    $language = Language::factory()->create();
    $site = Site::factory()->withTranslations($language)->language($language)->create();
    $layout = Layout::factory()->site($site)->default()->create([
        'containers' => [],
    ]);
    $page = Page::factory()
        ->site($site)
        ->layout($layout)
        ->withTranslations($language, [
            'title' => 'Saved title',
            'content' => '<p>Saved body</p>',
        ])
        ->create(['name' => 'Saved page name']);

    $snapshot = CreatePagePreviewSnapshotAction::run($page, [
        'name' => 'Unsaved page name',
        'translations' => [[
            'language_id' => $language->getKey(),
            'title' => 'Unsaved title',
            'content' => '<p>Unsaved body</p>',
            'meta' => ['slug' => 'saved-page-name'],
        ]],
    ])['snapshot'];

    $response = $this->get(URL::signedRoute('capell-filament-peek.preview', ['token' => $snapshot->token]));

    $response
        ->assertOk()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertSee('Unsaved page name')
        ->assertSee('Unsaved title')
        ->assertSee('Unsaved body', false);

    $page->refresh();

    expect($page->name)->toBe('Saved page name')
        ->and($page->translation->title)->toBe('Saved title')
        ->and($page->translation->content)->toBe('<p>Saved body</p>');
});

it('overlays an existing unsaved featured image selection in the preview page context', function (): void {
    $user = $this->createUserWithRole('super_admin');
    $this->actingAs($user);
    registerPreviewTestRenderer();

    $language = Language::factory()->create();
    $site = Site::factory()->withTranslations($language)->language($language)->create();
    $layout = Layout::factory()->site($site)->default()->create(['containers' => []]);
    $page = Page::factory()->site($site)->layout($layout)->withTranslations($language)->create();
    $savedImage = MediaFactory::new()->model($page)->collection(MediaCollectionEnum::Image)->create([
        'uuid' => '11111111-1111-4111-8111-111111111111',
    ]);
    $unsavedImage = MediaFactory::new()->model($page)->collection(MediaCollectionEnum::Image)->create([
        'uuid' => '22222222-2222-4222-8222-222222222222',
    ]);

    $snapshot = CreatePagePreviewSnapshotAction::run($page, [
        'image' => [$unsavedImage->uuid => $unsavedImage->uuid],
    ])['snapshot'];

    $response = $this->get(URL::signedRoute('capell-filament-peek.preview', ['token' => $snapshot->token]));

    $response
        ->assertOk()
        ->assertSee($unsavedImage->uuid)
        ->assertDontSee($savedImage->uuid);
});

it('returns a private friendly response for missing preview snapshots', function (): void {
    $user = $this->createUserWithRole('super_admin');
    $this->actingAs($user);

    $response = $this->get(URL::signedRoute('capell-filament-peek.preview', ['token' => 'missing-token']));

    $response
        ->assertNotFound()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertSee('Preview expired');
});

function registerPreviewTestRenderer(): void
{
    resolve(FrontendResponseRendererRegistry::class)->register(new class implements FrontendResponseRenderer
    {
        public function runtime(): FrontendRuntime
        {
            return FrontendRuntime::Blade;
        }

        public function render(FrontendRenderContextData $context): SymfonyResponse
        {
            $title = $context->page?->translation?->title ?? '';
            $content = $context->page?->translation?->content ?? '';
            $name = $context->page?->getAttribute('name') ?? '';
            $image = $context->page instanceof Page && $context->page->relationLoaded('image')
                ? $context->page->getRelation('image')?->uuid
                : '';

            return response()->make(sprintf(
                '<main><h1>%s</h1><h2>%s</h2><div>%s</div><span>%s</span></main>',
                e((string) $name),
                e((string) $title),
                $content,
                e((string) $image),
            ));
        }
    });
}
