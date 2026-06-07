<?php

declare(strict_types=1);

namespace Capell\PasswordPolicy\Events;

use Illuminate\Database\Eloquent\Model;

final readonly class PasswordChanged
{
    public function __construct(
        public Model $user,
        public string $source,
    ) {}
}
