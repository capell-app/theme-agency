<?php

declare(strict_types=1);

namespace Capell\Comments\Enums;

use Capell\Comments\Filament\Resources\CommentAuthors\CommentAuthorResource;
use Capell\Comments\Filament\Resources\Comments\CommentResource;

enum ResourceEnum: string
{
    case Comment = CommentResource::class;
    case CommentAuthor = CommentAuthorResource::class;
}
