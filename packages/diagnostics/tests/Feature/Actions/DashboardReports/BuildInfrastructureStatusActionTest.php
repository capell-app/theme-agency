<?php

declare(strict_types=1);

use Capell\Diagnostics\Actions\DashboardReports\BuildInfrastructureStatusAction;
use Illuminate\Support\Facades\File;

it('reports configured mail and local storage infrastructure status', function (): void {
    $storageRoot = storage_path('framework/testing/diagnostics-storage');
    File::ensureDirectoryExists($storageRoot);

    config()->set('mail.default', 'smtp');
    config()->set('mail.mailers.smtp', ['transport' => 'smtp']);
    config()->set('cache.default', 'redis');
    config()->set('cache.stores.redis', ['driver' => 'redis']);
    config()->set('queue.default', 'redis');
    config()->set('queue.connections.redis', ['driver' => 'redis']);
    config()->set('filesystems.default', 'diagnostics');
    config()->set('filesystems.disks.diagnostics', [
        'driver' => 'local',
        'root' => $storageRoot,
    ]);

    $statuses = collect(BuildInfrastructureStatusAction::run())->keyBy('key');

    expect($statuses->get('cache')?->status)->toBe('ok')
        ->and($statuses->get('queue')?->status)->toBe('ok')
        ->and($statuses->get('mail')?->status)->toBe('ok')
        ->and($statuses->get('storage')?->status)->toBe('ok');
});

it('warns for local-only cache queue and mail transports and errors for missing storage disk', function (): void {
    config()->set('cache.default', 'array');
    config()->set('cache.stores.array', ['driver' => 'array']);
    config()->set('queue.default', 'sync');
    config()->set('queue.connections.sync', ['driver' => 'sync']);
    config()->set('mail.default', 'log');
    config()->set('mail.mailers.log', ['transport' => 'log']);
    config()->set('filesystems.default', 'missing');
    config()->set('filesystems.disks.missing');

    $statuses = collect(BuildInfrastructureStatusAction::run())->keyBy('key');

    expect($statuses->get('cache')?->status)->toBe('warning')
        ->and($statuses->get('queue')?->status)->toBe('warning')
        ->and($statuses->get('mail')?->status)->toBe('warning')
        ->and($statuses->get('storage')?->status)->toBe('error');
});

it('errors when cache stores or queue connections are missing', function (): void {
    config()->set('cache.default', 'missing');
    config()->set('cache.stores.missing');
    config()->set('queue.default', 'missing');
    config()->set('queue.connections.missing');

    $statuses = collect(BuildInfrastructureStatusAction::run())->keyBy('key');

    expect($statuses->get('cache')?->status)->toBe('error')
        ->and($statuses->get('queue')?->status)->toBe('error');
});

it('errors when infrastructure defaults are blank or referenced mailers are missing', function (): void {
    config()->set('cache.default', '');
    config()->set('queue.default');
    config()->set('mail.default', 'missing');
    config()->set('mail.mailers.missing');
    config()->set('filesystems.default', '');

    $statuses = collect(BuildInfrastructureStatusAction::run())->keyBy('key');

    expect($statuses->get('cache')?->detail)->toBe(__('capell-diagnostics::package.infrastructure_cache_missing_default'))
        ->and($statuses->get('queue')?->detail)->toBe(__('capell-diagnostics::package.infrastructure_queue_missing_default'))
        ->and($statuses->get('mail')?->detail)->toBe(__('capell-diagnostics::package.infrastructure_mail_missing_mailer', ['mailer' => 'missing']))
        ->and($statuses->get('storage')?->detail)->toBe(__('capell-diagnostics::package.infrastructure_storage_missing_default'));
});

it('reports non-local storage as configured and local disks with missing roots as errors', function (): void {
    config()->set('cache.default', 'redis');
    config()->set('cache.stores.redis', ['driver' => 'redis']);
    config()->set('queue.default', 'redis');
    config()->set('queue.connections.redis', ['driver' => 'redis']);
    config()->set('mail.default', 'smtp');
    config()->set('mail.mailers.smtp', ['transport' => 'smtp']);
    config()->set('filesystems.default', 's3');
    config()->set('filesystems.disks.s3', ['driver' => 's3']);

    $nonLocalStatuses = collect(BuildInfrastructureStatusAction::run())->keyBy('key');

    config()->set('filesystems.default', 'broken-local');
    config()->set('filesystems.disks.broken-local', [
        'driver' => 'local',
        'root' => storage_path('framework/testing/missing-diagnostics-root'),
    ]);

    $missingRootStatuses = collect(BuildInfrastructureStatusAction::run())->keyBy('key');

    expect($nonLocalStatuses->get('storage')?->status)->toBe('ok')
        ->and($nonLocalStatuses->get('storage')?->detail)->toBe(__('capell-diagnostics::package.infrastructure_storage_configured', [
            'disk' => 's3',
            'driver' => 's3',
        ]))
        ->and($missingRootStatuses->get('storage')?->status)->toBe('error')
        ->and($missingRootStatuses->get('storage')?->detail)->toBe(__('capell-diagnostics::package.infrastructure_storage_missing_root', [
            'disk' => 'broken-local',
        ]));
});
