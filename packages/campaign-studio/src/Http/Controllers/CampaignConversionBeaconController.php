<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Http\Controllers;

use Capell\CampaignStudio\Actions\CaptureCampaignConversionAction;
use Capell\CampaignStudio\Data\CampaignConversionCaptureData;
use Capell\CampaignStudio\Enums\ConversionGoalType;
use Capell\Insights\Actions\ValidateInsightsBeaconRequestAction;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

final class CampaignConversionBeaconController
{
    public function __invoke(Request $request): Response
    {
        ValidateInsightsBeaconRequestAction::run($request);

        $validated = $request->validate([
            'type' => ['required', Rule::in([ConversionGoalType::PageView->value, ConversionGoalType::CtaClick->value])],
            'url' => ['required', 'url', 'max:512', $this->pathMaxRule()],
            'goal_key' => ['nullable', 'string', 'max:100'],
            'cta_key' => ['nullable', 'string', 'max:100'],
            'visit_id' => ['nullable', 'string', 'max:80'],
        ]);

        CaptureCampaignConversionAction::run(CampaignConversionCaptureData::from($validated));

        return response()->noContent();
    }

    private function pathMaxRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (! is_string($value)) {
                return;
            }

            $path = parse_url($value, PHP_URL_PATH);

            if (is_string($path) && mb_strlen($path) > 512) {
                $fail((string) __('capell-campaign-studio::generic.validation.url_path_max', [
                    'attribute' => $attribute,
                    'max' => 512,
                ]));
            }
        };
    }
}
