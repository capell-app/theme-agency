<?php

declare(strict_types=1);

namespace Capell\LiveChat\Filament\Resources\KnowledgeSources\Pages;

use Capell\LiveChat\Filament\Resources\KnowledgeSources\KnowledgeSourceResource;
use Filament\Resources\Pages\EditRecord;

final class EditKnowledgeSource extends EditRecord
{
    protected static string $resource = KnowledgeSourceResource::class;
}
