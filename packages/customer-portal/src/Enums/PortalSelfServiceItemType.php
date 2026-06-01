<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Enums;

use Filament\Support\Contracts\HasLabel;

enum PortalSelfServiceItemType: string implements HasLabel
{
    case GatedResource = 'gated_resource';
    case Payment = 'payment';
    case Document = 'document';
    case EventRegistration = 'event_registration';
    case NewsletterPreference = 'newsletter_preference';
    case Support = 'support';

    public function getLabel(): string
    {
        return __('capell-customer-portal::generic.self_service_item_types.' . $this->value);
    }
}
