<?php

declare(strict_types=1);

use Capell\Payments\Actions\ResolvePaymentSettingAction;
use Capell\Payments\Filament\Settings\PaymentsSettingsSchema;
use Capell\Payments\Settings\PaymentsSettings;
use Capell\Payments\Tests\TestCase;
use Filament\Schemas\Schema;

uses(TestCase::class);

it('declares payment settings group and schema', function (): void {
    expect(PaymentsSettings::group())->toBe('payments')
        ->and(PaymentsSettings::schema())->toBe(PaymentsSettingsSchema::class)
        ->and(PaymentsSettingsSchema::make(resolve(Schema::class)))->not->toBeEmpty();
});

it('prefers configured payment values over stored settings fallbacks', function (): void {
    config()->set('capell-payments.stripe.secret_key', 'sk_configured');

    expect(ResolvePaymentSettingAction::run('capell-payments.stripe.secret_key', 'stripe_secret_key'))->toBe('sk_configured');
});

it('falls back to defaults when settings are unavailable', function (): void {
    config()->set('capell-payments.stripe.secret_key');

    expect(ResolvePaymentSettingAction::run('capell-payments.stripe.secret_key', 'stripe_secret_key', 'fallback'))->toBe('fallback');
});
