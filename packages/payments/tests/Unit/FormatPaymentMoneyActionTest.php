<?php

declare(strict_types=1);

use Capell\Payments\Actions\FormatPaymentMoneyAction;
use Capell\Payments\Tests\TestCase;

uses(TestCase::class);

it('formats decimal and zero-decimal payment currencies', function (): void {
    expect(FormatPaymentMoneyAction::run(1099, 'gbp'))->toBe('GBP 10.99')
        ->and(FormatPaymentMoneyAction::run(1099, 'jpy'))->toBe('JPY 1,099')
        ->and(FormatPaymentMoneyAction::run(null, 'gbp'))->toBeNull()
        ->and(FormatPaymentMoneyAction::run(1099, null))->toBeNull();
});
