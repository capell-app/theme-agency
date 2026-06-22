<?php

declare(strict_types=1);

namespace Capell\AiCreator\Actions;

use Capell\AiCreator\Data\AiCreatorPreviewData;
use Capell\AiCreator\Models\AiCreatorSession;
use Lorisleiva\Actions\Concerns\AsAction;

final class BuildAiCreatorSessionPreviewAction
{
    use AsAction;

    public function handle(AiCreatorSession $session): AiCreatorPreviewData
    {
        $recommendations = $session->package_recommendations;
        $requirements = $session->package_requirements;

        return new AiCreatorPreviewData(
            sessionId: (int) $session->getKey(),
            intent: $session->intent,
            recommendations: is_array($recommendations) ? $recommendations : [],
            requiredPackages: is_array($requirements) ? $requirements : [],
        );
    }
}
