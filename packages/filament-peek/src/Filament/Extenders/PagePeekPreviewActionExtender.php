<?php

declare(strict_types=1);

namespace Capell\FilamentPeek\Filament\Extenders;

use Capell\Admin\Contracts\Extenders\PagePreviewActionExtender;
use Capell\FilamentPeek\Filament\Actions\PeekPagePreviewAction;
use Filament\Actions\Action;

final class PagePeekPreviewActionExtender implements PagePreviewActionExtender
{
    /** @return array<int, Action> */
    public function actions(): array
    {
        return [
            PeekPagePreviewAction::make(),
        ];
    }
}
