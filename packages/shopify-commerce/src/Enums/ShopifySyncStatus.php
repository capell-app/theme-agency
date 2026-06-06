<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Enums;

enum ShopifySyncStatus: string
{
    case Queued = 'queued';
    case Running = 'running';
    case Importing = 'importing';
    case Completed = 'completed';
    case Idle = 'idle';
    case Failed = 'failed';
    case Canceled = 'canceled';
    case Revoked = 'revoked';

    /**
     * @return list<string>
     */
    public static function busyValues(): array
    {
        return [
            self::Queued->value,
            self::Running->value,
            self::Importing->value,
        ];
    }

    /**
     * @return list<string>
     */
    public static function runningValues(): array
    {
        return [
            self::Running->value,
            self::Importing->value,
        ];
    }
}
