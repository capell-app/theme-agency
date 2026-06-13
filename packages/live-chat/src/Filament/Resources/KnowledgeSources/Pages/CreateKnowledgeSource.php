<?php

declare(strict_types=1);

namespace Capell\LiveChat\Filament\Resources\KnowledgeSources\Pages;

use Capell\LiveChat\Filament\Resources\KnowledgeSources\KnowledgeSourceResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateKnowledgeSource extends CreateRecord
{
    protected static string $resource = KnowledgeSourceResource::class;
}
