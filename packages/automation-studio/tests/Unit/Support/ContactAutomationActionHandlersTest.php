<?php

declare(strict_types=1);

use Capell\AutomationStudio\Contracts\AutomationActionHandler;
use Capell\AutomationStudio\Data\AutomationRuleActionData;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Enums\AutomationActionType;
use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Capell\AutomationStudio\Support\Handlers\CreateContactNoteAutomationActionHandler;
use Capell\AutomationStudio\Support\Handlers\TagContactAutomationActionHandler;

it('provides native contact tag and note handlers', function (): void {
    expect(new TagContactAutomationActionHandler)->toBeInstanceOf(AutomationActionHandler::class)
        ->and(new CreateContactNoteAutomationActionHandler)->toBeInstanceOf(AutomationActionHandler::class);
});

it('reports missing contact tags before resolving contacts', function (): void {
    $result = (new TagContactAutomationActionHandler)->handle(
        event: new AutomationTriggerEventData(
            triggerType: AutomationTriggerType::FormSubmitted,
            sourceType: 'form-builder.form',
            payload: ['site_id' => 1, 'email' => 'person@example.test'],
        ),
        action: new AutomationRuleActionData(
            key: 'tag-contact',
            type: AutomationActionType::TagContact,
        ),
    );

    expect($result->success)->toBeFalse()
        ->and($result->message)->toBe('At least one contact tag is required.');
});

it('reports missing site ids before creating contact notes', function (): void {
    $result = (new CreateContactNoteAutomationActionHandler)->handle(
        event: new AutomationTriggerEventData(
            triggerType: AutomationTriggerType::FormSubmitted,
            sourceType: 'form-builder.form',
            payload: ['email' => 'person@example.test'],
        ),
        action: new AutomationRuleActionData(
            key: 'create-note',
            type: AutomationActionType::CreateNote,
            settings: ['summary' => 'Submitted contact form'],
        ),
    );

    expect($result->success)->toBeFalse()
        ->and($result->message)->toBe('A site id is required to resolve a contact.');
});
