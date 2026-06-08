<?php

declare(strict_types=1);

use Capell\FormBuilder\Data\SubmissionMetaData;
use Capell\FormBuilder\Events\FormSubmitted;
use Capell\FormBuilder\Models\Form;
use Capell\PublicActions\Listeners\SubmitPublicActionFromFormSubmission;
use Capell\PublicActions\Models\PublicAction;
use Capell\PublicActions\Models\PublicActionSubmission;

it('submits form builder event payloads when submissions are not stored', function (): void {
    config()->set('capell-public-actions.form_builder.mappings', [
        'lead-form' => 'lead-capture',
    ]);

    PublicAction::factory()->create([
        'key' => 'lead-capture',
        'handler_key' => 'test.handler',
        'payload_schema' => [
            'fields' => [
                ['key' => 'email', 'type' => 'email', 'required' => true],
                ['key' => 'name', 'type' => 'text', 'required' => false],
            ],
        ],
    ]);

    $form = new Form;
    $form->exists = true;
    $form->forceFill([
        'id' => 123,
        'site_id' => 1,
        'handle' => 'lead-form',
    ]);

    resolve(SubmitPublicActionFromFormSubmission::class)->handle(new FormSubmitted(
        form: $form,
        metadata: new SubmissionMetaData(url: 'https://example.test/contact'),
        payload: [
            'email' => 'person@example.test',
            'name' => 'Mona',
        ],
    ));

    $submission = PublicActionSubmission::query()->firstOrFail();

    expect($submission->payload)
        ->toMatchArray([
            'email' => 'person@example.test',
            'name' => 'Mona',
        ])
        ->and($submission->source_type)->toBe('form_builder')
        ->and($submission->source_id)->toBe('123');
});
