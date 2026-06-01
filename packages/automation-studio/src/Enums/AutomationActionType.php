<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Enums;

use Filament\Support\Contracts\HasLabel;
use Override;

enum AutomationActionType: string implements HasLabel
{
    case SendEmail = 'send_email';
    case Webhook = 'webhook';
    case TagContact = 'tag_contact';
    case CreateNote = 'create_note';
    case SubscribeUser = 'subscribe_user';
    case QueueAgentCapability = 'queue_agent_capability';
    case PublicAction = 'public_action';

    #[Override]
    public function getLabel(): string
    {
        return match ($this) {
            self::SendEmail => __('capell-automation-studio::generic.actions.send_email'),
            self::Webhook => __('capell-automation-studio::generic.actions.webhook'),
            self::TagContact => __('capell-automation-studio::generic.actions.tag_contact'),
            self::CreateNote => __('capell-automation-studio::generic.actions.create_note'),
            self::SubscribeUser => __('capell-automation-studio::generic.actions.subscribe_user'),
            self::QueueAgentCapability => __('capell-automation-studio::generic.actions.queue_agent_capability'),
            self::PublicAction => __('capell-automation-studio::generic.actions.public_action'),
        };
    }
}
