<?php

declare(strict_types=1);

namespace Capell\Payments\Actions;

use Capell\Payments\Contracts\PaymentGateway;
use Capell\Payments\Data\CreateCheckoutSessionData;
use Capell\Payments\Models\CheckoutSession;
use Lorisleiva\Actions\Concerns\AsAction;

final class CreateCheckoutSessionAction
{
    use AsAction;

    public function __construct(private readonly PaymentGateway $gateway) {}

    public function handle(CreateCheckoutSessionData $data): CheckoutSession
    {
        $sessionData = $this->gateway->createCheckoutSession($data);

        return RecordCheckoutSessionAction::run($sessionData, $data);
    }
}
