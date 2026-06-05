<?php

declare(strict_types=1);

namespace Capell\MediaAI\Tests\Fixtures;

use Capell\AIOrchestrator\Contracts\AIOrchestratorModule;
use Capell\AIOrchestrator\Data\AIOrchestratorCapabilityData;
use Capell\AIOrchestrator\Enums\AIOrchestratorApprovalLevel;

final class AIOrchestratorImageDoctorModule implements AIOrchestratorModule
{
    public function key(): string
    {
        return 'media-ai';
    }

    public function label(): string
    {
        return 'Media AI';
    }

    /**
     * @return array<int, AIOrchestratorCapabilityData>
     */
    public function capabilities(): array
    {
        return [
            new AIOrchestratorCapabilityData(
                key: 'doctor-image',
                label: 'Doctor image',
                description: 'Run a Media AI image doctor request.',
                actionClass: AIOrchestratorImageDoctorAction::class,
                approvalLevel: AIOrchestratorApprovalLevel::Developer,
            ),
        ];
    }
}
