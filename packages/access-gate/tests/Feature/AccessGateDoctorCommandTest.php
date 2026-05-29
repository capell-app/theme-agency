<?php

declare(strict_types=1);

use Capell\AccessGate\Enums\AccessAreaStatus;
use Capell\AccessGate\Models\Area;
use Capell\AccessGate\Tests\Support\FakePageCacheMiddleware;
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
