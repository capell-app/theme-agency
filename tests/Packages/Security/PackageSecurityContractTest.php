<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../scripts/audit-package-security.php';

it('keeps every package manifest on the security contract', function (): void {
    expect(capell_security_manifest_contract_issues(base_path()))->toBe([]);
});

it('declares and protects public package routes', function (): void {
    expect(capell_security_route_contract_issues(base_path()))->toBe([]);
});

it('uses explicit timeouts for all external HTTP clients', function (): void {
    expect(capell_security_http_clients_without_timeouts(base_path()))->toBe([]);
});

it('keeps public package Blade free of authoring and secret surfaces', function (): void {
    expect(capell_security_public_blade_violations(base_path()))->toBe([]);
});

it('keeps GitHub workflow security gates intact', function (): void {
    expect(capell_security_workflow_issues(base_path()))->toBe([]);
});

it('declares authorization posture for admin package surfaces', function (): void {
    $invalid = [];

    foreach (capell_security_manifest_payloads(base_path()) as $slug => $manifest) {
        $surfaces = capell_security_string_list($manifest['surfaces'] ?? []);

        if (! in_array('admin', $surfaces, true)) {
            continue;
        }

        $security = $manifest['security'] ?? null;
        $adminSurface = is_array($security) ? ($security['adminSurface'] ?? null) : null;
        $authorization = is_array($adminSurface) ? ($adminSurface['authorization'] ?? null) : null;

        if (! in_array($authorization, ['permissions', 'policies', 'panel-auth'], true)) {
            $invalid[$slug] = is_scalar($authorization) ? (string) $authorization : get_debug_type($authorization);
        }
    }

    expect($invalid)->toBe([]);
});

it('requires admin resources to have manifest permissions or package policies', function (): void {
    expect(capell_security_admin_resource_coverage_issues(base_path()))->toBe([]);
});

it('fails when manifest public route metadata drifts from package code', function (): void {
    $root = makeSecurityFixtureRoot('route-drift');

    try {
        makeSecurityFixturePackage($root, 'fixture-route-drift', [
            'routeNames' => [],
        ]);

        $issues = capell_security_manifest_contract_issues($root);

        expect($issues['packages/fixture-route-drift/capell.json'] ?? [])->toContain('security.publicSurface.routeNames is out of sync with package code; run scripts/sync-package-security-manifests.php');
    } finally {
        deleteSecurityFixtureRoot($root);
    }
});

it('fails when sensitive manifest metadata drifts from model and migration code', function (): void {
    $root = makeSecurityFixtureRoot('sensitive-drift');

    try {
        makeSecurityFixturePackage($root, 'fixture-sensitive-drift', [
            'routeNames' => ['fixture-sensitive-drift.submit'],
        ], sensitiveData: [
            'encryptedFields' => [],
            'hashedTokenFields' => [],
            'redactedOutputClasses' => [],
            'plaintextJustifications' => [],
        ]);

        $issues = capell_security_manifest_contract_issues($root);

        expect($issues['packages/fixture-sensitive-drift/capell.json'] ?? [])->toContain('security.sensitiveData.encryptedFields is out of sync with package code; run scripts/sync-package-security-manifests.php')
            ->and($issues['packages/fixture-sensitive-drift/capell.json'] ?? [])->toContain('security.sensitiveData.hashedTokenFields is out of sync with package code; run scripts/sync-package-security-manifests.php')
            ->and($issues['packages/fixture-sensitive-drift/capell.json'] ?? [])->toContain('security.sensitiveData.plaintextJustifications is out of sync with package code; run scripts/sync-package-security-manifests.php');
    } finally {
        deleteSecurityFixtureRoot($root);
    }
});

it('fails when an admin resource has no permission or policy coverage', function (): void {
    $root = makeSecurityFixtureRoot('admin-resource-coverage');

    try {
        makeSecurityFixturePackage($root, 'fixture-admin-resource');
        $manifestPath = $root . '/packages/fixture-admin-resource/capell.json';
        $manifest = json_decode((string) file_get_contents($manifestPath), true, flags: JSON_THROW_ON_ERROR);

        throw_unless(is_array($manifest), RuntimeException::class, 'Expected fixture manifest JSON to decode to an array.');

        $manifest['surfaces'] = ['admin'];
        $contributions = $manifest['contributes'] ?? [];
        $manifest['contributes'] = is_array($contributions) ? $contributions : [];
        $manifest['contributes'][] = [
            'type' => 'admin-resource',
            'class' => 'Capell\\FixtureRouteDrift\\Manifest\\FixtureResourceContribution',
            'resourceClass' => 'Capell\\FixtureRouteDrift\\Filament\\Resources\\FixtureRecordResource',
        ];

        file_put_contents(
            $manifestPath,
            json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL,
        );

        $issues = capell_security_admin_resource_coverage_issues($root);

        expect($issues['packages/fixture-admin-resource/capell.json'] ?? [])->toContain('admin-resource contributions require manifest permissions or package policy classes');
    } finally {
        deleteSecurityFixtureRoot($root);
    }
});

