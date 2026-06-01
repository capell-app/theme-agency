<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Actions;

use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\SeoSuite\Data\SchemaTemplateReportData;
use Capell\SeoSuite\Enums\SchemaTemplateTypeEnum;
use Capell\SeoSuite\Enums\SeoIssueSeverityEnum;
use Capell\SeoSuite\Support\SchemaTemplates\SchemaTemplateRegistry;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static list<SchemaTemplateReportData> run(Page $page, Site $site, Language $language)
 */
class BuildSchemaTemplateReportAction
{
    use AsAction;

    /**
     * @return list<SchemaTemplateReportData>
     */
    public function handle(Page $page, Site $site, Language $language): array
    {
        $registry = resolve(SchemaTemplateRegistry::class);
        $dashboardReports = [];

        foreach ($registry->matching($page) as $type => $template) {
            $templateType = SchemaTemplateTypeEnum::from($type);
            $schema = $template->build($page, $site, $language);
            $requiredFields = $template->requiredFields($page, $site, $language);
            $presentFields = [];
            $missingFields = [];
            $warnings = BuildMarketplaceStructuredDataFreshnessWarningsAction::run($schema);

            foreach ($requiredFields as $field) {
                if ($this->hasSchemaValue($schema, $field)) {
                    $presentFields[] = $field;

                    continue;
                }

                $missingFields[] = $field;
            }

            $dashboardReports[] = new SchemaTemplateReportData(
                templateType: $templateType,
                presentFields: $presentFields,
                missingFields: $missingFields,
                severity: $this->severity($missingFields, $warnings, $registry->pageRequires($page, $templateType)),
                warnings: $warnings,
            );
        }

        return $dashboardReports;
    }

    /**
     * @param  list<string>  $missingFields
     * @param  list<string>  $warnings
     */
    private function severity(array $missingFields, array $warnings, bool $requiredByPageType): SeoIssueSeverityEnum
    {
        if ($missingFields === []) {
            return $warnings === []
                ? SeoIssueSeverityEnum::Passed
                : SeoIssueSeverityEnum::Warning;
        }

        return $requiredByPageType
            ? SeoIssueSeverityEnum::Critical
            : SeoIssueSeverityEnum::Warning;
    }

    /**
     * @param  array<string, mixed>  $schema
     */
    private function hasSchemaValue(array $schema, string $field): bool
    {
        if (! array_key_exists($field, $schema)) {
            return false;
        }

        if ($schema[$field] === null || $schema[$field] === '') {
            return false;
        }

        return ! (is_array($schema[$field]) && $schema[$field] === []);
    }
}
