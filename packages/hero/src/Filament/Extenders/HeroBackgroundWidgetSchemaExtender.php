<?php

declare(strict_types=1);

namespace Capell\Hero\Filament\Extenders;

use Capell\Hero\Filament\Components\Forms\HeroBackgroundSchema;
use Capell\LayoutBuilder\Contracts\Extenders\WidgetSchemaExtender;
use Filament\Schemas\Schema;

final class HeroBackgroundWidgetSchemaExtender implements WidgetSchemaExtender
{
    public function extendDisplayComponents(Schema $schema, array $components): array
    {
        return [
            ...$components,
            ...HeroBackgroundSchema::widget(),
        ];
    }
}
