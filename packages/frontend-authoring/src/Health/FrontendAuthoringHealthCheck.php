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
            label: 'Frontend Authoring beacon route',
            passed: $registered,
            message: $registered
                ? 'The admin beacon route is registered.'
                : 'The admin beacon route is not registered.',
            remediation: $registered
                ? null
                : 'Ensure FrontendAuthoringServiceProvider loads the package routes.',
        );
    }

    /**
     * Asserts the registries and signer the authoring surface depends on can be resolved.
     */
    public function serviceBindingsCheck(): DoctorCheckResultData
    {
        $unresolvableBindings = $this->unresolvableBindings();

        return new DoctorCheckResultData(
            label: 'Frontend Authoring service bindings',
            passed: $unresolvableBindings === [],
            message: $unresolvableBindings === []
                ? 'The editable region registry, editor surface registry, and signer are resolvable.'
                : 'Unresolvable bindings: ' . implode(', ', $unresolvableBindings) . '.',
            remediation: $unresolvableBindings === []
                ? null
                : 'Ensure FrontendAuthoringServiceProvider registers the authoring service bindings.',
        );
    }

    /**
     * Asserts the enabled flag is readable so the beacon can be toggled.
     */
    public function configurationCheck(): DoctorCheckResultData
    {
        $readable = $this->isConfigurationReadable();

        return new DoctorCheckResultData(
            label: 'Frontend Authoring configuration',
            passed: $readable,
            message: $readable
                ? 'The capell-frontend-authoring.enabled flag is readable.'
                : 'The capell-frontend-authoring configuration is not loaded.',
            remediation: $readable
                ? null
                : 'Ensure FrontendAuthoringServiceProvider merges the package configuration.',
        );
    }

    /**
     * Asserts an application key is available so the signer can sign edit payloads.
     */
    public function signingSecretCheck(): DoctorCheckResultData
    {
        $hasSecret = $this->hasSigningSecret();

        return new DoctorCheckResultData(
            label: 'Frontend Authoring signing secret',
            passed: $hasSecret,
            message: $hasSecret
                ? 'An application key is available for signing edit payloads.'
                : 'No application key is available; edit payloads cannot be signed or verified.',
            remediation: $hasSecret
                ? null
                : 'Set app.key so signed editor routes can be generated and validated.',
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
        return array_values(collect(self::REQUIRED_BINDINGS)
            ->reject(function (string $binding): bool {
                try {
                    return resolve($binding) instanceof $binding;
                } catch (Throwable) {
                    return false;
                }
            })
            ->values()
            ->all());
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
}
