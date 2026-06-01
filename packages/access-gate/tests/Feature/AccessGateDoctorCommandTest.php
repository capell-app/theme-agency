<?php

declare(strict_types=1);

use Capell\AccessGate\Console\Commands\AccessGateDoctorCommand;
use Capell\AccessGate\Enums\AccessAreaStatus;
use Capell\AccessGate\Http\Middleware\AccessGateMiddleware;
use Capell\AccessGate\Models\Area;
use Capell\AccessGate\Tests\Support\FakePageCacheMiddleware;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Manifest\CapellManifestData;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

afterEach(function (): void {
    $publishedMigrations = glob(base_path('database/migrations/*access_gate*.php'));

    foreach (is_array($publishedMigrations) ? $publishedMigrations : [] as $publishedMigration) {
        unlink($publishedMigration);
    }
});

it('passes doctor checks for the default test installation', function (): void {
    capell_artisan('capell:access-gate-doctor')
        ->assertSuccessful();
});

it('passes doctor checks when middleware priority forces the gate before frontend cache', function (): void {
    $router = resolve(Router::class);
    $router->aliasMiddleware('frontend.cache', FakePageCacheMiddleware::class);
    $router->pushMiddlewareToGroup('web', 'frontend.cache');

    capell_artisan('capell:access-gate-doctor')
        ->assertSuccessful();
});

