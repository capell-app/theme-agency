<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Support\Handlers;

use Capell\AutomationStudio\Actions\BuildSafeAutomationActionFailureResultAction;
use Capell\AutomationStudio\Contracts\AutomationActionHandler;
use Capell\AutomationStudio\Data\AutomationActionResultData;
use Capell\AutomationStudio\Data\AutomationRuleActionData;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Support\Handlers\Concerns\ResolvesContacts;
use Capell\EmailStudio\Actions\SendEmailAction;
use Capell\EmailStudio\Data\EmailAddressData;
use Capell\EmailStudio\Data\EmailHeaderData;
use Capell\EmailStudio\Data\SendEmailData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Override;
use Spatie\LaravelData\DataCollection;
use Throwable;

final class SendEmailAutomationActionHandler implements AutomationActionHandler
{
    use ResolvesContacts;

    #[Override]
    public function handle(AutomationTriggerEventData $event, AutomationRuleActionData $action): AutomationActionResultData
    {
        $sendEmailDataClass = SendEmailData::class;
        $emailAddressDataClass = EmailAddressData::class;
        $emailHeaderDataClass = EmailHeaderData::class;
        $sendEmailActionClass = SendEmailAction::class;

        if (
            ! class_exists($sendEmailDataClass)
            || ! class_exists($emailAddressDataClass)
            || ! class_exists($emailHeaderDataClass)
            || ! class_exists($sendEmailActionClass)
        ) {
            return new AutomationActionResultData(
                success: false,
                message: $this->automationMessage('capell-automation-studio::generic.dispatcher.email_studio_unavailable', 'Email Studio is not available.'),
            );
        }

        $templateKey = $this->templateKey($action);

        if ($templateKey === null) {
            return new AutomationActionResultData(
                success: false,
                message: $this->automationMessage('capell-automation-studio::generic.dispatcher.email_template_key_required', 'An Email Studio template key is required.'),
            );
        }

        $to = $this->addresses($event, $action, 'to', $emailAddressDataClass);

        if ($to === []) {
            return new AutomationActionResultData(
                success: false,
                message: $this->automationMessage('capell-automation-studio::generic.dispatcher.email_recipient_required', 'At least one email recipient is required.'),
            );
        }

        try {
            /** @var Model $message */
            $message = $sendEmailActionClass::run(new $sendEmailDataClass(
                templateKey: $templateKey,
                to: new DataCollection($emailAddressDataClass, $to),
                cc: new DataCollection($emailAddressDataClass, $this->addresses($event, $action, 'cc', $emailAddressDataClass)),
                bcc: new DataCollection($emailAddressDataClass, $this->addresses($event, $action, 'bcc', $emailAddressDataClass)),
                siteId: $this->siteId($event, $action),
                siteScopeKey: $this->siteScopeKey($event, $action),
                emailProfileId: $this->emailProfileId($action),
                variables: $this->variables($event, $action),
                headers: new DataCollection($emailHeaderDataClass, $this->headers($action, $emailHeaderDataClass)),
                triggeredByType: $event->sourceType,
                triggeredById: $this->triggeredById($event),
                queue: (bool) ($action->settings['queue'] ?? true),
                locale: $this->stringSetting($event, $action, 'locale'),
            ));
        } catch (Throwable) {
            return BuildSafeAutomationActionFailureResultAction::run($action->type);
        }

        return new AutomationActionResultData(
            success: true,
            context: [
                'email_message_id' => $message->getKey(),
                'template_key' => $templateKey,
            ],
        );
    }

    private function templateKey(AutomationRuleActionData $action): ?string
    {
        $templateKey = $action->settings['template_key'] ?? $action->settings['email_template_key'] ?? null;

        return is_string($templateKey) && trim($templateKey) !== '' ? trim($templateKey) : null;
    }

    /**
     * @param  class-string  $emailAddressDataClass
     * @return list<object>
     */
    private function addresses(
        AutomationTriggerEventData $event,
        AutomationRuleActionData $action,
        string $key,
        string $emailAddressDataClass,
    ): array {
        $configuredAddresses = Arr::wrap($action->settings[$key] ?? []);

        if ($key === 'to' && $configuredAddresses === []) {
            $configuredAddresses = Arr::wrap($this->stringSetting($event, $action, 'email'));
        }

        return collect($configuredAddresses)
            ->map(fn (mixed $address): ?object => $this->addressData($address, $emailAddressDataClass))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  class-string  $emailAddressDataClass
     */
    private function addressData(mixed $address, string $emailAddressDataClass): ?object
    {
        if (is_string($address) && trim($address) !== '') {
            return new $emailAddressDataClass(trim($address));
        }

        if (! is_array($address)) {
            return null;
        }

        $email = $address['email'] ?? $address['address'] ?? null;
        $name = $address['name'] ?? null;

        if (! is_string($email) || trim($email) === '') {
            return null;
        }

        return new $emailAddressDataClass(
            email: trim($email),
            name: is_string($name) && trim($name) !== '' ? trim($name) : null,
        );
    }

    private function siteScopeKey(AutomationTriggerEventData $event, AutomationRuleActionData $action): string
    {
        $siteScopeKey = $action->settings['site_scope_key'] ?? $event->payload['site_scope_key'] ?? null;

        if (is_string($siteScopeKey) && trim($siteScopeKey) !== '') {
            return trim($siteScopeKey);
        }

        $siteId = $this->siteId($event, $action);

        return $siteId === null ? 'global' : 'site:' . $siteId;
    }

    private function emailProfileId(AutomationRuleActionData $action): ?int
    {
        $profileId = $action->settings['email_profile_id'] ?? null;

        return is_numeric($profileId) && (int) $profileId > 0 ? (int) $profileId : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function variables(AutomationTriggerEventData $event, AutomationRuleActionData $action): array
    {
        $variables = $action->settings['variables'] ?? [];

        return is_array($variables) ? [...$event->payload, ...$variables] : $event->payload;
    }

    /**
     * @param  class-string  $emailHeaderDataClass
     * @return list<object>
     */
    private function headers(AutomationRuleActionData $action, string $emailHeaderDataClass): array
    {
        $headers = $action->settings['headers'] ?? [];

        if (! is_array($headers)) {
            return [];
        }

        return collect($headers)
            ->map(function (mixed $value, int|string $name) use ($emailHeaderDataClass): ?object {
                if (is_array($value)) {
                    $name = $value['name'] ?? null;
                    $value = $value['value'] ?? null;
                }

                if (! is_string($name) || trim($name) === '' || ! is_string($value)) {
                    return null;
                }

                return new $emailHeaderDataClass(trim($name), $value);
            })
            ->filter()
            ->values()
            ->all();
    }

    private function triggeredById(AutomationTriggerEventData $event): ?int
    {
        return is_numeric($event->sourceId) && (int) $event->sourceId > 0 ? (int) $event->sourceId : null;
    }
}
