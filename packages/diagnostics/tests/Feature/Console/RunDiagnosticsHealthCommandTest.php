<?php

declare(strict_types=1);

use Capell\Diagnostics\Tests\Fixtures\Health\PassingFixtureHealthCheck;
use Capell\Diagnostics\Tests\Fixtures\Health\StubFixtureHealthCheck;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

it('requires a single health export format', function (): void {
    $this->artisan('capell:diagnostics:health', [
        '--json' => true,
        '--csv' => true,
    ])
        ->expectsOutputToContain((string) __('capell-diagnostics::package.health_command_single_export_format'))
        ->assertExitCode(SymfonyCommand::FAILURE);
});

it('allows stubbed health checks outside strict mode', function (): void {
    $packagesPath = commandHealthChecksPackagesPath([
        ['key' => 'fixture.stub', 'label' => 'Stub', 'class' => StubFixtureHealthCheck::class, 'severity' => 'warning'],
    ]);

    config(['capell-diagnostics.health_checks.local_packages_path' => $packagesPath]);

    try {
        $this->artisan('capell:diagnostics:health')
            ->expectsOutputToContain('1 stub')
            ->assertExitCode(SymfonyCommand::SUCCESS);
    } finally {
        File::deleteDirectory($packagesPath);
    }
});

it('fails strict mode when declared health checks are stubbed', function (): void {
    $packagesPath = commandHealthChecksPackagesPath([
        ['key' => 'fixture.passing', 'label' => 'Passing', 'class' => PassingFixtureHealthCheck::class, 'severity' => 'warning'],
        ['key' => 'fixture.stub', 'label' => 'Stub', 'class' => StubFixtureHealthCheck::class, 'severity' => 'warning'],
    ]);

    config(['capell-diagnostics.health_checks.local_packages_path' => $packagesPath]);

    try {
        $this->artisan('capell:diagnostics:health', ['--strict' => true])
            ->expectsOutputToContain('1 stub')
            ->assertExitCode(SymfonyCommand::FAILURE);
    } finally {
        File::deleteDirectory($packagesPath);
    }
});

/**
 * @param  list<array{key: string, label: string, class: string, severity: string}>  $healthChecks
 */
function commandHealthChecksPackagesPath(array $healthChecks): string
{
    $packagesPath = sys_get_temp_dir() . '/capell_command_health_checks_' . uniqid();
    $packagePath = $packagesPath . '/fixture-package';
    File::ensureDirectoryExists($packagePath);

    File::put($packagePath . '/capell.json', json_encode([
        'name' => 'capell-app/fixture-package',
        'slug' => 'fixture-package',
        'healthChecks' => $healthChecks,
    ], JSON_THROW_ON_ERROR));

    return $packagesPath;
}
