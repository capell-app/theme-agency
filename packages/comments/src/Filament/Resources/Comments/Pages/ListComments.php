<?php

declare(strict_types=1);

namespace Capell\Comments\Filament\Resources\Comments\Pages;

use Capell\Comments\Filament\Resources\Comments\CommentResource;
use Filament\Resources\Pages\ListRecords;

class ListComments extends ListRecords
{
    protected static string $resource = CommentResource::class;
}
