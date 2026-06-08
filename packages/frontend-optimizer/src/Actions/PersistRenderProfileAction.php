<?php

declare(strict_types=1);

namespace Capell\FrontendOptimizer\Actions;

use Capell\FrontendOptimizer\Data\RenderProfileData;
use Capell\FrontendOptimizer\Enums\OptimizationStatus;
use Capell\FrontendOptimizer\Models\FrontendRenderProfile;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static FrontendRenderProfile run(RenderProfileData $profile, ?string $manifestPath = null)
 */
class PersistRenderProfileAction
{
    use AsAction;

    public function handle(RenderProfileData $profile, ?string $manifestPath = null): FrontendRenderProfile
    {
        $values = [
            'critical_css_path' => null,
            'label' => $profile->label,
            'manifest' => $manifestPath === null ? null : ['path' => $manifestPath],
            'scope' => $profile->scope->value,
            'signature' => $profile->signature,
            'status' => OptimizationStatus::Pending->value,
        ];

        $renderProfile = FrontendRenderProfile::query()->createOrFirst(
            ['hash' => $profile->hash],
            $values,
        );

        if ($renderProfile->wasRecentlyCreated) {
            return $renderProfile;
        }

        $values = [
            'label' => $profile->label,
            'scope' => $profile->scope->value,
            'signature' => $profile->signature,
        ];

        if ($manifestPath !== null) {
            $values['manifest'] = ['path' => $manifestPath];
        }

        $renderProfile->fill($values);

        $renderProfile->save();

        return $renderProfile;
    }
}
