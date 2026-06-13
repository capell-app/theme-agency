<?php

declare(strict_types=1);

namespace Capell\LiveChat\Filament\Resources\EscalationRules\Pages;

use Capell\LiveChat\Filament\Resources\EscalationRules\EscalationRuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListEscalationRules extends ListRecords
{
    protected static string $resource = EscalationRuleResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
