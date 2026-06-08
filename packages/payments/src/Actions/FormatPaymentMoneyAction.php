<?php

declare(strict_types=1);

namespace Capell\Payments\Actions;

use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static ?string run(?int $minorAmount, ?string $currency)
 */
final class FormatPaymentMoneyAction
{
    use AsAction;

    /** @var list<string> */
    private const array ZERO_DECIMAL_CURRENCIES = [
        'bif',
        'clp',
        'djf',
        'gnf',
        'jpy',
        'kmf',
        'krw',
        'mga',
        'pyg',
        'rwf',
        'ugx',
        'vnd',
        'vuv',
        'xaf',
        'xof',
        'xpf',
    ];

    public function handle(?int $minorAmount, ?string $currency): ?string
    {
        if ($minorAmount === null || $currency === null || $currency === '') {
            return null;
        }

        $normalizedCurrency = strtolower($currency);
        $decimals = in_array($normalizedCurrency, self::ZERO_DECIMAL_CURRENCIES, true) ? 0 : 2;
        $divisor = $decimals === 0 ? 1 : 100;

        return sprintf(
            '%s %s',
            strtoupper($normalizedCurrency),
            number_format($minorAmount / $divisor, $decimals),
        );
    }
}
