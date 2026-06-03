<?php

declare(strict_types=1);

namespace Capell\Comments\Tests\Fixtures;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class CommentAdminUrlCommentable extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    protected $guarded = [];

    public function getUrl(): string
    {
        return (string) $this->getAttribute('url');
    }
}
