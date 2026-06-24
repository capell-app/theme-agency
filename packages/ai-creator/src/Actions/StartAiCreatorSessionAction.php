<?php

declare(strict_types=1);

namespace Capell\AiCreator\Actions;

use Capell\AiCreator\Data\AiCreatorPackageRecommendationData;
use Capell\AiCreator\Data\AiCreatorStartSessionData;
use Capell\AiCreator\Enums\AiCreatorRecommendationLevel;
use Capell\AiCreator\Enums\AiCreatorSessionStatus;
use Capell\AiCreator\Models\AiCreatorSession;
use Lorisleiva\Actions\Concerns\AsAction;

final class StartAiCreatorSessionAction
{
    use AsAction;

    public function handle(AiCreatorStartSessionData $data): AiCreatorSession
    {
        $recommendations = RecommendAiCreatorPackagesAction::make()->handle($data->intent);
        $recommendationPayload = array_map(
            static fn (AiCreatorPackageRecommendationData $recommendation): array => $recommendation->toPayload(),
            $recommendations,
        );

        $requiredPackages = array_values(array_map(
            static fn (array $recommendation): string => $recommendation['package'],
            array_filter(
                $recommendationPayload,
                static fn (array $recommendation): bool => $recommendation['level'] === AiCreatorRecommendationLevel::Required->value,
            ),
        ));

        return AiCreatorSession::query()->create([
            'intent' => $data->intent,
            'answers' => $data->answers,
            'package_recommendations' => $recommendationPayload,
            'package_requirements' => $requiredPackages,
            'status' => AiCreatorSessionStatus::Draft,
            'user_id' => $data->userId,
            'site_id' => $data->siteId,
            'workspace_id' => $data->workspaceId,
        ]);
    }
}
