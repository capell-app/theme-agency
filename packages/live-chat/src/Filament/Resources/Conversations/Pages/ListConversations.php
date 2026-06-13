<?php

declare(strict_types=1);

namespace Capell\LiveChat\Filament\Resources\Conversations\Pages;

use Capell\LiveChat\Filament\Resources\Conversations\ConversationResource;
use Filament\Resources\Pages\ListRecords;

final class ListConversations extends ListRecords
{
    protected static string $resource = ConversationResource::class;
}
