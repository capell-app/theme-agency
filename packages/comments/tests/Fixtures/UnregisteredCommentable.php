<?php

declare(strict_types=1);

namespace Capell\Comments\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

final class UnregisteredCommentable extends Model
{
    protected $guarded = [];
}
