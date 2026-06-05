<?php

declare(strict_types=1);

namespace Capell\MediaAI\Tests\Fixtures;

use Capell\AIOrchestrator\Data\AIOrchestratorRunData;

final class AIOrchestratorImageDoctorAction
{
    public static ?AIOrchestratorRunData $lastRun = null;

    public static function lastRun(): ?AIOrchestratorRunData
    {
        return self::$lastRun;
    }

    /**
     * @return array{successful: bool, message: string}
     */
    public static function run(AIOrchestratorRunData $run): array
    {
        self::$lastRun = $run;

        return [
            'successful' => true,
            'message' => 'Doctor finished through AI Orchestrator',
        ];
    }
}
