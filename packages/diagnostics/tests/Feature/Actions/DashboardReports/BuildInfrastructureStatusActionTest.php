<?php

declare(strict_types=1);

use Capell\Diagnostics\Actions\DashboardReports\BuildInfrastructureStatusAction;
use Illuminate\Support\Facades\File;

it('reports configured mail and local storage infrastructure status', function (): void {
    $storageRoot = storage_path('framework/testing/diagnostics-storage');
    File::ensureDirectoryExists($storageRoot);

    config()->set('mail.default', 'smtp');
    config()->set('mail.mailers.smtp', ['transport' => 'smtp']);
    config()->set('filesystems.default', 'diagnostics');
    config()->set('filesystems.disks.diagnostics', [
        'driver' => 'local',
        'root' => $storageRoot,
    ]);

    $statuses = collect(BuildInfrastructureStatusAction::run())->keyBy('key');

    expect($statuses->get('mail')?->status)->toBe('ok')
        ->and($statuses->get('storage')?->status)->toBe('ok');
});

it('warns for non-delivering mail transports and errors for missing storage disk', function (): void {
    config()->set('mail.default', 'log');
    config()->set('mail.mailers.log', ['transport' => 'log']);
    config()->set('filesystems.default', 'missing');
    config()->set('filesystems.disks.missing');

    $statuses = collect(BuildInfrastructureStatusAction::run())->keyBy('key');

    expect($statuses->get('mail')?->status)->toBe('warning')
        ->and($statuses->get('storage')?->status)->toBe('error');
});
