<?php

declare(strict_types=1);

use Capell\Core\Enums\FrontendRuntime;
use Capell\Frontend\Support\Render\FrontendResponseRendererRegistry;
use Capell\Frontend\Support\Routing\FrontendRouteMiddlewareRegistry;
use Capell\Inertia\Facades\CapellInertia;
use Capell\Inertia\Http\Middleware\HandleInertiaRequests;
use Capell\Inertia\Tests\InertiaTestCase;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\get;

use Symfony\Component\HttpFoundation\Response;

require_once dirname(__DIR__) . '/InertiaTestCase.php';

uses(InertiaTestCase::class);

it('registers the inertia renderer and middleware on the frontend route stack', function (): void {
    $middleware = resolve(FrontendRouteMiddlewareRegistry::class)->all();

    expect(resolve(FrontendResponseRendererRegistry::class)->has(FrontendRuntime::Inertia))->toBeTrue()
        ->and($middleware)->toContain(HandleInertiaRequests::class)
        ->and(array_search(HandleInertiaRequests::class, $middleware, true))->toBeGreaterThan(array_search('web', $middleware, true));
});

it('shares namespaced capell inertia props through the middleware', function (): void {
    config()->set('capell-inertia.adapter', 'react');

    $shared = app(HandleInertiaRequests::class)->share(request());

    expect($shared)->toHaveKey('capell')
        ->and($shared['capell']['adapter'])->toBe('react');
});

it('renders package route responses through the capell inertia helper', function (): void {
    Route::middleware([HandleInertiaRequests::class])->get('/_test/inertia', fn (): Response => CapellInertia::render('Capell/Test', ['message' => 'ok']));

    get('/_test/inertia', ['X-Inertia' => 'true'])
        ->assertOk()
        ->assertJsonPath('component', 'Capell/Test')
        ->assertJsonPath('props.message', 'ok');
});

it('uses the capell head and inertia root components in the bridge view', function (): void {
    $view = file_get_contents(__DIR__ . '/../../resources/views/app.blade.php');

    expect($view)->toContain('<x-capell::app.head')
        ->and($view)->toContain('<x-inertia::head')
        ->and($view)->toContain('<x-inertia::app');
});
