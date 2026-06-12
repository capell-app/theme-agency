<?php

declare(strict_types=1);

use Capell\Payments\Actions\RedactPaymentErrorMessageAction;
use Capell\Payments\Tests\TestCase;

uses(TestCase::class);

it('redacts configured Stripe secrets from persisted payment errors', function (): void {
    config()->set('capell-payments.stripe.secret_key', 'sk_test_secret_key');
    config()->set('capell-payments.stripe.webhook_secret', 'whsec_test_secret');

    $redacted = RedactPaymentErrorMessageAction::run(
        new RuntimeException('Stripe failed with Authorization: Bearer sk_test_secret_key and webhook_secret=whsec_test_secret.'),
    );

    expect($redacted)->not->toContain('sk_test_secret_key')
        ->and($redacted)->not->toContain('whsec_test_secret')
        ->and($redacted)->toContain('[redacted]');
});
