<?php

declare(strict_types=1);

namespace Capell\LiveChat\Filament\Resources\EscalationRules\Pages;

use Capell\LiveChat\Filament\Resources\EscalationRules\EscalationRuleResource;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditEscalationRule extends EditRecord
{
    protected static string $resource = EscalationRuleResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    #[Override]
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return EscalationRuleResource::prepareFormDataForPersistence($data);
    }
}
