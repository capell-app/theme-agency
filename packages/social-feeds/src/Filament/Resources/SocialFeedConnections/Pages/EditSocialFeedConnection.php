<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Filament\Resources\SocialFeedConnections\Pages;

use Capell\SocialFeeds\Filament\Resources\SocialFeedConnections\SocialFeedConnectionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditSocialFeedConnection extends EditRecord
{
    protected static string $resource = SocialFeedConnectionResource::class;

    /**
     * @return array<int, DeleteAction>
     */
    #[Override]
    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
