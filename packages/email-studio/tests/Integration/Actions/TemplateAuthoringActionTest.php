<?php

declare(strict_types=1);

use Capell\EmailStudio\Actions\ActivateEmailTemplateVariantAction;
use Capell\EmailStudio\Actions\BuildAuthEmailMailMessageAction;
use Capell\EmailStudio\Actions\CaptureEmailTemplateThemeScreenshotAction;
use Capell\EmailStudio\Actions\CreateDefaultEmailTemplateThemeAction;
use Capell\EmailStudio\Actions\CreateEmailTemplateOverrideAction;
use Capell\EmailStudio\Actions\RenderResolvedEmailTemplateAction;
use Capell\EmailStudio\Contracts\CapturesEmailTemplateThemeScreenshots;
use Capell\EmailStudio\Data\EmailContextData;
use Capell\EmailStudio\Data\EmailTemplateDefinitionData;
use Capell\EmailStudio\Data\EmailTemplateVariableData;
use Capell\EmailStudio\Enums\EmailVariantStatus;
use Capell\EmailStudio\Models\EmailTemplateTheme;
use Capell\EmailStudio\Models\EmailTemplateVariant;
use Capell\EmailStudio\Support\EmailTemplateRegistry;
use Capell\EmailStudio\Tests\Fixtures\ThemeScreenshotAdapterFixture;

it('creates draft overrides from static definitions and activates versions with themed rendering', function (): void {
    CreateDefaultEmailTemplateThemeAction::run(null, 'global', 'default', 'Capell Mail');

    resolve(EmailTemplateRegistry::class)->registerDefinition(new EmailTemplateDefinitionData(
        key: 'billing.receipt',
        packageName: 'capell-app/billing',
        name: 'Billing receipt',
        variables: [
            new EmailTemplateVariableData('customer.name'),
            new EmailTemplateVariableData('receipt_url'),
        ],
        subject: 'Receipt for {{ customer.name }}',
        previewText: 'Your receipt is ready.',
        html: '<p>Hello {{ customer.name }}</p><p><a href="{{ receipt_url }}">View receipt</a></p>',
        text: 'Hello {{ customer.name }}: {{ receipt_url }}',
        defaultThemeKey: 'default',
    ));

    $variant = CreateEmailTemplateOverrideAction::run('billing.receipt');
    $activeVariant = EmailTemplateVariant::factory()
        ->for($variant->template, 'template')
        ->create([
            'locale' => 'en',
            'status' => EmailVariantStatus::Active,
            'version' => 1,
        ]);

    expect($variant->status)->toBe(EmailVariantStatus::Draft)
        ->and($variant->version)->toBe(1)
        ->and($variant->subject)->toBe('Receipt for {{ customer.name }}')
        ->and($variant->html_body)->toContain('{{ customer.name }}')
        ->and($variant->email_template_theme_id)->not->toBeNull();

    $activated = ActivateEmailTemplateVariantAction::run($variant, 7);

    expect($activated->status)->toBe(EmailVariantStatus::Active)
        ->and($activated->approved_by)->toBe(7)
        ->and($activeVariant->refresh()->status)->toBe(EmailVariantStatus::Retired);

    $rendered = RenderResolvedEmailTemplateAction::run(
        templateKey: 'billing.receipt',
        context: new EmailContextData(variables: [
            'customer' => ['name' => 'Ben'],
            'receipt_url' => 'https://example.test/receipt',
        ]),
    )->rendered;

    expect($rendered->subject)->toBe('Receipt for Ben')
        ->and($rendered->html)->toContain('Capell Mail')
        ->and($rendered->html)->toContain('View receipt');
});

it('retires neutral-locale active variants and captures theme screenshots through the configured adapter', function (): void {
    $theme = CreateDefaultEmailTemplateThemeAction::run(null, 'global', 'default', 'Capell Mail');
    app()->bind(CapturesEmailTemplateThemeScreenshots::class, ThemeScreenshotAdapterFixture::class);

    $capturedTheme = CaptureEmailTemplateThemeScreenshotAction::run($theme);

    expect($capturedTheme->screenshot_path)->toBe('email-themes/default.png');

    resolve(EmailTemplateRegistry::class)->registerDefinition(new EmailTemplateDefinitionData(
        key: 'system.notice',
        packageName: 'capell-app/system',
        name: 'System notice',
        variables: [new EmailTemplateVariableData('name')],
        subject: 'Notice {{ name }}',
        html: '<p>{{ name }}</p>',
    ));

    $variant = CreateEmailTemplateOverrideAction::run('system.notice', null, 'global', null);
    $variant->forceFill(['locale' => null])->save();

    $activeVariant = EmailTemplateVariant::factory()
        ->for($variant->template, 'template')
        ->create([
            'locale' => null,
            'status' => EmailVariantStatus::Active,
            'version' => 1,
        ]);

    ActivateEmailTemplateVariantAction::run($variant);

    expect($activeVariant->refresh()->status)->toBe(EmailVariantStatus::Retired)
        ->and(EmailTemplateTheme::query()->where('site_scope_key', 'global')->where('is_default', true)->count())->toBe(1);
});

it('builds auth replacement mail from registered static templates', function (): void {
    config(['app.name' => 'Capell Test']);
    CreateDefaultEmailTemplateThemeAction::run(null, 'global', 'default', 'Capell Auth');

    $mail = BuildAuthEmailMailMessageAction::run('auth.verify-email', [
        'name' => 'Ben',
        'email' => 'ben@example.com',
        'action_url' => 'https://example.test/verify',
        'expires_minutes' => 60,
    ]);

    expect($mail->subject)->toBe('Verify your email address for Capell Test')
        ->and($mail->view)->toBe('capell-email-studio::emails.auth-rendered')
        ->and($mail->viewData['html'])->toContain('Capell Auth')
        ->and($mail->viewData['html'])->toContain('https://example.test/verify');
});
