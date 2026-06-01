<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Filament\Resources\RetentionRules\Pages;

use Capell\PrivacyCenter\Filament\Resources\RetentionRules\RetentionRuleResource;
use Filament\Resources\Pages\EditRecord;

final class EditRetentionRule extends EditRecord
{
    protected static string $resource = RetentionRuleResource::class;
}
