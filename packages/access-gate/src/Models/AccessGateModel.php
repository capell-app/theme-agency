<?php

declare(strict_types=1);

namespace Capell\AccessGate\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Override;

abstract class AccessGateModel extends Model
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    #[Override]
    public function getConnectionName(): ?string
    {
        $connection = config('access-gate.connection');

        return is_string($connection) && $connection !== '' ? $connection : parent::getConnectionName();
    }
}
