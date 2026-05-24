<?php

declare(strict_types=1);

namespace Capell\Hero\Filament\Extenders;

use Capell\Admin\Contracts\Extenders\ThemeSchemaExtender;
use Capell\Hero\Filament\Components\Forms\HeroBackgroundSchema;
use Filament\Schemas\Schema;

final class HeroBackgroundThemeSchemaExtender implements ThemeSchemaExtender
{
    public function extendSettingsComponents(Schema $schema, array $components): array
    {
        return [
            ...$components,
            ...HeroBackgroundSchema::theme(),
        ];
    }
}
