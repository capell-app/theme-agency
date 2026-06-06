<?php

declare(strict_types=1);

namespace Capell\LoginAudit\Actions;

use Capell\LoginAudit\Models\LoginAudit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lorisleiva\Actions\Concerns\AsAction;

final class BuildLoginAuditsCsvAction
{
    use AsAction;

    public function handle(?Model $authenticatable = null): string
    {
        $stream = fopen('php://temp', 'r+');

        if ($stream === false) {
            return '';
        }

        fputcsv($stream, [
            'id',
            'authenticatable_type',
            'authenticatable_id',
            'authenticatable_name',
            'login_successful',
            'ip_address',
            'user_agent',
            'device_name',
            'is_trusted',
            'login_at',
            'last_activity_at',
            'last_seen_at',
            'logout_at',
            'cleared_by_user',
            'is_suspicious',
            'suspicious_reason',
        ]);

        $this->query($authenticatable)
            ->cursor()
            ->each(static function (LoginAudit $loginAudit) use ($stream): void {
                fputcsv($stream, [
                    $loginAudit->getKey(),
                    $loginAudit->authenticatable_type,
                    $loginAudit->authenticatable_id,
                    self::authenticatableName($loginAudit),
                    $loginAudit->login_successful ? '1' : '0',
                    $loginAudit->ip_address,
                    $loginAudit->user_agent,
                    $loginAudit->getAttribute('device_name'),
                    $loginAudit->getAttribute('is_trusted') ? '1' : '0',
                    $loginAudit->login_at?->toISOString(),
                    $loginAudit->last_activity_at?->toISOString(),
                    $loginAudit->last_seen_at?->toISOString(),
                    $loginAudit->logout_at?->toISOString(),
                    $loginAudit->cleared_by_user ? '1' : '0',
                    $loginAudit->getAttribute('is_suspicious') ? '1' : '0',
                    $loginAudit->getAttribute('suspicious_reason'),
                ]);
            });

        rewind($stream);

        $contents = stream_get_contents($stream);
        fclose($stream);

        return is_string($contents) ? $contents : '';
    }

    /**
     * @return Builder<LoginAudit>
     */
    private function query(?Model $authenticatable): Builder
    {
        return LoginAudit::query()
            ->with(['authenticatable'])
            ->when($authenticatable instanceof Model, static fn (Builder $query): Builder => $query
                ->where('authenticatable_type', $authenticatable->getMorphClass())
                ->where('authenticatable_id', $authenticatable->getKey()))
            ->orderByDesc('login_at')
            ->orderByDesc('id');
    }

    private static function authenticatableName(LoginAudit $loginAudit): string
    {
        $authenticatable = $loginAudit->authenticatable;

        if (! $authenticatable instanceof Model) {
            return '';
        }

        $name = $authenticatable->getAttribute('name');

        return is_scalar($name) ? (string) $name : '';
    }
}
