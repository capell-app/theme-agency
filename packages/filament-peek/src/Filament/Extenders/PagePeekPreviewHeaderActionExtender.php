<?php

declare(strict_types=1);

namespace Capell\FilamentPeek\Filament\Extenders;

use Capell\Admin\Contracts\Extenders\ResourceHeaderActionExtender;
use Capell\Admin\Filament\Resources\Pages\Pages\EditPage;
use Capell\FilamentPeek\Filament\Actions\PeekPagePreviewAction;
use Filament\Actions\Action;

final class PagePeekPreviewHeaderActionExtender implements ResourceHeaderActionExtender
{
    public function supports(string $pageClass): bool
    {
        return $pageClass === EditPage::class;
    }

    /** @return array<int, Action> */
    public function actions(): array
    {
        return [
            PeekPagePreviewAction::make(),
        ];
    }
}
