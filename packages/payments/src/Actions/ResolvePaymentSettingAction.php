<?php

declare(strict_types=1);

namespace Capell\Payments\Actions;

use Capell\Payments\Settings\PaymentsSettings;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

final class ResolvePaymentSettingAction
{
    use AsAction;

    public function handle(string $configKey, string $settingsKey, mixed $default = null): mixed
    {
        $configuredValue = config($configKey);

        if ($this->isFilled($configuredValue)) {
            return $configuredValue;
        }

        if (! class_exists(PaymentsSettings::class)) {
            return $default;
        }

        try {
            $settings = app(PaymentsSettings::class);
        } catch (Throwable) {
            return $default;
        }

        $settingsValue = $settings->{$settingsKey} ?? null;

        return $this->isFilled($settingsValue) ? $settingsValue : $default;
    }

    private function isFilled(mixed $value): bool
    {
        if (is_string($value)) {
            return trim($value) !== '';
        }

        return $value !== null;
    }
}
