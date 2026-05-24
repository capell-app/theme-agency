<?php

declare(strict_types=1);

namespace Capell\Comments\Filament\Resources\CommentAuthors\Pages;

use Capell\Comments\Filament\Resources\CommentAuthors\CommentAuthorResource;
use Filament\Resources\Pages\ListRecords;

class ListCommentAuthors extends ListRecords
{
    protected static string $resource = CommentAuthorResource::class;
}
