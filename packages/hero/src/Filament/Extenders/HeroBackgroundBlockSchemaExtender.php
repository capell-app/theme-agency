<?php

declare(strict_types=1);

namespace Capell\Hero\Filament\Extenders;

use Capell\Hero\Filament\Components\Forms\HeroBackgroundSchema;
use Capell\LayoutBuilder\Contracts\Extenders\BlockSchemaExtender;
use Filament\Schemas\Schema;

final class HeroBackgroundBlockSchemaExtender implements BlockSchemaExtender
{
    public function extendDisplayComponents(Schema $schema, array $components): array
    {
        return [
            ...$components,
            ...HeroBackgroundSchema::block(),
        ];
    }
}
