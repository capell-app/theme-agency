<?php

declare(strict_types=1);

namespace Capell\PasswordPolicy\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\PasswordPolicy\Actions\BuildPasswordSecurityPostureReportAction;
use Capell\PasswordPolicy\Data\PasswordSecurityPostureReportData;
use Capell\PasswordPolicy\Data\ResolvedPasswordPolicySettingsData;
use Capell\PasswordPolicy\Support\PasswordPolicySettingsResolver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

final class PasswordPolicyHealthCheck implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    /**
     * Runs the package's real install-health diagnostics.
     *
     * Each enabled policy control requires its backing persistence to be
     * present; an enabled control with missing storage silently fails to
     * enforce, so those combinations are reported as failures.
     *
     * @return Collection<int, DoctorCheckResultData>
     */
    public static function runDiagnostics(): Collection
    {
        $check = new self;

        return collect([
            $check->forcedChangeColumnCheck(),
            $check->passwordExpiryColumnCheck(),
            $check->passwordHistoryTableCheck(),
        ]);
    }

    public static function passed(): bool
    {
        return self::runDiagnostics()
            ->every(static fn (DoctorCheckResultData $result): bool => $result->passed);
    }

    /**
     * @param  list<string>  $panelIds
     */
    public function securityPosture(array $panelIds = ['admin']): PasswordSecurityPostureReportData
    {
        return BuildPasswordSecurityPostureReportAction::run($panelIds);
    }

    /**
     * Asserts the forced-change column exists when forced change is enabled.
     */
    public function forcedChangeColumnCheck(): DoctorCheckResultData
    {
        $settings = $this->settings();
        $columnInstalled = $this->hasUserColumn('must_change_password');
        $satisfied = ! $settings->forceChangeEnabled || $columnInstalled;

        return new DoctorCheckResultData(
            label: 'Forced password change column',
            passed: $satisfied,
            message: $satisfied
                ? 'Forced password change is either disabled or backed by the must_change_password column.'
                : 'Forced password change is enabled but the users.must_change_password column is missing, so the policy cannot be enforced.',
            remediation: $satisfied
                ? null
                : 'Run the Password Policy migrations to add the must_change_password column, or disable forced password change.',
        );
    }

    /**
     * Asserts the expiry tracking column exists when expiry is enabled.
     */
    public function passwordExpiryColumnCheck(): DoctorCheckResultData
    {
        $settings = $this->settings();
        $columnInstalled = $this->hasUserColumn('password_changed_at');
        $satisfied = ! $settings->passwordExpiryEnabled || $columnInstalled;

        return new DoctorCheckResultData(
            label: 'Password expiry column',
            passed: $satisfied,
            message: $satisfied
                ? 'Password expiry is either disabled or backed by the password_changed_at column.'
                : 'Password expiry is enabled but the users.password_changed_at column is missing, so expiry cannot be evaluated.',
            remediation: $satisfied
                ? null
                : 'Run the Password Policy migrations to add the password_changed_at column, or disable password expiry.',
        );
    }

    /**
     * Asserts the history table exists when history blocking is enabled.
     */
    public function passwordHistoryTableCheck(): DoctorCheckResultData
    {
        $settings = $this->settings();
        $tableInstalled = Schema::hasTable('password_policy_password_histories');
        $satisfied = ! $settings->passwordHistoryEnabled || $tableInstalled;

        return new DoctorCheckResultData(
            label: 'Password history table',
            passed: $satisfied,
            message: $satisfied
                ? 'Password history blocking is either disabled or backed by the password_policy_password_histories table.'
                : 'Password history blocking is enabled but the password_policy_password_histories table is missing, so reuse cannot be detected.',
            remediation: $satisfied
                ? null
                : 'Run the Password Policy migrations to create the password_policy_password_histories table, or disable password history blocking.',
        );
    }

    private function hasUserColumn(string $columnName): bool
    {
        return Schema::hasTable('users')
            && Schema::hasColumn('users', $columnName);
    }

    private function settings(): ResolvedPasswordPolicySettingsData
    {
        return resolve(PasswordPolicySettingsResolver::class)->settings();
    }
}
