<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Support\Handlers\Concerns;

use Capell\AutomationStudio\Data\AutomationRuleActionData;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\Contacts\Actions\FindOrCreateContactAction;
use Capell\Contacts\Data\ContactIdentityData;
use Illuminate\Database\Eloquent\Model;
use Throwable;

trait ResolvesContacts
{
    /**
     * @return array{success: bool, contact?: Model, message?: string}
     */
    private function resolveContact(AutomationTriggerEventData $event, AutomationRuleActionData $action): array
    {
        $identityClass = ContactIdentityData::class;
        $findOrCreateActionClass = FindOrCreateContactAction::class;

        if (! class_exists($identityClass) || ! class_exists($findOrCreateActionClass)) {
            return [
                'success' => false,
                'message' => $this->automationMessage('capell-automation-studio::generic.dispatcher.contacts_unavailable', 'Contacts is not available.'),
            ];
        }

        $siteId = $this->siteId($event, $action);

        if ($siteId === null) {
            return [
                'success' => false,
                'message' => $this->automationMessage('capell-automation-studio::generic.dispatcher.contact_site_required', 'A site id is required to resolve a contact.'),
            ];
        }

        $identity = new $identityClass(
            siteId: $siteId,
            email: $this->stringSetting($event, $action, 'email'),
            phone: $this->stringSetting($event, $action, 'phone'),
            firstName: $this->stringSetting($event, $action, 'first_name'),
            lastName: $this->stringSetting($event, $action, 'last_name'),
            displayName: $this->stringSetting($event, $action, 'display_name') ?? $this->stringSetting($event, $action, 'name'),
            profile: $this->arraySetting($action, 'profile'),
        );

        /** @var Model $contact */
        $contact = $findOrCreateActionClass::run($identity);

        return [
            'success' => true,
            'contact' => $contact,
        ];
    }

    private function siteId(AutomationTriggerEventData $event, AutomationRuleActionData $action): ?int
    {
        $siteId = $action->settings['site_id'] ?? $event->payload['site_id'] ?? null;

        return is_numeric($siteId) && (int) $siteId > 0 ? (int) $siteId : null;
    }

    private function stringSetting(AutomationTriggerEventData $event, AutomationRuleActionData $action, string $key): ?string
    {
        $identity = $action->settings['identity'] ?? [];
        $value = is_array($identity) ? ($identity[$key] ?? null) : null;
        $value ??= $action->settings[$key] ?? null;
        $value ??= $event->payload[$key] ?? null;

        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function arraySetting(AutomationRuleActionData $action, string $key): ?array
    {
        $value = $action->settings[$key] ?? null;

        return is_array($value) ? $value : null;
    }

    private function automationMessage(string $key, string $fallback): string
    {
        try {
            if (function_exists('app') && app()->bound('translator')) {
                return __($key);
            }
        } catch (Throwable) {
            //
        }

        return $fallback;
    }
}
