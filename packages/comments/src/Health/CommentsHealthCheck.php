<?php

declare(strict_types=1);

namespace Capell\Comments\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;

final class CommentsHealthCheck implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
