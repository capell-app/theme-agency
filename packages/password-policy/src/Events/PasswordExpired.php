<?php

declare(strict_types=1);

namespace Capell\PasswordPolicy\Events;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

final readonly class PasswordExpired
{
    public function __construct(
        public Model $user,
        public CarbonImmutable $expiredAt,
    ) {}
}
