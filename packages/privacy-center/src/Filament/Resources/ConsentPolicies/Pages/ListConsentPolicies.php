<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Filament\Resources\ConsentPolicies\Pages;

use Capell\PrivacyCenter\Filament\Resources\ConsentPolicies\ConsentPolicyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListConsentPolicies extends ListRecords
{
    protected static string $resource = ConsentPolicyResource::class;

    /**
     * @return array<int, CreateAction>
     */
    #[Override]
    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
