<?php

declare(strict_types=1);

namespace Capell\FilamentPeek\Filament\Extenders;

use Capell\Admin\Contracts\Extenders\AdminPanelExtender;
use Filament\Panel;
use Pboivin\FilamentPeek\FilamentPeekPlugin;

final class FilamentPeekPanelExtender implements AdminPanelExtender
{
    public function extend(Panel $panel): void
    {
        $panel->plugin(FilamentPeekPlugin::make());
    }
}
