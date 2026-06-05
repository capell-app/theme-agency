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

        $url = trim($url);

        if ($this->isLocalPath($url)) {
            return URL::to($url);
        }

        $components = parse_url($url);
        $scheme = is_array($components) ? strtolower((string) ($components['scheme'] ?? '')) : '';
        $host = is_array($components) ? $this->normalizeHost($components['host'] ?? null) : null;

        if (
            ! in_array($scheme, ['http', 'https'], true)
            || $host === null
            || $this->isPrivateOrReservedHost($host)
            || ! in_array($host, $this->allowedHosts(), true)
        ) {
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

        return strtolower(trim($host, " \t\n\r\0\x0B[]"));
    }

    private function isLocalPath(string $url): bool
    {
        return str_starts_with($url, '/')
            && ! str_starts_with($url, '//')
            && ! str_contains($url, '\\');
    }

    private function isPrivateOrReservedHost(string $host): bool
    {
        if ($host === 'localhost' || str_ends_with($host, '.localhost')) {
            return true;
        }

        if (! filter_var($host, FILTER_VALIDATE_IP)) {
            return false;
        }

        return filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }
}
