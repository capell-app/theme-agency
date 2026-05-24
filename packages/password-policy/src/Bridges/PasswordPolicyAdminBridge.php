<?php

declare(strict_types=1);

namespace Capell\PasswordPolicy\Bridges;

use Capell\Admin\Contracts\Bridges\AdminBridge;
use Capell\Admin\Data\Bridges\AdminBridgeContextData;
use Capell\Admin\Support\Bridges\AdminBridgeRegistrar;
use Capell\PasswordPolicy\Filament\Extenders\PasswordPolicyPanelExtender;
use Capell\PasswordPolicy\Filament\Pages\ForcedPasswordChangePage;

final class PasswordPolicyAdminBridge implements AdminBridge
{
    public function isEnabled(AdminBridgeContextData $context): bool
    {
        return true;
    }

    public function register(AdminBridgeRegistrar $registrar, AdminBridgeContextData $context): void
    {

        $registrar->page(ForcedPasswordChangePage::class);
        $registrar->panelExtender(PasswordPolicyPanelExtender::class);

    }
}
