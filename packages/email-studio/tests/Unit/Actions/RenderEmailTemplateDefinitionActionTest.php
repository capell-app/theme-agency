<?php

declare(strict_types=1);

use Capell\EmailStudio\Actions\RenderEmailTemplateDefinitionAction;
use Capell\EmailStudio\Data\EmailContextData;
use Capell\EmailStudio\Data\EmailTemplateDefinitionData;
use Capell\EmailStudio\Data\EmailTemplateVariableData;
use Capell\EmailStudio\Exceptions\EmailTemplateRenderingException;

it('renders static email template definitions with dot variables and allow-listed config values', function (): void {
    config([
        'app.name' => 'Capell Test',
        'capell-email-studio.template_config_variables' => ['app.name'],
    ]);

    $definition = new EmailTemplateDefinitionData(
        key: 'auth.verify-email',
        packageName: 'capell-app/email-studio',
        name: 'Verify email',
        variables: [
            new EmailTemplateVariableData('user.name', 'Name', sampleValue: 'Preview User'),
            new EmailTemplateVariableData('verification_url', 'Verification URL', sampleValue: 'https://example.test/verify'),
            new EmailTemplateVariableData('config.app.name', 'App name', required: false),
        ],
        subject: 'Verify {{ user.name }} for {{ config.app.name }}',
        previewText: 'Use this link: {{ verification_url }}',
        html: '<p>Hello {{ user.name }}</p><a href="{{ verification_url }}">Verify</a>',
        text: 'Hello {{ user.name }}: {{ verification_url }}',
    );

    $rendered = RenderEmailTemplateDefinitionAction::run(
        definition: $definition,
        context: new EmailContextData(variables: [
            'user' => ['name' => '<Ben>'],
            'verification_url' => 'https://example.test/verify?token=<secret>',
        ]),
    );

    expect($rendered->subject)->toBe('Verify <Ben> for Capell Test')
        ->and($rendered->previewText)->toBe('Use this link: https://example.test/verify?token=&lt;secret&gt;')
        ->and($rendered->html)->toContain('<p>Hello &lt;Ben&gt;</p>')
        ->and($rendered->html)->toContain('href="https://example.test/verify?token=&lt;secret&gt;"')
        ->and($rendered->text)->toBe('Hello <Ben>: https://example.test/verify?token=<secret>');
});

it('renders optional variables as empty strings and keeps subjects as plain text', function (): void {
    $definition = new EmailTemplateDefinitionData(
        key: 'demo.optional',
        packageName: 'capell-app/demo',
        name: 'Optional demo',
        variables: [
            new EmailTemplateVariableData('company.name'),
            new EmailTemplateVariableData('optional_note', required: false),
        ],
        subject: "Hello {{ company.name }}\n{{ optional_note }}",
        html: '<p>{{ company.name }}</p><p>{{ optional_note }}</p>',
        text: '{{ company.name }} {{ optional_note }}',
    );

    $rendered = RenderEmailTemplateDefinitionAction::run(
        definition: $definition,
        context: new EmailContextData(variables: [
            'company' => ['name' => 'Ben & Sons'],
        ]),
    );

    expect($rendered->subject)->toBe('Hello Ben & Sons')
        ->and($rendered->html)->toContain('Ben &amp; Sons')
        ->and($rendered->html)->not->toContain('{{ optional_note }}');
});

it('rejects non allow-listed config variables in production rendering', function (): void {
    config(['capell-email-studio.template_config_variables' => []]);

    $definition = new EmailTemplateDefinitionData(
        key: 'auth.verify-email',
        packageName: 'capell-app/email-studio',
        name: 'Verify email',
        variables: [
            new EmailTemplateVariableData('config.app.key'),
        ],
        subject: 'Secret {{ config.app.key }}',
        html: '<p>Secret {{ config.app.key }}</p>',
    );

    expect(fn (): mixed => RenderEmailTemplateDefinitionAction::run(
        definition: $definition,
        context: new EmailContextData,
    ))->toThrow(EmailTemplateRenderingException::class);
});
