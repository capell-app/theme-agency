<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Filament\Resources\SocialFeedItems\Pages;

use Capell\SocialFeeds\Filament\Resources\SocialFeedItems\SocialFeedItemResource;
use Filament\Resources\Pages\ListRecords;

final class ListSocialFeedItems extends ListRecords
{
    protected static string $resource = SocialFeedItemResource::class;
}
