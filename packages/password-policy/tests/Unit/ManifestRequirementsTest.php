<?php

declare(strict_types=1);

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Contracts\Extensions\ExtensionContribution;
use Capell\Core\Contracts\Extensions\RegistersExtensionSetting;
use Capell\PasswordPolicy\Console\Commands\ExpireStalePasswordsCommand;
use Capell\PasswordPolicy\Console\Commands\PasswordPolicyDoctorCommand;
use Capell\PasswordPolicy\Console\Commands\PrunePasswordHistoryCommand;
use Capell\PasswordPolicy\Console\Commands\RequirePasswordChangeCommand;
use Capell\PasswordPolicy\Filament\Extenders\PasswordPolicyPanelExtender;
use Capell\PasswordPolicy\Filament\Extenders\PasswordPolicyUserFormExtender;
use Capell\PasswordPolicy\Filament\Extenders\PasswordPolicyUserTableExtender;
use Capell\PasswordPolicy\Filament\Pages\ForcedPasswordChangePage;
use Capell\PasswordPolicy\Filament\Pages\PasswordPolicySettingsPage;
use Capell\PasswordPolicy\Filament\Settings\PasswordPolicySettingsSchema;
use Capell\PasswordPolicy\Health\PasswordPolicyHealthCheck;
use Capell\PasswordPolicy\Manifest\PasswordPolicyAdminExtendersContribution;
use Capell\PasswordPolicy\Manifest\PasswordPolicyAdminPagesContribution;
use Capell\PasswordPolicy\Manifest\PasswordPolicyConsoleCommandsContribution;
use Capell\PasswordPolicy\Manifest\PasswordPolicyHealthContribution;
use Capell\PasswordPolicy\Manifest\PasswordPolicySettingsContribution;
use Capell\PasswordPolicy\Settings\PasswordPolicySettings;

require_once dirname(__DIR__) . '/PasswordPolicyTestCase.php';

/**
 * @return array<string, mixed>
 */
function passwordPolicyManifest(): array
{
    return collect(capell_json_file_array(__DIR__ . '/../../capell.json'))->all();
}

it('declares implemented password policy package contributions', function (): void {
    $manifest = passwordPolicyManifest();
    $manifestContributions = $manifest['contributes'] ?? [];
    throw_unless(is_array($manifestContributions), RuntimeException::class, 'Password Policy contributions must be arrays.');
    $contributions = collect($manifestContributions);

    expect(data_get($manifest, 'contributionTraceability.deferredContributions'))->toBe([])
        ->and($contributions->pluck('class')->all())->toContain(
            PasswordPolicyAdminPagesContribution::class,
            PasswordPolicyAdminExtendersContribution::class,
            PasswordPolicySettingsContribution::class,
            PasswordPolicyConsoleCommandsContribution::class,
            PasswordPolicyHealthContribution::class,
        );

    $adminPages = $contributions->firstWhere('class', PasswordPolicyAdminPagesContribution::class);
    $adminExtenders = $contributions->firstWhere('class', PasswordPolicyAdminExtendersContribution::class);
    $settings = $contributions->firstWhere('class', PasswordPolicySettingsContribution::class);
    $consoleCommands = $contributions->firstWhere('class', PasswordPolicyConsoleCommandsContribution::class);
    $healthCheck = $contributions->firstWhere('class', PasswordPolicyHealthContribution::class);
    throw_unless(is_array($adminPages), RuntimeException::class, 'Expected password policy admin page contribution.');
    throw_unless(is_array($adminExtenders), RuntimeException::class, 'Expected password policy admin extender contribution.');
    throw_unless(is_array($settings), RuntimeException::class, 'Expected password policy settings contribution.');
    throw_unless(is_array($consoleCommands), RuntimeException::class, 'Expected password policy console command contribution.');
    throw_unless(is_array($healthCheck), RuntimeException::class, 'Expected password policy health check contribution.');

    expect($adminPages['pageClasses'])->toBe([
        PasswordPolicySettingsPage::class,
        ForcedPasswordChangePage::class,
    ])
        ->and(data_get($adminPages, 'extensionManagementSurface.settingsGroup'))->toBe('password_policy')
        ->and($adminExtenders['extenderClasses'])->toBe([
            PasswordPolicyPanelExtender::class,
            PasswordPolicyUserFormExtender::class,
            PasswordPolicyUserTableExtender::class,
        ])
        ->and($adminExtenders['tags'])->toBe([
            'capell.admin.panel_extenders',
            'capell.admin.user-form-extender',
            'capell.admin.user_table_extenders',
        ])
        ->and($settings['settingsClass'])->toBe(PasswordPolicySettings::class)
        ->and($settings['settingsGroup'])->toBe('password_policy')
        ->and($settings['settingsSchema'])->toBe(PasswordPolicySettingsSchema::class)
        ->and($consoleCommands['commands'])->toBe([
            'capell:password-policy:expire-stale',
            'capell:password-policy:doctor',
            'capell:password-policy:prune-history',
            'capell:password-policy:require-change',
        ])
        ->and($consoleCommands['commandClasses'])->toBe([
            ExpireStalePasswordsCommand::class,
            PasswordPolicyDoctorCommand::class,
            PrunePasswordHistoryCommand::class,
            RequirePasswordChangeCommand::class,
        ])
        ->and($healthCheck['checkClass'])->toBe(PasswordPolicyHealthCheck::class)
        ->and(class_implements(PasswordPolicyAdminPagesContribution::class))->toContain(ExtensionContribution::class)
        ->and(class_implements(PasswordPolicyAdminExtendersContribution::class))->toContain(ExtensionContribution::class)
        ->and(class_implements(PasswordPolicyConsoleCommandsContribution::class))->toContain(ExtensionContribution::class)
        ->and(class_implements(PasswordPolicySettingsContribution::class))->toContain(RegistersExtensionSetting::class)
        ->and(class_implements(PasswordPolicyHealthContribution::class))->toContain(ChecksExtensionHealth::class);
});

it('declares password policy storage required by health checks', function (): void {
    $manifest = passwordPolicyManifest();

    expect(data_get($manifest, 'database.requiredTables'))->toBe(['password_policy_password_histories'])
        ->and(data_get($manifest, 'database.requiredUserColumns'))->toBe(['password_changed_at', 'must_change_password']);
});
