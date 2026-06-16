<?php

declare(strict_types=1);

use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Models\Blueprint;
use Capell\Core\Models\Page;
use Capell\Frontend\Data\FrontendRenderContextData;
use Capell\Frontend\Support\Render\FrontendResponseRendererRegistry;
use Capell\Frontend\Support\Routing\FrontendRouteMiddlewareRegistry;
use Capell\Inertia\Actions\BuildInertiaPagePropsAction;
use Capell\Inertia\Actions\ResolveInertiaAdapterKeyAction;
use Capell\Inertia\Actions\ResolveInertiaComponentNameAction;
use Capell\Inertia\Actions\ResolveInertiaRootViewAction;
use Capell\Inertia\Data\InertiaAdapterData;
use Capell\Inertia\Facades\CapellInertia;
use Capell\Inertia\Health\InertiaHealthCheck;
use Capell\Inertia\Http\Middleware\HandleInertiaRequests;
use Capell\Inertia\Rendering\CapellInertiaResponseRenderer;
use Capell\Inertia\Support\InertiaAdapterRegistry;
use Capell\Inertia\Tests\InertiaTestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Testing\TestResponse;

use function Pest\Laravel\get;

use Symfony\Component\HttpFoundation\Response;

require_once dirname(__DIR__) . '/InertiaTestCase.php';

uses(InertiaTestCase::class);

it('registers the inertia renderer and middleware on the frontend route stack', function (): void {
    $middleware = resolve(FrontendRouteMiddlewareRegistry::class)->all();
    $webMiddlewareIndex = array_search('web', $middleware, true);
    $inertiaMiddlewareIndex = array_search(HandleInertiaRequests::class, $middleware, true);

    throw_if($webMiddlewareIndex === false, RuntimeException::class, 'Web middleware was not registered.');
    throw_if($inertiaMiddlewareIndex === false, RuntimeException::class, 'Inertia middleware was not registered.');

    expect(resolve(FrontendResponseRendererRegistry::class)->has(FrontendRuntime::Inertia))->toBeTrue()
        ->and($middleware)->toContain(HandleInertiaRequests::class)
        ->and($inertiaMiddlewareIndex)->toBeGreaterThan($webMiddlewareIndex);
});

it('shares namespaced capell inertia props through the middleware', function (): void {
    config()->set('capell-inertia.adapter', 'react');

    $shared = resolve(HandleInertiaRequests::class)->share(request());
    $capell = $shared['capell'] ?? null;
    throw_unless(is_array($capell), RuntimeException::class, 'Expected shared Capell inertia props.');

    expect($shared)->toHaveKey('capell')
        ->and($capell['adapter'] ?? null)->toBe('react');

    config()->set('capell-inertia.adapter', ['invalid']);

    $shared = resolve(HandleInertiaRequests::class)->share(request());
    $capell = $shared['capell'] ?? null;
    throw_unless(is_array($capell), RuntimeException::class, 'Expected shared Capell inertia props.');

    expect($capell['adapter'] ?? null)->toBe('vue');
});

it('tracks registered inertia adapters and resolves the configured active adapter', function (): void {
    $registry = new InertiaAdapterRegistry;
    $vueAdapter = new InertiaAdapterData(
        key: 'vue',
        packageName: 'capell-app/theme-inertia-bookings-vue',
        npmDependencies: ['@inertiajs/vue3' => '^2.0'],
        buildPath: 'resources/js/app.js',
        entrypoint: 'resources/js/app.js',
        components: ['Capell/Page' => 'resources/js/Pages/Capell/Page.vue'],
    );
    $reactAdapter = new InertiaAdapterData(
        key: 'react',
        packageName: 'capell-app/theme-inertia-bookings-react',
        npmDependencies: ['@inertiajs/react' => '^2.0'],
        buildPath: 'resources/js/app.jsx',
        entrypoint: 'resources/js/app.jsx',
        components: ['Capell/Page' => 'resources/js/Pages/Capell/Page.jsx'],
    );

    config()->set('capell-inertia.adapter', 'react');

    expect($registry->register($vueAdapter))->toBe($registry)
        ->and($registry->register($reactAdapter))->toBe($registry)
        ->and($registry->all())->toHaveKeys(['vue', 'react'])
        ->and($registry->get('missing'))->toBeNull()
        ->and($registry->active())->toBe($reactAdapter);

    config()->set('capell-inertia.adapter', ['invalid']);

    expect($registry->active())->toBe($vueAdapter)
        ->and(ResolveInertiaAdapterKeyAction::run(''))->toBe('vue')
        ->and(ResolveInertiaAdapterKeyAction::run(' react '))->toBe('react');
});

it('sanitizes root view and component config values', function (): void {
    config()->set('capell-inertia.root_view', ['invalid']);

    expect(ResolveInertiaRootViewAction::run(['invalid']))->toBe('capell-inertia::app')
        ->and(ResolveInertiaRootViewAction::run(' inertia-test::app '))->toBe('inertia-test::app')
        ->and(ResolveInertiaComponentNameAction::run(['invalid']))->toBe('Capell/Page')
        ->and(ResolveInertiaComponentNameAction::run(' Capell/CustomPage '))->toBe('Capell/CustomPage')
        ->and(ResolveInertiaComponentNameAction::run('', 'Capell/Fallback'))->toBe('Capell/Fallback')
        ->and(resolve(HandleInertiaRequests::class)->rootView(request()))->toBe('capell-inertia::app');
});

