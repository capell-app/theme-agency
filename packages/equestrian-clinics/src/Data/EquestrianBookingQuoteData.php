<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Data;

use Capell\EquestrianClinics\Enums\EquestrianPaymentFeeModeEnum;
use Capell\Payments\Enums\PaymentProvider;
use Spatie\LaravelData\Data;

final class EquestrianBookingQuoteData extends Data
{
    public function __construct(
        public int $basePricePence,
        public int $universalBookingFeePence,
        public int $travelFeePence,
        public int $addOnsPence,
        public int $methodFeePence,
        public int $creditAppliedPence,
        public int $totalPence,
        public PaymentProvider $provider,
        public EquestrianPaymentFeeModeEnum $feeMode,
        public bool $methodFeeLegalAcknowledgementRequired,
    ) {}
}
