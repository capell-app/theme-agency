<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Actions\DashboardReports;

use Capell\Diagnostics\Data\InfrastructureStatusData;
use Illuminate\Support\Facades\File;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static array<int, InfrastructureStatusData> run()
 */
final class BuildInfrastructureStatusAction
{
    use AsAction;

    /**
     * @return array<int, InfrastructureStatusData>
     */
    public function handle(): array
    {
        return [
            $this->cacheStatus(),
            $this->queueStatus(),
            $this->mailStatus(),
            $this->storageStatus(),
        ];
    }

    private function cacheStatus(): InfrastructureStatusData
    {
        $defaultStore = config('cache.default');

        if (! is_string($defaultStore) || $defaultStore === '') {
            return $this->status(
                key: 'cache',
                label: (string) __('capell-diagnostics::package.infrastructure_cache'),
                status: 'error',
                detail: (string) __('capell-diagnostics::package.infrastructure_cache_missing_default'),
            );
        }

        $storeConfig = config('cache.stores.' . $defaultStore);

        if (! is_array($storeConfig)) {
            return $this->status(
                key: 'cache',
                label: (string) __('capell-diagnostics::package.infrastructure_cache'),
                status: 'error',
                detail: (string) __('capell-diagnostics::package.infrastructure_cache_missing_store', ['store' => $defaultStore]),
            );
        }

        $driver = $storeConfig['driver'] ?? $defaultStore;
        $status = $this->driverNeedsWarning($driver, 'capell-diagnostics.infrastructure.warning_cache_drivers')
            ? 'warning'
            : 'ok';

        return $this->status(
            key: 'cache',
            label: (string) __('capell-diagnostics::package.infrastructure_cache'),
            status: $status,
            detail: (string) __('capell-diagnostics::package.infrastructure_cache_configured', [
                'store' => $defaultStore,
                'driver' => is_string($driver) ? $driver : 'unknown',
            ]),
        );
    }

    private function queueStatus(): InfrastructureStatusData
    {
        $defaultConnection = config('queue.default');

        if (! is_string($defaultConnection) || $defaultConnection === '') {
            return $this->status(
                key: 'queue',
                label: (string) __('capell-diagnostics::package.infrastructure_queue'),
                status: 'error',
                detail: (string) __('capell-diagnostics::package.infrastructure_queue_missing_default'),
            );
        }

        $connectionConfig = config('queue.connections.' . $defaultConnection);

        if (! is_array($connectionConfig)) {
            return $this->status(
                key: 'queue',
                label: (string) __('capell-diagnostics::package.infrastructure_queue'),
                status: 'error',
                detail: (string) __('capell-diagnostics::package.infrastructure_queue_missing_connection', ['connection' => $defaultConnection]),
            );
        }

        $driver = $connectionConfig['driver'] ?? $defaultConnection;
        $status = $this->driverNeedsWarning($driver, 'capell-diagnostics.infrastructure.warning_queue_drivers')
            ? 'warning'
            : 'ok';

        return $this->status(
            key: 'queue',
            label: (string) __('capell-diagnostics::package.infrastructure_queue'),
            status: $status,
            detail: (string) __('capell-diagnostics::package.infrastructure_queue_configured', [
                'connection' => $defaultConnection,
                'driver' => is_string($driver) ? $driver : 'unknown',
            ]),
        );
    }

    private function mailStatus(): InfrastructureStatusData
    {
        $defaultMailer = config('mail.default');

        if (! is_string($defaultMailer) || $defaultMailer === '') {
            return $this->status(
                key: 'mail',
                label: (string) __('capell-diagnostics::package.infrastructure_mail'),
                status: 'error',
                detail: (string) __('capell-diagnostics::package.infrastructure_mail_missing_default'),
            );
        }

        $mailerConfig = config('mail.mailers.' . $defaultMailer);

        if (! is_array($mailerConfig)) {
            return $this->status(
                key: 'mail',
                label: (string) __('capell-diagnostics::package.infrastructure_mail'),
                status: 'error',
                detail: (string) __('capell-diagnostics::package.infrastructure_mail_missing_mailer', ['mailer' => $defaultMailer]),
            );
        }

        $transport = $mailerConfig['transport'] ?? $defaultMailer;
        $status = $this->driverNeedsWarning($transport, 'capell-diagnostics.infrastructure.warning_mail_transports')
            ? 'warning'
            : 'ok';

        return $this->status(
            key: 'mail',
            label: (string) __('capell-diagnostics::package.infrastructure_mail'),
            status: $status,
            detail: (string) __('capell-diagnostics::package.infrastructure_mail_configured', [
                'mailer' => $defaultMailer,
                'transport' => is_string($transport) ? $transport : 'unknown',
            ]),
        );
    }

    private function storageStatus(): InfrastructureStatusData
    {
        $defaultDisk = config('filesystems.default');

        if (! is_string($defaultDisk) || $defaultDisk === '') {
            return $this->status(
                key: 'storage',
                label: (string) __('capell-diagnostics::package.infrastructure_storage'),
                status: 'error',
                detail: (string) __('capell-diagnostics::package.infrastructure_storage_missing_default'),
            );
        }

        $diskConfig = config('filesystems.disks.' . $defaultDisk);

        if (! is_array($diskConfig)) {
            return $this->status(
                key: 'storage',
                label: (string) __('capell-diagnostics::package.infrastructure_storage'),
                status: 'error',
                detail: (string) __('capell-diagnostics::package.infrastructure_storage_missing_disk', ['disk' => $defaultDisk]),
            );
        }

        $driver = $diskConfig['driver'] ?? 'unknown';

        if ($driver !== 'local') {
            return $this->status(
                key: 'storage',
                label: (string) __('capell-diagnostics::package.infrastructure_storage'),
                status: 'ok',
                detail: (string) __('capell-diagnostics::package.infrastructure_storage_configured', [
                    'disk' => $defaultDisk,
                    'driver' => is_string($driver) ? $driver : 'unknown',
                ]),
            );
        }

        $root = $diskConfig['root'] ?? null;

        if (! is_string($root) || $root === '' || ! File::isDirectory($root)) {
            return $this->status(
                key: 'storage',
                label: (string) __('capell-diagnostics::package.infrastructure_storage'),
                status: 'error',
                detail: (string) __('capell-diagnostics::package.infrastructure_storage_missing_root', ['disk' => $defaultDisk]),
            );
        }

        return $this->status(
            key: 'storage',
            label: (string) __('capell-diagnostics::package.infrastructure_storage'),
            status: File::isWritable($root) ? 'ok' : 'warning',
            detail: (string) __('capell-diagnostics::package.infrastructure_storage_local_root', [
                'disk' => $defaultDisk,
                'root' => $root,
            ]),
        );
    }

    private function status(string $key, string $label, string $status, string $detail): InfrastructureStatusData
    {
        return new InfrastructureStatusData(
            key: $key,
            label: $label,
            status: $status,
            detail: $detail,
        );
    }

    private function driverNeedsWarning(mixed $driver, string $configKey): bool
    {
        if (! is_string($driver)) {
            return false;
        }

        $warningDrivers = config($configKey);

        if (! is_array($warningDrivers)) {
            return false;
        }

        return in_array($driver, array_filter($warningDrivers, is_string(...)), true);
    }
}
