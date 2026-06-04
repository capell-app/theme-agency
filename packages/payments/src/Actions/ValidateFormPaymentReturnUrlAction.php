<?php

declare(strict_types=1);

namespace Capell\Payments\Actions;

use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

final class ValidateFormPaymentReturnUrlAction
{
    use AsAction;

    public function handle(?string $url): ?string
    {
        if ($url === null || $url === '') {
            return null;
        }

        $components = parse_url($url);
        $scheme = is_array($components) ? strtolower((string) ($components['scheme'] ?? '')) : '';
        $host = is_array($components) ? $this->normalizeHost($components['host'] ?? null) : null;

        if (! in_array($scheme, ['http', 'https'], true) || $host === null || ! in_array($host, $this->allowedHosts(), true)) {
            throw ValidationException::withMessages([
                'return_url' => __('capell-payments::generic.form_payments.invalid_return_url'),
            ]);
        }

        return $url;
    }

    /**
     * @return list<string>
     */
    private function allowedHosts(): array
    {
        $hosts = [];
        $appHost = $this->normalizeHost(parse_url(URL::to('/'), PHP_URL_HOST));

        if ($appHost !== null) {
            $hosts[] = $appHost;
        }

        $configuredHosts = config('capell-payments.form_builder.allowed_return_hosts', []);

        if (is_string($configuredHosts)) {
            $configuredHosts = explode(',', $configuredHosts);
        }

        if (is_array($configuredHosts)) {
            foreach ($configuredHosts as $configuredHost) {
                $host = $this->normalizeHost($configuredHost);

                if ($host !== null) {
                    $hosts[] = $host;
                }
            }
        }

        return array_values(array_unique($hosts));
    }

    private function normalizeHost(mixed $host): ?string
    {
        if (! is_string($host) || trim($host) === '') {
            return null;
        }

        return strtolower(trim($host));
    }
}
