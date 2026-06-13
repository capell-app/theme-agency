<?php

declare(strict_types=1);

namespace Capell\LiveChat\Enums;

use Capell\LiveChat\Filament\Resources\AvailabilityWindows\AvailabilityWindowResource;
use Capell\LiveChat\Filament\Resources\Conversations\ConversationResource;
use Capell\LiveChat\Filament\Resources\EscalationRules\EscalationRuleResource;
use Capell\LiveChat\Filament\Resources\KnowledgeSources\KnowledgeSourceResource;

enum ResourceEnum: string
{
    case Conversation = ConversationResource::class;
    case AvailabilityWindow = AvailabilityWindowResource::class;
    case EscalationRule = EscalationRuleResource::class;
    case KnowledgeSource = KnowledgeSourceResource::class;
}
