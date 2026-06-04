<?php

declare(strict_types=1);

namespace Capell\FrontendAuthoring\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\FrontendAuthoring\Support\EditableRegionRegistry;
use Capell\FrontendAuthoring\Support\EditableRegionSigner;
use Capell\FrontendAuthoring\Support\EditorSurfaceRegistry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Throwable;

final class FrontendAuthoringHealthCheck implements ChecksExtensionHealth
{
    private const string BEACON_ROUTE_NAME = 'capell-frontend.beacon';

    /**
     * @var list<class-string>
     */
    private const array REQUIRED_BINDINGS = [
        EditableRegionRegistry::class,
        EditorSurfaceRegistry::class,
        EditableRegionSigner::class,
    ];

    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    /**
     * @return Collection<int, DoctorCheckResultData>
     */
    public static function runDiagnostics(): Collection
    {
        $check = new self;

        return collect([
            $check->beaconRouteCheck(),
            $check->serviceBindingsCheck(),
            $check->configurationCheck(),
            $check->signingSecretCheck(),
        ]);
    }

    public static function passed(): bool
    {
        return self::runDiagnostics()
            ->every(static fn (DoctorCheckResultData $result): bool => $result->passed);
    }

    /**
     * Asserts the admin beacon route is registered so editors can be bootstrapped.
     */
    public function beaconRouteCheck(): DoctorCheckResultData
    {
        $registered = $this->isBeaconRouteRegistered();

        return new DoctorCheckResultData(
            label: $this->translation('health.beacon_route.label'),
            passed: $registered,
            message: $registered
                ? $this->translation('health.beacon_route.passed')
                : $this->translation('health.beacon_route.failed'),
            remediation: $registered
                ? null
                : $this->translation('health.beacon_route.remediation'),
        );
    }

    /**
     * Asserts the registries and signer the authoring surface depends on can be resolved.
     */
    public function serviceBindingsCheck(): DoctorCheckResultData
    {
        $unresolvableBindings = $this->unresolvableBindings();

        return new DoctorCheckResultData(
            label: $this->translation('health.service_bindings.label'),
            passed: $unresolvableBindings === [],
            message: $unresolvableBindings === []
                ? $this->translation('health.service_bindings.passed')
                : $this->translation('health.service_bindings.failed', [
                    'bindings' => implode(', ', $unresolvableBindings),
                ]),
            remediation: $unresolvableBindings === []
                ? null
                : $this->translation('health.service_bindings.remediation'),
        );
    }

    /**
     * Asserts the enabled flag is readable so the beacon can be toggled.
     */
    public function configurationCheck(): DoctorCheckResultData
    {
        $readable = $this->isConfigurationReadable();

        return new DoctorCheckResultData(
            label: $this->translation('health.configuration.label'),
            passed: $readable,
            message: $readable
                ? $this->translation('health.configuration.passed')
                : $this->translation('health.configuration.failed'),
            remediation: $readable
                ? null
                : $this->translation('health.configuration.remediation'),
        );
    }

    /**
     * Asserts an application key is available so the signer can sign edit payloads.
     */
    public function signingSecretCheck(): DoctorCheckResultData
    {
        $hasSecret = $this->hasSigningSecret();

        return new DoctorCheckResultData(
            label: $this->translation('health.signing_secret.label'),
            passed: $hasSecret,
            message: $hasSecret
                ? $this->translation('health.signing_secret.passed')
                : $this->translation('health.signing_secret.failed'),
            remediation: $hasSecret
                ? null
                : $this->translation('health.signing_secret.remediation'),
        );
    }

    public function isBeaconRouteRegistered(): bool
    {
        return Route::has(self::BEACON_ROUTE_NAME);
    }

    /**
     * @return list<class-string>
     */
    public function unresolvableBindings(): array
    {
        $bindings = [];

        foreach (self::REQUIRED_BINDINGS as $binding) {
            try {
                if (! resolve($binding) instanceof $binding) {
                    $bindings[] = $binding;
                }
            } catch (Throwable) {
                $bindings[] = $binding;
            }
        }

        return $bindings;
    }

    public function isConfigurationReadable(): bool
    {
        return config()->has('capell-frontend-authoring.enabled');
    }

    public function hasSigningSecret(): bool
    {
        $secret = config('app.key');

        return is_string($secret) && $secret !== '';
    }

    /**
     * @param  array<string, string>  $replace
     */
    private function translation(string $key, array $replace = []): string
    {
        return (string) __('capell-frontend-authoring::authoring.' . $key, $replace);
    }
}
