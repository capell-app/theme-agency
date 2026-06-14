<?php

declare(strict_types=1);

namespace Capell\GA4Reports\Data;

use Spatie\LaravelData\Data;

final class GA4ReportsCredentialsStatusData extends Data
{
    public const string STATUS_MISSING = 'missing';

    public const string STATUS_NOT_READABLE = 'not_readable';

    public const string STATUS_INVALID_JSON = 'invalid_json';

    public const string STATUS_INVALID_SERVICE_ACCOUNT = 'invalid_service_account';

    public const string STATUS_VALID = 'valid';

    public function __construct(
        public readonly string $status,
        public readonly bool $readable,
        public readonly bool $valid,
        public readonly string $messageKey,
    ) {}
}