it('reports doctor diagnostics for middleware ordering and site scoping edge cases', function (): void {
    $command = new AccessGateDoctorCommand;
    $router = resolve(Router::class);
    $checkMiddleware = new ReflectionMethod(AccessGateDoctorCommand::class, 'checkMiddleware');
    $checkClaimHosts = new ReflectionMethod(AccessGateDoctorCommand::class, 'checkClaimHosts');
    $checkSiteScopedAreas = new ReflectionMethod(AccessGateDoctorCommand::class, 'checkSiteScopedAreas');
    $firstMiddlewarePosition = new ReflectionMethod(AccessGateDoctorCommand::class, 'firstMiddlewarePosition');
    $pageCacheAliases = new ReflectionMethod(AccessGateDoctorCommand::class, 'pageCacheAliases');
    $pageCacheMiddlewarePriorityNames = new ReflectionMethod(AccessGateDoctorCommand::class, 'pageCacheMiddlewarePriorityNames');

    $router->aliasMiddleware('frontend.cache', FakePageCacheMiddleware::class);
    $router->pushMiddlewareToGroup('web', 'frontend.cache');
    $router->middlewarePriority = [AccessGateMiddleware::class, 'access-gate', FakePageCacheMiddleware::class];

    $priorityOk = $checkMiddleware->invoke($command, $router);
    $priorityOk = accessGateDoctorResult($priorityOk);

    $router->middlewarePriority = [FakePageCacheMiddleware::class, AccessGateMiddleware::class];
    $pageCacheBeforeGate = $checkMiddleware->invoke($command, $router);
    $pageCacheBeforeGate = accessGateDoctorResult($pageCacheBeforeGate);

    $router->middlewarePriority = [];
    $router->middlewareGroup('web', ['frontend.cache']);

    $routeLevelRequired = $checkMiddleware->invoke($command, $router);
    $routeLevelRequired = accessGateDoctorResult($routeLevelRequired);

    config()->set('access-gate.middleware.page_cache_aliases', ['frontend.cache', '', FakePageCacheMiddleware::class, 123]);
    $aliases = $pageCacheAliases->invoke($command);
    $priorityNames = $pageCacheMiddlewarePriorityNames->invoke($command, $router);

    config()->set('access-gate.middleware.page_cache_aliases', 'broken');

    config()->set('app.url', 'not-a-url');

    $missingAppUrl = $checkClaimHosts->invoke($command);
    $missingAppUrl = accessGateDoctorResult($missingAppUrl);

    config()->set('app.url', 'https://app.test');
    Area::factory()->create([
        'key' => 'preview',
        'claim_url_hosts' => ['preview.test'],
    ]);
    $missingClaimHost = $checkClaimHosts->invoke($command);
    $missingClaimHost = accessGateDoctorResult($missingClaimHost);

    $noSitesTable = $checkSiteScopedAreas->invoke($command);
    $noSitesTable = accessGateDoctorResult($noSitesTable);

    defineAccessGateSiteTablesForDoctorTest();
    $noSites = $checkSiteScopedAreas->invoke($command);
    $noSites = accessGateDoctorResult($noSites);

    DB::table('sites')->insert([
        'id' => 1,
        'name' => 'Site 1',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    Area::query()->where('key', 'preview')->update(['site_id' => 1]);
    $siteScopedOk = $checkSiteScopedAreas->invoke($command);
    $siteScopedOk = accessGateDoctorResult($siteScopedOk);

    expect($priorityOk->passed)->toBeTrue()
        ->and($pageCacheBeforeGate->passed)->toBeFalse()
        ->and($routeLevelRequired->passed)->toBeFalse()
        ->and($aliases)->toBe(['frontend.cache', FakePageCacheMiddleware::class])
        ->and($priorityNames)->toContain('frontend.cache', FakePageCacheMiddleware::class)
        ->and($pageCacheAliases->invoke($command))->toBe([])
        ->and($firstMiddlewarePosition->invoke($command, [null, 'auth:web', 'access-gate:preview'], ['auth', 'access-gate']))->toBe(1)
        ->and($missingAppUrl->passed)->toBeTrue()
        ->and($missingClaimHost->message)->toContain('preview')
        ->and($noSitesTable->passed)->toBeTrue()
        ->and($noSites->passed)->toBeTrue()
        ->and($siteScopedOk->passed)->toBeTrue();
});

it('reports site-scoped access areas without coverage for every site', function (): void {
    defineAccessGateSiteTablesForDoctorTest();

    DB::table('sites')->insert([
        [
            'id' => 1,
            'name' => 'Site 1',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => 2,
            'name' => 'Site 2',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    Area::factory()->create([
        'key' => 'preview',
        'site_id' => 1,
    ]);

    capell_artisan('capell:access-gate-doctor')
        ->assertFailed()
        ->expectsOutputToContain('Site-scoped access areas are missing explicit config');
});

it('sets up the configured default access area', function (): void {
    config()->set('access-gate.install.default_area.key', 'capell-preview');
    config()->set('access-gate.install.default_area.name', 'Capell Preview');

    capell_artisan('capell:access-gate-setup')
        ->assertSuccessful();

    $area = Area::query()->where('key', 'capell-preview')->firstOrFail();

    expect($area->name)->toBe('Capell Preview')
        ->and($area->status)->toBe(AccessAreaStatus::Paused);
});

it('installs publishables, runs migrations, and creates the paused default access area', function (): void {
    config()->set('access-gate.install.default_area.key', 'capell-preview');
    config()->set('access-gate.install.default_area.name', 'Capell Preview');

    $manifestPath = dirname(__DIR__, 2) . '/capell.json';
    $manifestData = json_decode((string) file_get_contents($manifestPath), true, flags: JSON_THROW_ON_ERROR);
    throw_unless(is_array($manifestData), RuntimeException::class, 'Access Gate manifest must decode to an array.');

    CapellCore::registerManifestPackage(CapellManifestData::fromArray(
        $manifestData,
        dirname($manifestPath),
    ));

    capell_artisan('capell:access-gate-install')
        ->assertSuccessful();

    $area = Area::query()->where('key', 'capell-preview')->firstOrFail();

    expect($area->name)->toBe('Capell Preview')
        ->and($area->status)->toBe(AccessAreaStatus::Paused);
});

function defineAccessGateSiteTablesForDoctorTest(): void
{
    if (! Schema::hasTable('sites')) {
        Schema::create('sites', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
        });
    }
}

function accessGateDoctorResult(mixed $result): DoctorCheckResultData
{
    expect($result)->toBeInstanceOf(DoctorCheckResultData::class);

    return $result;
}
