<?php

declare(strict_types=1);

use Capell\AccessGate\Enums\AccessAreaStatus;
use Capell\AccessGate\Enums\ClaimTokenStatus;
use Capell\AccessGate\Enums\EventType;
use Capell\AccessGate\Http\Middleware\AccessGateMiddleware;
use Capell\AccessGate\Models\Area;
use Capell\AccessGate\Models\ClaimToken;
use Capell\AccessGate\Models\Event;
use Capell\AccessGate\Models\Registration;
use Capell\AccessGate\Support\AccessGateDiagnosticsService;
use Capell\AccessGate\Tests\Support\FakePageCacheMiddleware;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Manifest\CapellManifestData;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

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
    $diagnostics = resolve(AccessGateDiagnosticsService::class);
    $router = resolve(Router::class);

    $router->aliasMiddleware('frontend.cache', FakePageCacheMiddleware::class);
    $router->pushMiddlewareToGroup('web', 'frontend.cache');
    $router->middlewarePriority = [AccessGateMiddleware::class, 'access-gate', FakePageCacheMiddleware::class];

    $priorityOk = $diagnostics->checkMiddleware($router);
    $priorityOk = accessGateDoctorResult($priorityOk);

    $router->middlewarePriority = [FakePageCacheMiddleware::class, AccessGateMiddleware::class];
    $pageCacheBeforeGate = $diagnostics->checkMiddleware($router);
    $pageCacheBeforeGate = accessGateDoctorResult($pageCacheBeforeGate);

    $router->middlewarePriority = [];
    $router->middlewareGroup('web', ['frontend.cache']);

    $routeLevelRequired = $diagnostics->checkMiddleware($router);
    $routeLevelRequired = accessGateDoctorResult($routeLevelRequired);

    config()->set('access-gate.middleware.page_cache_aliases', ['frontend.cache', '', FakePageCacheMiddleware::class, 123]);
    $aliases = $diagnostics->pageCacheAliases();
    $priorityNames = $diagnostics->pageCacheMiddlewarePriorityNames($router);

    config()->set('access-gate.middleware.page_cache_aliases', 'broken');

    config()->set('app.url', 'not-a-url');

    $missingAppUrl = $diagnostics->checkClaimHosts();
    $missingAppUrl = accessGateDoctorResult($missingAppUrl);

    config()->set('app.url', 'https://app.test');
    Area::factory()->create([
        'key' => 'preview',
        'claim_url_hosts' => ['preview.test'],
    ]);
    $missingClaimHost = $diagnostics->checkClaimHosts();
    $missingClaimHost = accessGateDoctorResult($missingClaimHost);

    $noSitesTable = $diagnostics->checkSiteScopedAreas();
    $noSitesTable = accessGateDoctorResult($noSitesTable);

    defineAccessGateSiteTablesForDoctorTest();
    $noSites = $diagnostics->checkSiteScopedAreas();
    $noSites = accessGateDoctorResult($noSites);

    DB::table('sites')->insert([
        'id' => 1,
        'name' => 'Site 1',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    Area::query()->where('key', 'preview')->update(['site_id' => 1]);
    $siteScopedOk = $diagnostics->checkSiteScopedAreas();
    $siteScopedOk = accessGateDoctorResult($siteScopedOk);

    expect($priorityOk->passed)->toBeTrue()
        ->and($pageCacheBeforeGate->passed)->toBeFalse()
        ->and($routeLevelRequired->passed)->toBeFalse()
        ->and($aliases)->toBe(['frontend.cache', FakePageCacheMiddleware::class])
        ->and($priorityNames)->toContain('frontend.cache', FakePageCacheMiddleware::class)
        ->and($diagnostics->pageCacheAliases())->toBe([])
        ->and($diagnostics->firstMiddlewarePosition([null, 'auth:web', 'access-gate:preview'], ['auth', 'access-gate']))->toBe(1)
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

it('reports stale access records before pruning them', function (): void {
    $area = Area::factory()->create();
    $claimToken = ClaimToken::factory()->for($area, 'area')->create([
        'status' => ClaimTokenStatus::Expired,
        'expires_at' => now()->subDays(120),
        'updated_at' => now()->subDays(120),
    ]);

    capell_artisan('capell:access-gate-prune', ['--dry-run' => true])
        ->expectsOutputToContain('Found 1 stale Access Gate record')
        ->assertSuccessful();

    expect(ClaimToken::query()->whereKey($claimToken->getKey())->exists())->toBeTrue();

    capell_artisan('capell:access-gate-prune')
        ->expectsOutputToContain('Pruned 1 stale Access Gate record')
        ->assertSuccessful();

    expect(ClaimToken::query()->whereKey($claimToken->getKey())->exists())->toBeFalse();
});

it('exports filtered audit events as CSV from the console', function (): void {
    $area = Area::factory()->create([
        'key' => 'private-launch',
        'name' => 'Private Launch',
    ]);
    $registration = Registration::factory()->for($area, 'area')->create([
        'email' => 'reader@example.com',
    ]);
    Event::factory()->for($area, 'area')->for($registration, 'registration')->create([
        'type' => EventType::RegistrationApproved,
        'user_id' => 42,
        'payload' => ['registration_id' => $registration->getKey()],
        'metadata' => ['source' => 'test'],
        'occurred_at' => now()->subMinute(),
    ]);
    Event::factory()->for($area, 'area')->for($registration, 'registration')->create([
        'type' => EventType::RegistrationApproved,
        'occurred_at' => now()->subMinutes(2),
    ]);
    Event::factory()->create([
        'type' => EventType::AccessDenied,
    ]);

    $path = storage_path('framework/testing/access-gate-audit-' . Str::random(8) . '.csv');

    capell_artisan('capell:access-gate-audit-export', [
        '--path' => $path,
        '--area' => 'private-launch',
        '--type' => EventType::RegistrationApproved->value,
        '--limit' => 1,
    ])
        ->expectsOutputToContain('Access Gate audit CSV written')
        ->assertSuccessful();

    $rows = accessGateCsvRows((string) File::get($path));

    expect($rows)->toHaveCount(2)
        ->and($rows[0])->toBe([
            'occurred_at',
            'event_id',
            'type',
            'area_id',
            'area_key',
            'area_name',
            'registration_id',
            'registration_email',
            'grant_id',
            'grant_email',
            'claim_token_id',
            'browser_token_id',
            'user_id',
            'subject_type',
            'subject_id',
            'payload',
            'metadata',
        ])
        ->and($rows[1][2])->toBe(EventType::RegistrationApproved->value)
        ->and($rows[1][4])->toBe('private-launch')
        ->and($rows[1][7])->toBe('reader@example.com')
        ->and($rows[1][12])->toBe('42')
        ->and($rows[1][15])->toBe('{"registration_id":' . $registration->getKey() . '}')
        ->and($rows[1][16])->toBe('{"source":"test"}');
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

/**
 * @return list<list<string>>
 */
function accessGateCsvRows(string $csv): array
{
    $rows = [];

    foreach (explode("\n", trim($csv)) as $line) {
        if ($line === '') {
            continue;
        }

        $rows[] = str_getcsv($line);
    }

    return $rows;
}
