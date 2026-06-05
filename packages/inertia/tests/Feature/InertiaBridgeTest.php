<?php

declare(strict_types=1);

use Capell\Core\Enums\FrontendRuntime;
use Capell\Frontend\Support\Render\FrontendResponseRendererRegistry;
use Capell\Frontend\Support\Routing\FrontendRouteMiddlewareRegistry;
use Capell\Inertia\Data\InertiaAdapterData;
use Capell\Inertia\Facades\CapellInertia;
use Capell\Inertia\Health\InertiaHealthCheck;
use Capell\Inertia\Http\Middleware\HandleInertiaRequests;
use Capell\Inertia\Support\InertiaAdapterRegistry;
use Capell\Inertia\Tests\InertiaTestCase;
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

    expect($shared)->toHaveKey('capell')
        ->and($shared['capell']['adapter'])->toBe('react');
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

    expect($registry->active())->toBeNull();
});

it('reports inertia bridge health from registered renderer and middleware services', function (): void {
    $health = new InertiaHealthCheck;

    expect(InertiaHealthCheck::compatibleCapellApiVersion())->toBe('^4.0')
        ->and($health->rendererRegistered())->toBeTrue()
        ->and($health->middlewareRegistered())->toBeTrue()
        ->and($health->passes())->toBeTrue();
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
