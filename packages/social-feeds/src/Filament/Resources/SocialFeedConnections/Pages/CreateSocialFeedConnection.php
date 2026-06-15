<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Filament\Resources\SocialFeedConnections\Pages;

use Capell\SocialFeeds\Filament\Resources\SocialFeedConnections\SocialFeedConnectionResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateSocialFeedConnection extends CreateRecord
{
    protected static string $resource = SocialFeedConnectionResource::class;
}
