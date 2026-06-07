<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Support\Admin;

use Capell\Admin\Contracts\Extenders\PageAuthoringValidator;
use Capell\Core\Contracts\Pageable;
use Capell\SeoSuite\Actions\BuildSeoAuthoringQualityGateResultsAction;
use Capell\SeoSuite\Data\SeoAuthoringQualityGateResultData;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

final class SeoAuthoringQualityGateValidator implements PageAuthoringValidator
{
    /**
     * @param  array<string, mixed>  $formData
     */
    public function validate(array $formData, ?Pageable $page = null, string $operation = 'save'): void
    {
        $results = BuildSeoAuthoringQualityGateResultsAction::run($formData, $page);
        $blockingMessages = [];
        $warningMessages = [];

        foreach ($results as $result) {
            if (! $result instanceof SeoAuthoringQualityGateResultData) {
                continue;
            }

            if ($result->blocks()) {
                $fieldPath = $this->validationFieldPath($result->fieldPath ?? 'translations', $operation);
                $blockingMessages[$fieldPath][] = $result->message;

                continue;
            }

            if ($result->warns()) {
                $warningMessages[] = $result->message;
            }
        }

        if ($blockingMessages !== []) {
            throw ValidationException::withMessages($blockingMessages);
        }

        if ($warningMessages !== []) {
            Notification::make('seo-authoring-quality-gates')
                ->warning()
                ->title(__('capell-seo-suite::generic.seo_authoring_quality_gate_warnings_title'))
                ->body(collect($warningMessages)->unique()->implode("\n"))
                ->send();
        }
    }

    private function validationFieldPath(string $fieldPath, string $operation): string
    {
        if ($operation === 'action-create' || str_starts_with($fieldPath, 'data.')) {
            return $fieldPath;
        }

        return 'data.' . $fieldPath;
    }
}
