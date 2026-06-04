<?php

declare(strict_types=1);

namespace Capell\Contacts\Enums;

use Filament\Support\Contracts\HasLabel;

enum ContactActivityType: string implements HasLabel
{
    case Note = 'note';
    case FormSubmission = 'form_submission';
    case NewsletterSubscription = 'newsletter_subscription';
    case Comment = 'comment';
    case AccessRegistration = 'access_registration';
    case EventRegistration = 'event_registration';
    case ShopifyCustomer = 'shopify_customer';
    case CampaignConversion = 'campaign_conversion';
    case PrivacyExport = 'privacy_export';
    case PrivacyAnonymization = 'privacy_anonymization';

    public function getLabel(): string
    {
        return __('capell-contacts::generic.activity_type.' . $this->value);
    }
}
