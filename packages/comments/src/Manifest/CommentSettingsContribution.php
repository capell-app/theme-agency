<?php

declare(strict_types=1);

namespace Capell\Comments\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;
use Capell\Core\Contracts\Extensions\RegistersExtensionSetting;

final class CommentSettingsContribution implements ExtensionContribution, RegistersExtensionSetting
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
