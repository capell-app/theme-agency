<?php

declare(strict_types=1);

namespace Capell\PasswordPolicy\Bridges;

use Capell\Admin\Contracts\Bridges\AdminBridge;
use Capell\Admin\Data\Bridges\AdminBridgeContextData;
use Capell\Admin\Data\Extensions\ExtensionManagementSurfaceData;
use Capell\Admin\Support\Bridges\AdminBridgeRegistrar;
use Capell\PasswordPolicy\Filament\Extenders\PasswordPolicyPanelExtender;
use Capell\PasswordPolicy\Filament\Extenders\PasswordPolicyUserFormExtender;
use Capell\PasswordPolicy\Filament\Extenders\PasswordPolicyUserTableExtender;
use Capell\PasswordPolicy\Filament\Pages\ForcedPasswordChangePage;
use Capell\PasswordPolicy\Filament\Pages\PasswordPolicySettingsPage;
use Filament\Support\Icons\Heroicon;

final class PasswordPolicyAdminBridge implements AdminBridge
{
    public function isEnabled(AdminBridgeContextData $context): bool
    {
        return true;
    }

    public function register(AdminBridgeRegistrar $registrar, AdminBridgeContextData $context): void
    {
        $registrar->page(ForcedPasswordChangePage::class);
        $registrar->page(PasswordPolicySettingsPage::class);
        $registrar->panelExtender(PasswordPolicyPanelExtender::class);

        $registrar->extensionManagementSurface(ExtensionManagementSurfaceData::settings(
            packageName: $context->packageName,
            label: 'capell-password-policy::settings.title',
            settingsGroup: 'password_policy',
            icon: Heroicon::OutlinedKey,
        ));
        $registrar->userFormExtender(PasswordPolicyUserFormExtender::class);
        $registrar->userTableExtender(PasswordPolicyUserTableExtender::class);
    }
}
