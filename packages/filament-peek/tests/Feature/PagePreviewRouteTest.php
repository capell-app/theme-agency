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
use Illuminate\Database\Eloquent\Model;
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

it('rejects a validly-signed preview URL for an unauthenticated request', function (): void {
    $user = $this->createUserWithRole('super_admin');
    $this->actingAs($user);
    registerPreviewTestRenderer();

    $page = Page::factory()->create();
    $snapshot = CreatePagePreviewSnapshotAction::run($page, ['name' => 'Preview'])['snapshot'];
    $url = URL::signedRoute('capell-filament-peek.preview', ['token' => $snapshot->token]);

    auth('web')->logout();

    $this->get($url)
        ->assertForbidden()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertDontSee('Preview renderer reached');
});

it('rejects a validly-signed preview URL for a non-admin user who does not own the snapshot', function (): void {
    $owner = $this->createUserWithRole('super_admin');
    $this->actingAs($owner);

    $page = Page::factory()->create();
    $snapshot = CreatePagePreviewSnapshotAction::run($page, ['name' => 'Preview'])['snapshot'];
    $url = URL::signedRoute('capell-filament-peek.preview', ['token' => $snapshot->token]);

    $nonAdmin = $this->createUser(['email' => 'non-admin@example.test']);
    $this->actingAs($nonAdmin);

    $this->get($url)
        ->assertForbidden()
        ->assertHeader('Cache-Control', 'no-store, private');
});

it('rejects a validly-signed preview URL for a non-admin user who owns the snapshot before rendering', function (): void {
    $nonAdmin = $this->createUser(['email' => 'non-admin-owner@example.test']);
    $this->actingAs($nonAdmin);
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

    $snapshot = CreatePagePreviewSnapshotAction::run($page, ['name' => 'Preview'])['snapshot'];
    $url = URL::signedRoute('capell-filament-peek.preview', ['token' => $snapshot->token]);

    $this->get($url)
        ->assertForbidden()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertDontSee('Preview renderer reached');
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
        ->assertSee('Unsaved preview - not published')
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
        ->assertSee((string) $unsavedImage->uuid)
        ->assertDontSee((string) $savedImage->uuid);
});

it('overlays an existing unsaved social image selection in the preview page context', function (): void {
    $user = $this->createUserWithRole('super_admin');
    $this->actingAs($user);
    registerPreviewTestRenderer();

    $language = Language::factory()->create();
    $site = Site::factory()->withTranslations($language)->language($language)->create();
    $layout = Layout::factory()->site($site)->default()->create(['containers' => []]);
    $page = Page::factory()->site($site)->layout($layout)->withTranslations($language)->create();
    $savedImage = MediaFactory::new()->model($page)->collection(MediaCollectionEnum::SocialImage)->create([
        'uuid' => '33333333-3333-4333-8333-333333333333',
    ]);
    $unsavedImage = MediaFactory::new()->model($page)->collection(MediaCollectionEnum::SocialImage)->create([
        'uuid' => '44444444-4444-4444-8444-444444444444',
    ]);

    $snapshot = CreatePagePreviewSnapshotAction::run($page, [
        'social_image' => [$unsavedImage->uuid => $unsavedImage->uuid],
    ])['snapshot'];

    $response = $this->get(URL::signedRoute('capell-filament-peek.preview', ['token' => $snapshot->token]));

    $response
        ->assertOk()
        ->assertSee((string) $unsavedImage->uuid)
        ->assertDontSee((string) $savedImage->uuid);
});

it('returns a private friendly response when preview rendering fails', function (): void {
    $user = $this->createUserWithRole('super_admin');
    $this->actingAs($user);
    registerFailingPreviewTestRenderer();

    $language = Language::factory()->create();
    $site = Site::factory()->withTranslations($language)->language($language)->create();
    $layout = Layout::factory()->site($site)->default()->create(['containers' => []]);
    $page = Page::factory()->site($site)->layout($layout)->withTranslations($language)->create();
    $snapshot = CreatePagePreviewSnapshotAction::run($page, ['name' => 'Preview'])['snapshot'];

    $response = $this->get(URL::signedRoute('capell-filament-peek.preview', ['token' => $snapshot->token]));

    $response
        ->assertStatus(500)
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertSee('Preview could not render')
        ->assertDontSee('Preview renderer reached');
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
            $title = $context->page->translation->title ?? '';
            $content = $context->page->translation->content ?? '';
            $name = $context->page?->getAttribute('name') ?? '';
            $image = $context->page instanceof Page && $context->page->relationLoaded('image')
                ? previewTestRelatedMediaUuid($context->page->getRelation('image'))
                : '';
            $socialImage = $context->page instanceof Page && $context->page->relationLoaded('socialImage')
                ? previewTestRelatedMediaUuid($context->page->getRelation('socialImage'))
                : '';

            return response()->make(sprintf(
                '<main><h1>%s</h1><h2>%s</h2><div>%s</div><span>%s</span><span>%s</span></main>',
                e((string) $name),
                e((string) $title),
                $content,
                e((string) $image) . ' Preview renderer reached',
                e((string) $socialImage),
            ));
        }
    });
}

function previewTestRelatedMediaUuid(mixed $media): string
{
    if (! $media instanceof Model) {
        return '';
    }

    $uuid = $media->getAttribute('uuid');

    return is_string($uuid) ? $uuid : '';
}

function registerFailingPreviewTestRenderer(): void
{
    resolve(FrontendResponseRendererRegistry::class)->register(new class implements FrontendResponseRenderer
    {
        public function runtime(): FrontendRuntime
        {
            return FrontendRuntime::Blade;
        }

        public function render(FrontendRenderContextData $context): SymfonyResponse
        {
            throw new RuntimeException('Preview renderer failed for test.');
        }
    });
}
