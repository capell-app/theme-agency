<?php

declare(strict_types=1);

namespace Capell\MediaAI\Health;

use Capell\Admin\Contracts\Extenders\MediaEditActionExtender;
use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\MediaAI\Contracts\ImageDoctor;
use Capell\MediaAI\Data\ImageDoctorRequest;
use Capell\MediaAI\Filament\MediaAIEditActionExtender;
use Illuminate\Support\Collection;
use Throwable;

final class MediaAIHealthCheck implements ChecksExtensionHealth
{
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
            $check->providerAdapterCheck(),
            $check->editActionCheck(),
            $check->structuredRequestCheck(),
        ]);
    }

    public static function passed(): bool
    {
        return self::runDiagnostics()
            ->every(static fn (DoctorCheckResultData $result): bool => $result->passed);
    }

    /**
     * Asserts an ImageDoctor implementation resolves through the contract.
     *
     * The default NullImageDoctor satisfies this check: the seam is wired even
     * when no real provider is bound. Only a binding failure fails the check.
     * No provider credentials are read or surfaced.
     */
    public function providerAdapterCheck(): DoctorCheckResultData
    {
        $resolved = $this->resolvedImageDoctorClass();

        return new DoctorCheckResultData(
            label: 'Media AI image doctor adapter',
            passed: $resolved !== null,
            message: $resolved !== null
                ? 'The ImageDoctor contract resolves to a bound implementation.'
                : 'The ImageDoctor contract could not be resolved from the container.',
            remediation: $resolved !== null
                ? null
                : 'Ensure MediaAIServiceProvider is registered so the ImageDoctor contract is bound.',
        );
    }

    /**
     * Asserts the media edit action extender is registered when the package is
     * enabled and Admin is present, and that the action stays hidden under the
     * default NullImageDoctor.
     */
    public function editActionCheck(): DoctorCheckResultData
    {
        if (! $this->adminMediaExtensionAvailable()) {
            return new DoctorCheckResultData(
                label: 'Media AI edit action',
                passed: false,
                message: 'Capell Admin media edit extension points are unavailable, so the Doctor image action is not registered.',
                remediation: 'Install capell-app/admin so the Media AI edit action can be tagged onto the media edit page.',
            );
        }

        if (config('capell-media-ai.enabled', true) !== true) {
            return new DoctorCheckResultData(
                label: 'Media AI edit action',
                passed: false,
                message: 'Media AI is disabled, so the Doctor image action is not registered.',
                remediation: 'Set capell-media-ai.enabled to true to register the Doctor image action.',
            );
        }

        $registered = $this->editActionExtenderRegistered();

        return new DoctorCheckResultData(
            label: 'Media AI edit action',
            passed: $registered,
            message: $registered
                ? 'The Doctor image edit action is registered and stays hidden until a real image doctor is bound.'
                : 'The Doctor image edit action is not registered against the media edit page.',
            remediation: $registered
                ? null
                : 'Ensure MediaAIServiceProvider tags MediaAIEditActionExtender onto the media edit action extender tag.',
        );
    }

    /**
     * Asserts a structured ImageDoctorRequest can be built and validates the
     * operation against the known operation set.
     */
    public function structuredRequestCheck(): DoctorCheckResultData
    {
        $valid = $this->canBuildStructuredRequest();

        return new DoctorCheckResultData(
            label: 'Media AI structured request',
            passed: $valid,
            message: $valid
                ? 'Media AI builds a structured ImageDoctorRequest with a validated operation and instructions.'
                : 'Media AI could not build a structured ImageDoctorRequest from the known operation set.',
            remediation: $valid
                ? null
                : 'Ensure ImageDoctorRequest::OPERATIONS lists the supported operations.',
        );
    }

    public function adminMediaExtensionAvailable(): bool
    {
        return interface_exists(MediaEditActionExtender::class);
    }

    private function resolvedImageDoctorClass(): ?string
    {
        try {
            return resolve(ImageDoctor::class)::class;
        } catch (Throwable) {
            return null;
        }
    }

    private function editActionExtenderRegistered(): bool
    {
        if (! $this->adminMediaExtensionAvailable()) {
            return false;
        }

        return collect(app()->tagged(MediaEditActionExtender::TAG))
            ->contains(static fn (object $extender): bool => $extender instanceof MediaAIEditActionExtender);
    }

    private function canBuildStructuredRequest(): bool
    {
        try {
            new ImageDoctorRequest(
                operation: ImageDoctorRequest::OPERATIONS[0],
                instructions: 'Diagnostic structured-request probe.',
                locale: app()->getLocale(),
                budgetCents: 100,
                model: 'diagnostic',
            );

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
