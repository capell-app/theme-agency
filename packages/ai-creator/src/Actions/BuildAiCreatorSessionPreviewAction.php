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
        $recommendations = array_values(array_map(
            static fn (array $recommendation): array => [
                'package' => (string) ($recommendation['package'] ?? ''),
                'level' => (string) ($recommendation['level'] ?? ''),
                'reason' => (string) ($recommendation['reason'] ?? ''),
                'consequence' => (string) ($recommendation['consequence'] ?? ''),
            ],
            array_filter($session->package_recommendations ?? [], 'is_array'),
        ));

        $requiredPackages = array_values(array_map(
            static fn (mixed $package): string => (string) $package,
            array_filter($session->package_requirements ?? [], 'is_scalar'),
        ));

        return new AiCreatorPreviewData(
            sessionId: $session->id,
            intent: $session->intent,
            recommendations: $recommendations,
            requiredPackages: $requiredPackages,
        );
    }
}