/**
 * @return non-empty-string
 */
function makeSecurityFixtureRoot(string $name): string
{
    $root = sys_get_temp_dir() . '/capell-security-contract-' . $name . '-' . bin2hex(random_bytes(4));

    mkdir($root . '/packages', 0777, true);

    return $root;
}

/**
 * @param  array<string, mixed>  $publicSurfaceOverrides
 * @param  array<string, mixed>  $sensitiveData
 */
function makeSecurityFixturePackage(
    string $root,
    string $slug,
    array $publicSurfaceOverrides = [],
    array $sensitiveData = [
        'encryptedFields' => ['Capell\\FixtureRouteDrift\\Models\\FixtureRecord::$api_key'],
        'hashedTokenFields' => ['fixture_records.token_hash'],
        'redactedOutputClasses' => [],
        'plaintextJustifications' => [
            [
                'field' => 'fixture_records.webhook_secret',
                'reason' => 'Fixture justification.',
            ],
        ],
    ],
): void {
    $packagePath = $root . '/packages/' . $slug;
    $routeName = $slug . '.submit';

    mkdir($packagePath . '/routes', 0777, true);
    mkdir($packagePath . '/src/Models', 0777, true);
    mkdir($packagePath . '/database/migrations', 0777, true);

    $routeFile = <<<'PHP'
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::post('submit', static fn (): string => 'ok')->middleware('throttle:30,1')->name('ROUTE_NAME');
PHP;

    file_put_contents($packagePath . '/routes/web.php', str_replace('ROUTE_NAME', $routeName, $routeFile));

    file_put_contents($packagePath . '/src/Models/FixtureRecord.php', <<<'PHP'
<?php

declare(strict_types=1);

namespace Capell\FixtureRouteDrift\Models;

use Illuminate\Database\Eloquent\Model;

final class FixtureRecord extends Model
{
    protected $casts = [
        'api_key' => 'encrypted',
    ];
}
PHP);

    file_put_contents($packagePath . '/database/migrations/2026_01_01_000000_create_fixture_records_table.php', <<<'PHP'
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixture_records', function (Blueprint $table): void {
            $table->id();
            $table->string('token_hash');
            $table->string('webhook_secret');
        });
    }
};
PHP);

    $publicSurface = [
        'routeNames' => [$routeName],
        'auth' => 'public',
        'csrfExemptRoutes' => [],
        'signedRoutes' => [],
        'tokenizedRoutes' => [],
        'webhookRoutes' => [],
        'throttledRoutes' => [$routeName],
    ];

    $manifest = [
        'surfaces' => ['frontend'],
        'permissions' => [],
        'database' => [
            'migrations' => true,
        ],
        'performance' => [
            'cacheSafety' => [
                'sensitiveOutput' => false,
            ],
        ],
        'security' => [
            'riskTier' => 'standard',
            'publicSurface' => [
                ...$publicSurface,
                ...$publicSurfaceOverrides,
            ],
            'sensitiveData' => $sensitiveData,
            'publicOutput' => [
                'cacheSafe' => true,
                'forbidAuthoringSurface' => true,
                'forbidSecrets' => true,
                'forbidPublicBladeQueries' => true,
            ],
            'externalHttpClients' => [
                'requiresTimeouts' => false,
                'requiresSecretRedaction' => false,
                'clients' => [],
            ],
            'adminSurface' => [
                'authorization' => 'none',
                'permissions' => [],
            ],
        ],
    ];

    file_put_contents(
        $packagePath . '/capell.json',
        json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL,
    );
}

function deleteSecurityFixtureRoot(string $root): void
{
    if (! is_dir($root)) {
        return;
    }

    $paths = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST,
    );

    foreach ($paths as $path) {
        if (! $path instanceof SplFileInfo) {
            continue;
        }

        if ($path->isDir()) {
            rmdir($path->getPathname());

            continue;
        }

        unlink($path->getPathname());
    }

    rmdir($root);
}
