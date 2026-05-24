<?php

declare(strict_types=1);

namespace Capell\Hero\Filament\Extenders;

use Capell\Hero\Filament\Components\Forms\HeroBackgroundSchema;
use Capell\LayoutBuilder\Contracts\Extenders\BlockAssetSchemaExtender;
use Filament\Schemas\Schema;

final class HeroBackgroundBlockAssetSchemaExtender implements BlockAssetSchemaExtender
{
    public function extendAssetComponents(Schema $schema, array $components): array
    {
        return [
            ...$components,
            ...HeroBackgroundSchema::asset(),
        ];
    }

    public function extendRepeaterComponents(array $components): array
    {
        return [
            ...$components,
            ...HeroBackgroundSchema::asset(),
        ];
    }
}
