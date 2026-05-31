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
            $this->mailStatus(),
            $this->storageStatus(),
        ];
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
        $status = in_array($transport, ['array', 'log'], true) ? 'warning' : 'ok';

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
}
