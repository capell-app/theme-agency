<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Filament\Resources\RetentionRules\Pages;

use Capell\PrivacyCenter\Filament\Resources\RetentionRules\RetentionRuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListRetentionRules extends ListRecords
{
    protected static string $resource = RetentionRuleResource::class;

    /**
     * @return array<int, CreateAction>
     */
    #[Override]
    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
