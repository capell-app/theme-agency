<?php

declare(strict_types=1);

namespace Capell\AccessGate\Tests\Fixtures\Autoload;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as AuthenticatableUser;

final class AccessGateTestUser extends AuthenticatableUser
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    protected $guarded = [];
}
