<?php

declare(strict_types=1);

namespace Capell\PasswordPolicy\Actions;

use Capell\Core\Support\Database\RuntimeSchemaState;
use Capell\PasswordPolicy\Data\PasswordSecurityPostureReportData;
use Capell\PasswordPolicy\Filament\Pages\ForcedPasswordChangePage;
use Capell\PasswordPolicy\Settings\PasswordPolicySettings;
use Lorisleiva\Actions\Concerns\AsAction;

final class BuildPasswordSecurityPostureReportAction
{
    use AsAction;

    /**
     * @param  list<string>  $panelIds
     */
    public function handle(array $panelIds = ['admin']): PasswordSecurityPostureReportData
    {
        $settings = PasswordPolicySettings::instance();

        return new PasswordSecurityPostureReportData(
            forceChangeEnabled: $settings->force_change_enabled,
            passwordExpiryEnabled: $settings->password_expiry_enabled,
            passwordHistoryEnabled: $settings->password_history_enabled,
            compromisedPasswordChecksEnabled: $settings->compromised_password_checks_enabled,
            userColumnsInstalled: $this->userColumnsInstalled(),
            historyTableInstalled: resolve(RuntimeSchemaState::class)->hasTable('password_policy_password_histories'),
            forcedChangeUrls: $this->forcedChangeUrls($panelIds),
        );
    }

    private function userColumnsInstalled(): bool
    {
        $schema = resolve(RuntimeSchemaState::class);

        return $schema->hasTable('users')
            && $schema->hasColumn('users', 'password_changed_at')
            && $schema->hasColumn('users', 'must_change_password');
    }

    /**
     * @param  list<string>  $panelIds
     * @return array<string, string>
     */
    private function forcedChangeUrls(array $panelIds): array
    {
        $urls = [];

        foreach ($panelIds as $panelId) {
            if ($panelId === '') {
                continue;
            }

            $urls[$panelId] = ForcedPasswordChangePage::getUrl(panel: $panelId);
        }

        return $urls;
    }
}
