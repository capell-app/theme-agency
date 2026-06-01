<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Support\Handlers;

use Capell\AutomationStudio\Contracts\AutomationActionHandler;
use Capell\AutomationStudio\Data\AutomationActionResultData;
use Capell\AutomationStudio\Data\AutomationRuleActionData;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Support\Handlers\Concerns\ResolvesContacts;
use Capell\Contacts\Actions\TagContactAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Override;

final class TagContactAutomationActionHandler implements AutomationActionHandler
{
    use ResolvesContacts;

    #[Override]
    public function handle(AutomationTriggerEventData $event, AutomationRuleActionData $action): AutomationActionResultData
    {
        $tagContactActionClass = TagContactAction::class;

        if (! class_exists($tagContactActionClass)) {
            return new AutomationActionResultData(
                success: false,
                message: $this->automationMessage('capell-automation-studio::generic.dispatcher.contacts_unavailable', 'Contacts is not available.'),
            );
        }

        $tags = $this->tags($action);

        if ($tags === []) {
            return new AutomationActionResultData(
                success: false,
                message: $this->automationMessage('capell-automation-studio::generic.dispatcher.contact_tag_required', 'At least one contact tag is required.'),
            );
        }

        $resolvedContact = $this->resolveContact($event, $action);

        if (! ($resolvedContact['success'] ?? false) || ! ($resolvedContact['contact'] ?? null) instanceof Model) {
            return new AutomationActionResultData(
                success: false,
                message: (string) ($resolvedContact['message'] ?? 'Unable to resolve contact.'),
            );
        }

        $contact = $tagContactActionClass::run($resolvedContact['contact'], $tags);

        return new AutomationActionResultData(
            success: true,
            context: [
                'contact_id' => $contact->getKey(),
                'tags' => $tags,
            ],
        );
    }

    /**
     * @return list<string>
     */
    private function tags(AutomationRuleActionData $action): array
    {
        return collect(Arr::wrap($action->settings['tags'] ?? $action->settings['tag'] ?? []))
            ->filter(static fn (mixed $tag): bool => is_string($tag) && trim($tag) !== '')
            ->map(static fn (string $tag): string => trim($tag))
            ->values()
            ->all();
    }
}
