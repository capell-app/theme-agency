<?php

declare(strict_types=1);

namespace Capell\LoginAudit\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\LoginAudit\Http\Middleware\UserActivityMiddleware;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

final class LoginAuditHealthCheck implements ChecksExtensionHealth
{
    /**
     * The configured authentication-log listener keys that must be wired for
     * the package to capture login, failed-login, and logout events.
     *
     * @var list<string>
     */
    private const array REQUIRED_LISTENER_KEYS = [
        'login',
        'failed',
        'logout',
        'other-device-logout',
    ];

    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    /**
     * @return Collection<int, DoctorCheckResultData>
     */
    public static function runDiagnostics(): Collection
    {
        $check = new self;

        return collect([
            $check->storageTableCheck(),
            $check->eventListenersCheck(),
            $check->activityMiddlewareAliasCheck(),
        ]);
    }

    public static function passed(): bool
    {
        return self::runDiagnostics()
            ->every(static fn (DoctorCheckResultData $result): bool => $result->passed);
    }

    /**
     * Asserts the login audit storage table exists so events can be persisted.
     */
    public function storageTableCheck(): DoctorCheckResultData
    {
        $tableName = $this->tableName();
        $tableExists = $this->hasStorageTable();

        return new DoctorCheckResultData(
            label: 'Login audit storage table',
            passed: $tableExists,
            message: $tableExists
                ? sprintf('The %s table is present and ready to record authentication events.', $tableName)
                : sprintf('The %s table is missing, so authentication events cannot be recorded.', $tableName),
            remediation: $tableExists
                ? null
                : 'Run the Capell migrations to create the login audit storage table.',
        );
    }

    /**
     * Asserts the vendor authentication-log listeners are configured so login,
     * failed-login, and logout events are captured.
     */
    public function eventListenersCheck(): DoctorCheckResultData
    {
        $missingListenerKeys = $this->missingListenerKeys();

        return new DoctorCheckResultData(
            label: 'Login audit event listeners',
            passed: $missingListenerKeys === [],
            message: $missingListenerKeys === []
                ? 'All authentication event listeners are configured for capture.'
                : 'Missing authentication event listeners: ' . implode(', ', $missingListenerKeys) . '.',
            remediation: $missingListenerKeys === []
                ? null
                : 'Restore the listeners map in config/login-audit.php so login events are recorded.',
        );
    }

    /**
     * Asserts the frontend activity middleware alias is registered so logged-in
     * frontend users have their last-seen activity updated.
     */
    public function activityMiddlewareAliasCheck(): DoctorCheckResultData
    {
        $aliasRegistered = $this->hasActivityMiddlewareAlias();

        return new DoctorCheckResultData(
            label: 'Login audit frontend activity middleware',
            passed: $aliasRegistered,
            message: $aliasRegistered
                ? 'The frontend.activity middleware alias is registered.'
                : 'The frontend.activity middleware alias is not registered.',
            remediation: $aliasRegistered
                ? null
                : 'Ensure LoginAuditServiceProvider registers the frontend.activity middleware alias.',
        );
    }

    public function hasStorageTable(): bool
    {
        return Schema::hasTable($this->tableName());
    }

    /**
     * @return list<string>
     */
    public function missingListenerKeys(): array
    {
        $listeners = config('login-audit.listeners', []);

        if (! is_array($listeners)) {
            return self::REQUIRED_LISTENER_KEYS;
        }

        return array_values(array_filter(
            self::REQUIRED_LISTENER_KEYS,
            static function (string $listenerKey) use ($listeners): bool {
                $listenerClass = $listeners[$listenerKey] ?? null;

                return ! is_string($listenerClass) || ! class_exists($listenerClass);
            },
        ));
    }

    public function hasActivityMiddlewareAlias(): bool
    {
        $registeredAlias = Route::getMiddleware()['frontend.activity'] ?? null;

        return $registeredAlias === UserActivityMiddleware::class;
    }

    private function tableName(): string
    {
        $tableName = config('login-audit.table_name', 'login_audit');

        return is_string($tableName) && $tableName !== '' ? $tableName : 'login_audit';
    }
}
