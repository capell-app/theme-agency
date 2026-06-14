<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Filament\Resources\SocialFeedConnections\Pages;

use Capell\SocialFeeds\Filament\Resources\SocialFeedConnections\SocialFeedConnectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListSocialFeedConnections extends ListRecords
{
    protected static string $resource = SocialFeedConnectionResource::class;

    /**
     * @return array<int, CreateAction>
     */
    #[Override]
    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