it('uses a sanitized adapter key in public page props', function (): void {
    config()->set('capell-inertia.adapter', ['invalid']);

    $props = BuildInertiaPagePropsAction::run(new FrontendRenderContextData(
        page: null,
        site: null,
        language: null,
        layout: null,
        theme: null,
    ));

    expect(data_get($props, 'runtime.adapter'))->toBe('vue');
});

it('uses sanitized root view and component values for public page rendering', function (): void {
    config()->set('capell-inertia.root_view', ['invalid']);
    config()->set('capell-inertia.page_component', ['invalid']);

    $pageType = Blueprint::factory()->page()->default()->create();
    $page = Page::factory()->type($pageType)->create(['name' => 'Inertia Page']);
    $request = Request::create('/inertia-page', 'GET', server: ['HTTP_X_INERTIA' => 'true']);
    app()->instance('request', $request);

    $response = (new CapellInertiaResponseRenderer)->render(new FrontendRenderContextData(
        page: $page,
        site: null,
        language: null,
        layout: null,
        theme: null,
    ));

    $payload = json_decode((string) $response->getContent(), associative: true, flags: JSON_THROW_ON_ERROR);

    expect($payload['component'] ?? null)->toBe('Capell/Page');
});

it('reports inertia bridge health from registered renderer and middleware services', function (): void {
    resolve(InertiaAdapterRegistry::class)->register(new InertiaAdapterData(
        key: 'vue',
        packageName: 'capell-app/theme-inertia-bookings-vue',
        npmDependencies: ['@inertiajs/vue3' => '^2.0'],
        buildPath: 'resources/js/app.js',
        entrypoint: 'resources/js/app.js',
        components: ['Capell/Page' => 'resources/js/Pages/Capell/Page.vue'],
    ));

    $health = new InertiaHealthCheck;
    $diagnostics = $health->runDiagnostics();

    expect(InertiaHealthCheck::compatibleCapellApiVersion())->toBe('^4.0')
        ->and($health->rendererRegistered())->toBeTrue()
        ->and($health->middlewareRegistered())->toBeTrue()
        ->and($health->adapterReadinessCheck()->passed)->toBeTrue()
        ->and($diagnostics)->toHaveCount(3)
        ->and($health->passes())->toBeTrue();
});

it('reports a missing configured inertia adapter in health diagnostics', function (): void {
    config()->set('capell-inertia.adapter', 'react');

    $check = (new InertiaHealthCheck)->adapterReadinessCheck();

    expect($check->passed)->toBeFalse()
        ->and($check->message)->toContain('react')
        ->and($check->remediation)->not->toBeNull();
});

it('renders package route responses through the capell inertia helper', function (): void {
    View::addNamespace('inertia-test', __DIR__ . '/../Fixtures/views');
    config()->set('capell-inertia.root_view', 'inertia-test::app');

    Route::middleware([HandleInertiaRequests::class])->get('/_test/inertia', fn (): Response => CapellInertia::render('Capell/Test', ['message' => 'ok']));

    get('/_test/inertia')
        ->assertOk()
        ->assertSee('data-page=', false)
        ->assertSee('Capell\\/Test', false);

    get('/_test/inertia', ['X-Inertia' => 'true'])
        ->assertOk()
        ->assertJsonPath('component', 'Capell/Test')
        ->assertJsonPath('props.message', 'ok');
});

it('uses sanitized root view and component values for package route responses', function (): void {
    config()->set('capell-inertia.root_view', ['invalid']);

    Route::middleware([HandleInertiaRequests::class])->get('/_test/inertia-sanitized', fn (): Response => CapellInertia::render(' Capell/Test ', [
        'message' => 'ok',
    ]));

    get('/_test/inertia-sanitized', ['X-Inertia' => 'true'])
        ->assertOk()
        ->assertJsonPath('component', 'Capell/Test')
        ->assertJsonPath('props.message', 'ok');

    expect(resolve(HandleInertiaRequests::class)->rootView(request()))->toBe('capell-inertia::app');
});

it('applies optional response status through the shared inertia response renderer', function (): void {
    Route::middleware([HandleInertiaRequests::class])->get('/_test/inertia-status', fn (): Response => CapellInertia::render('Capell/Test', [
        'message' => 'accepted',
    ], 202));

    get('/_test/inertia-status', ['X-Inertia' => 'true'])
        ->assertStatus(202)
        ->assertJsonPath('component', 'Capell/Test')
        ->assertJsonPath('props.message', 'accepted');
});

it('rejects package route initial html when inertia props expose authoring markers', function (): void {
    $this->withoutExceptionHandling();
    View::addNamespace('inertia-test', __DIR__ . '/../Fixtures/views');
    config()->set('capell-inertia.root_view', 'inertia-test::app');

    Route::middleware([HandleInertiaRequests::class])->get('/_test/inertia-unsafe', fn (): Response => CapellInertia::render('Capell/Test', [
        'model_id' => 123,
    ]));

    expect(fn (): TestResponse => get('/_test/inertia-unsafe'))
        ->toThrow(RuntimeException::class, 'Public HTML contains an authoring marker');
});

it('uses the capell head and inertia root components in the bridge view', function (): void {
    $view = file_get_contents(__DIR__ . '/../../resources/views/app.blade.php');

    expect($view)->toContain('<x-capell::app.head')
        ->and($view)->toContain('<x-inertia::head')
        ->and($view)->toContain('<x-inertia::app');
});
