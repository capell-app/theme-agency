<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Actions;

use Capell\Core\Contracts\Pageable;
use Capell\Core\Models\Blueprint;
use Capell\SeoSuite\Data\SeoAuthoringQualityGateResultData;
use Capell\SeoSuite\Enums\SeoCheckKeyEnum;
use Capell\SeoSuite\Enums\SeoCheckModeEnum;
use Capell\SeoSuite\Settings\SeoSuiteSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static list<SeoAuthoringQualityGateResultData> run(array $formData, ?Pageable $page = null, bool $ignoreStrictSwitch = false)
 */
final class BuildSeoAuthoringQualityGateResultsAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $formData
     * @return list<SeoAuthoringQualityGateResultData>
     */
    public function handle(array $formData, ?Pageable $page = null, bool $ignoreStrictSwitch = false): array
    {
        $settings = resolve(SeoSuiteSettings::class);
        $blueprint = $this->resolveBlueprint($formData, $page);
        $strictEnabled = $this->boolOverride($blueprint, 'enabled') ?? $settings->seo_authoring_strict_meta_enabled;

        if (! $strictEnabled && ! $ignoreStrictSwitch) {
            return [];
        }

        $results = [];

        foreach ($this->translations($formData) as $translationKey => $translationData) {
            $results = [
                ...$results,
                ...$this->metadataResults($translationKey, $translationData, $settings, $blueprint),
            ];
        }

        return $results;
    }

    /**
     * @return list<SeoAuthoringQualityGateResultData>
     */
    private function metadataResults(
        string|int $translationKey,
        array $translationData,
        SeoSuiteSettings $settings,
        ?Blueprint $blueprint,
    ): array {
        $results = [];
        $titleMode = $this->mode($settings, $blueprint, SeoCheckKeyEnum::MetaTitle);
        $descriptionMode = $this->mode($settings, $blueprint, SeoCheckKeyEnum::MetaDescription);

        if ($titleMode !== SeoCheckModeEnum::Ignored) {
            $title = $this->plainText($translationData['meta']['title'] ?? null);

            if ($title === null) {
                $results[] = $this->result(
                    SeoCheckKeyEnum::MetaTitle,
                    $titleMode,
                    false,
                    __('capell-seo-suite::generic.seo_authoring_meta_title_required'),
                    $this->fieldPath($translationKey, 'title'),
                );
            }
        }

        if ($descriptionMode === SeoCheckModeEnum::Ignored) {
            return $results;
        }

        $description = $this->plainText($translationData['meta']['description'] ?? null);
        $minimum = $this->minimumDescriptionLength($settings, $blueprint);
        $maximum = $this->maximumDescriptionLength($settings, $blueprint, $minimum);
        $title = $this->plainText($translationData['title'] ?? null);
        $requiresTitle = $this->boolOverride($blueprint, 'meta_description.require_title')
            ?? $settings->seo_authoring_require_title_in_description;

        if ($description === null) {
            $results[] = $this->result(
                SeoCheckKeyEnum::MetaDescription,
                $descriptionMode,
                false,
                __('capell-seo-suite::generic.seo_authoring_meta_description_required'),
                $this->fieldPath($translationKey, 'description'),
            );

            return $results;
        }

        $length = mb_strlen($description);

        if ($length < $minimum) {
            $results[] = $this->result(
                SeoCheckKeyEnum::MetaDescription,
                $descriptionMode,
                false,
                __('capell-seo-suite::generic.seo_authoring_meta_description_too_short', ['min' => $minimum]),
                $this->fieldPath($translationKey, 'description'),
            );
        }

        if ($length > $maximum) {
            $results[] = $this->result(
                SeoCheckKeyEnum::MetaDescription,
                $descriptionMode,
                false,
                __('capell-seo-suite::generic.seo_authoring_meta_description_too_long', ['max' => $maximum]),
                $this->fieldPath($translationKey, 'description'),
            );
        }

        if ($requiresTitle && $title !== null && ! str_contains(mb_strtolower($description), mb_strtolower($title))) {
            $results[] = $this->result(
                SeoCheckKeyEnum::MetaDescription,
                $descriptionMode,
                false,
                __('capell-seo-suite::generic.seo_authoring_meta_description_missing_title'),
                $this->fieldPath($translationKey, 'description'),
            );
        }

        return $results;
    }

    private function result(
        SeoCheckKeyEnum $key,
        SeoCheckModeEnum $mode,
        bool $passed,
        string $message,
        ?string $fieldPath = null,
    ): SeoAuthoringQualityGateResultData {
        return new SeoAuthoringQualityGateResultData(
            key: $key,
            mode: $mode,
            passed: $passed,
            message: $message,
            fieldPath: $fieldPath,
        );
    }

    private function mode(SeoSuiteSettings $settings, ?Blueprint $blueprint, SeoCheckKeyEnum $key): SeoCheckModeEnum
    {
        $override = $this->stringOverride($blueprint, $key->value . '.mode');

        if ($override !== null) {
            return SeoCheckModeEnum::tryFrom($override) ?? SeoCheckModeEnum::Ignored;
        }

        return $settings->qualityGateMode($key);
    }

    private function minimumDescriptionLength(SeoSuiteSettings $settings, ?Blueprint $blueprint): int
    {
        $override = $this->intOverride($blueprint, 'meta_description.min_length');

        return max(1, $override ?? $settings->metaDescriptionMinimumLength());
    }

    private function maximumDescriptionLength(SeoSuiteSettings $settings, ?Blueprint $blueprint, int $minimum): int
    {
        $override = $this->intOverride($blueprint, 'meta_description.max_length');

        return max($minimum, $override ?? $settings->metaDescriptionMaximumLength());
    }

    private function fieldPath(string|int $translationKey, string $metaKey): string
    {
        return sprintf('translations.%s.meta.%s', $translationKey, $metaKey);
    }

    /**
     * @param  array<string, mixed>  $formData
     * @return array<string|int, array<string, mixed>>
     */
    private function translations(array $formData): array
    {
        $translations = $formData['translations'] ?? [];

        if (! is_array($translations)) {
            return [];
        }

        return array_filter($translations, is_array(...));
    }

    private function plainText(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $text = trim(html_entity_decode(strip_tags((string) $value)));

        return $text !== '' ? $text : null;
    }

    /**
     * @param  array<string, mixed>  $formData
     */
    private function resolveBlueprint(array $formData, ?Pageable $page): ?Blueprint
    {
        $pageModel = $page instanceof Model ? $page : null;

        if ($pageModel !== null && $pageModel->relationLoaded('type')) {
            $type = $pageModel->getRelation('type');

            if ($type instanceof Blueprint) {
                return $type;
            }
        }

        $blueprintId = $formData['blueprint_id'] ?? $pageModel?->getAttribute('blueprint_id');

        if (! is_scalar($blueprintId) || $blueprintId === '') {
            return null;
        }

        return Blueprint::query()->find($blueprintId);
    }

    private function stringOverride(?Blueprint $blueprint, string $path): ?string
    {
        $value = $this->override($blueprint, $path);

        return is_string($value) && $value !== '' ? $value : null;
    }

    private function intOverride(?Blueprint $blueprint, string $path): ?int
    {
        $value = $this->override($blueprint, $path);

        if (! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    private function boolOverride(?Blueprint $blueprint, string $path): ?bool
    {
        $value = $this->override($blueprint, $path);

        return is_bool($value) ? $value : null;
    }

    private function override(?Blueprint $blueprint, string $path): mixed
    {
        if (! $blueprint instanceof Blueprint) {
            return null;
        }

        return Arr::get((array) $blueprint->admin, 'seo_quality_gates.' . $path);
    }
}
