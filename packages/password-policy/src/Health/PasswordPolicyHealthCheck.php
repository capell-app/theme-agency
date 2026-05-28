<?php

declare(strict_types=1);

namespace Capell\PasswordPolicy\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\PasswordPolicy\Actions\BuildPasswordSecurityPostureReportAction;
use Capell\PasswordPolicy\Data\PasswordSecurityPostureReportData;

final class PasswordPolicyHealthCheck implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    /**
     * @param  list<string>  $panelIds
     */
    public function securityPosture(array $panelIds = ['admin']): PasswordSecurityPostureReportData
    {
        return BuildPasswordSecurityPostureReportAction::run($panelIds);
    }
}
