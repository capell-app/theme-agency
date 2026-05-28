<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Actions;

use Capell\AgentBridge\Data\AgentBridgePromptData;
use Lorisleiva\Actions\Concerns\AsAction;

final class BuildAgentBridgePromptAction
{
    use AsAction;

    public function handle(AgentBridgePromptData $data): string
    {
        return trim(sprintf(
            <<<'PROMPT'
                I want to use the Capell Site Agent Bridge server to %s.

                Area: %s
                Operation: %s
                Safety mode: %s

                Target/context:
                %s

                Constraints:
                %s

                Success criteria:
                %s

                Before taking any mutating action, list the matching Agent Bridge capability, the exact payload you plan to send, and wait for the preview/confirmation workflow.
                PROMPT,
            $data->goal,
            $data->area,
            $data->operation,
            $data->safety,
            $data->target !== '' ? $data->target : 'Not provided.',
            $data->constraints !== '' ? $data->constraints : 'Use Capell package boundaries, policies, and preview-first workflow.',
            $data->successCriteria !== '' ? $data->successCriteria : 'Explain what changed or why no change is needed.',
        ));
    }
}
