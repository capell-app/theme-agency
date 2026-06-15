<?php

declare(strict_types=1);

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Contracts\Extensions\ExtensionContribution;
use Capell\Core\Contracts\Extensions\RegistersExtensionRoute;
use Capell\Core\Contracts\Extensions\RegistersExtensionSetting;
use Capell\Payments\Console\Commands\ReconcilePaymentWebhooksCommand;
use Capell\Payments\Console\Commands\ReprocessPaymentWebhookEventsCommand;
use Capell\Payments\Filament\Settings\PaymentsSettingsSchema;
use Capell\Payments\Health\PaymentsHealthCheck;
use Capell\Payments\Manifest\PaymentsConsoleCommandsContribution;
use Capell\Payments\Manifest\PaymentsFrontendRoutesContribution;
use Capell\Payments\Manifest\PaymentsHealthContribution;
use Capell\Payments\Manifest\PaymentsModelsContribution;
use Capell\Payments\Manifest\PaymentsSettingsContribution;
use Capell\Payments\Models\CheckoutSession;
use Capell\Payments\Models\PaymentCustomer;
use Capell\Payments\Models\PaymentDispute;
use Capell\Payments\Models\PaymentDownloadEntitlement;
use Capell\Payments\Models\PaymentIntent;
use Capell\Payments\Models\PaymentRefund;
use Capell\Payments\Models\PaymentWebhookEvent;
use Capell\Payments\Models\Subscription;
use Capell\Payments\Settings\PaymentsSettings;
use Capell\Payments\Tests\TestCase;

uses(TestCase::class);

function paymentsManifest(): array
{
    return capell_json_file_array(__DIR__ . '/../../capell.json');
}

it('declares implemented payments package contributions', function (): void {
    $manifest = paymentsManifest();
    $manifestContributions = $manifest['contributes'] ?? [];
    throw_unless(is_array($manifestContributions), RuntimeException::class, 'Payments contributions must be arrays.');
    $contributions = collect($manifestContributions);

    expect($manifest['contributionTraceability']['deferredContributions'])->toBe([])
        ->and($contributions->pluck('class')->all())->toContain(
            PaymentsModelsContribution::class,
            PaymentsFrontendRoutesContribution::class,
            PaymentsSettingsContribution::class,
            PaymentsConsoleCommandsContribution::class,
            PaymentsHealthContribution::class,
        );

    $models = $contributions->firstWhere('class', PaymentsModelsContribution::class);
    $routes = $contributions->firstWhere('class', PaymentsFrontendRoutesContribution::class);
    $settings = $contributions->firstWhere('class', PaymentsSettingsContribution::class);
    $consoleCommands = $contributions->firstWhere('class', PaymentsConsoleCommandsContribution::class);
    $healthCheck = $contributions->firstWhere('class', PaymentsHealthContribution::class);
    throw_unless(is_array($models), RuntimeException::class, 'Expected payments model contribution.');
    throw_unless(is_array($routes), RuntimeException::class, 'Expected payments route contribution.');
    throw_unless(is_array($settings), RuntimeException::class, 'Expected payments settings contribution.');
    throw_unless(is_array($consoleCommands), RuntimeException::class, 'Expected payments console command contribution.');
    throw_unless(is_array($healthCheck), RuntimeException::class, 'Expected payments health check contribution.');

    expect($models['modelClasses'])->toBe([
        PaymentCustomer::class,
        CheckoutSession::class,
        PaymentIntent::class,
        Subscription::class,
        PaymentWebhookEvent::class,
        PaymentRefund::class,
        PaymentDispute::class,
        PaymentDownloadEntitlement::class,
    ])
        ->and($routes['routes'])->toBe([
            'capell-payments.form-builder.checkout',
            'capell-payments.paid-downloads.show',
            'capell-payments.portal.billing',
            'capell-payments.stripe-webhook',
        ])
        ->and($settings['settingsClass'])->toBe(PaymentsSettings::class)
        ->and($settings['settingsGroup'])->toBe('payments')
        ->and($settings['settingsSchema'])->toBe(PaymentsSettingsSchema::class)
        ->and($manifest['commands']['webhookReconcile'])->toBe('capell:payments:webhooks:reconcile')
        ->and($manifest['commands']['webhookReprocess'])->toBe('capell:payments:webhooks:reprocess')
        ->and($consoleCommands['commands'])->toBe([
            'capell:payments:webhooks:reconcile',
            'capell:payments:webhooks:reprocess',
        ])
        ->and($consoleCommands['commandClasses'])->toBe([
            ReconcilePaymentWebhooksCommand::class,
            ReprocessPaymentWebhookEventsCommand::class,
        ])
        ->and($healthCheck['checkClass'])->toBe(PaymentsHealthCheck::class)
        ->and(class_implements(PaymentsModelsContribution::class))->toContain(ExtensionContribution::class)
        ->and(class_implements(PaymentsFrontendRoutesContribution::class))->toContain(RegistersExtensionRoute::class)
        ->and(class_implements(PaymentsSettingsContribution::class))->toContain(RegistersExtensionSetting::class)
        ->and(class_implements(PaymentsConsoleCommandsContribution::class))->toContain(ExtensionContribution::class)
        ->and(class_implements(PaymentsHealthContribution::class))->toContain(ChecksExtensionHealth::class);
});
