<?php

declare(strict_types=1);

namespace Capell\Comments\Tests\Fixtures;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class UnregisteredCommentable extends Model
{
    use HasFactory;

    protected $guarded = [];
}
