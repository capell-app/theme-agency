<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Actions;

use Capell\SeoSuite\Data\SeoIssueData;
use Capell\SeoSuite\Data\SeoScoreBreakdownData;
use Capell\SeoSuite\Data\SeoScoreCategoryData;
use Capell\SeoSuite\Enums\SeoCheckKeyEnum;
use Capell\SeoSuite\Enums\SeoIssueSeverityEnum;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static int run(list<SeoIssueData> $issues)
 */
final class CalculateSeoScoreAction
{
    use AsAction;

    private const string CATEGORY_ON_PAGE = 'on_page';

    private const string CATEGORY_TECHNICAL = 'technical';

    private const string CATEGORY_STRUCTURED_DATA = 'structured_data';

    private const string CATEGORY_LINKS = 'links';

    /**
     * @param  list<SeoIssueData>  $issues
     */
    public function handle(array $issues): int
    {
        return $this->breakdown($issues)->score;
    }

    /**
     * @param  list<SeoIssueData>  $issues
     */
    public function breakdown(array $issues): SeoScoreBreakdownData
    {
        $categoryPenalties = array_fill_keys(array_keys($this->categoryWeights()), 0.0);
        $categoryIssueCounts = array_fill_keys(array_keys($this->categoryWeights()), 0);

        foreach ($issues as $issue) {
            if (! $issue instanceof SeoIssueData || $issue->severity === SeoIssueSeverityEnum::Passed) {
                continue;
            }

            $category = $this->categoryForCheck($issue->key);
            $categoryPenalties[$category] += $this->checkWeight($issue->key) * $this->severityMultiplier($issue->severity);
            $categoryIssueCounts[$category]++;
        }

        $categories = [];
        $overallScore = 0.0;

        foreach ($this->categoryWeights() as $category => $weight) {
            $categoryScore = max(0, (int) round(100 - min(100.0, $categoryPenalties[$category])));
            $overallScore += $categoryScore * ($weight / 100);

            $categories[] = new SeoScoreCategoryData(
                key: $category,
                label: __('capell-seo-suite::generic.seo_score_category_' . $category),
                score: $categoryScore,
                weight: $weight,
                issueCount: $categoryIssueCounts[$category],
            );
        }

        return new SeoScoreBreakdownData(
            score: max(0, min(100, (int) round($overallScore))),
            categories: $categories,
        );
    }

    /**
     * @return array<string, int>
     */
    private function categoryWeights(): array
    {
        return [
            self::CATEGORY_ON_PAGE => 35,
            self::CATEGORY_TECHNICAL => 30,
            self::CATEGORY_STRUCTURED_DATA => 20,
            self::CATEGORY_LINKS => 15,
        ];
    }

    private function categoryForCheck(SeoCheckKeyEnum $checkKey): string
    {
        return match ($checkKey) {
            SeoCheckKeyEnum::MetaTitle,
            SeoCheckKeyEnum::MetaDescription,
            SeoCheckKeyEnum::DuplicateTitle,
            SeoCheckKeyEnum::SocialImage,
            SeoCheckKeyEnum::ImageAltText,
            SeoCheckKeyEnum::OnPageContent,
            SeoCheckKeyEnum::FocusKeyword => self::CATEGORY_ON_PAGE,
            SeoCheckKeyEnum::Schema => self::CATEGORY_STRUCTURED_DATA,
            SeoCheckKeyEnum::InternalLinks,
            SeoCheckKeyEnum::BrokenLinks,
            SeoCheckKeyEnum::Redirects => self::CATEGORY_LINKS,
            default => self::CATEGORY_TECHNICAL,
        };
    }

    private function checkWeight(SeoCheckKeyEnum $checkKey): int
    {
        return match ($checkKey) {
            SeoCheckKeyEnum::MetaTitle => 50,
            SeoCheckKeyEnum::MetaDescription => 30,
            SeoCheckKeyEnum::DuplicateTitle => 25,
            SeoCheckKeyEnum::OnPageContent => 25,
            SeoCheckKeyEnum::FocusKeyword => 25,
            SeoCheckKeyEnum::SocialImage,
            SeoCheckKeyEnum::ImageAltText => 10,
            SeoCheckKeyEnum::Canonical => 25,
            SeoCheckKeyEnum::Robots => 30,
            SeoCheckKeyEnum::Sitemap => 20,
            SeoCheckKeyEnum::TranslationCoverage => 15,
            SeoCheckKeyEnum::LlmsTxt,
            SeoCheckKeyEnum::SearchConsole => 10,
            SeoCheckKeyEnum::Schema => 100,
            SeoCheckKeyEnum::InternalLinks => 35,
            SeoCheckKeyEnum::BrokenLinks => 40,
            SeoCheckKeyEnum::Redirects => 25,
        };
    }

    private function severityMultiplier(SeoIssueSeverityEnum $severity): float
    {
        return match ($severity) {
            SeoIssueSeverityEnum::Critical => 1.0,
            SeoIssueSeverityEnum::Warning => 0.55,
            SeoIssueSeverityEnum::Notice => 0.2,
            SeoIssueSeverityEnum::Passed => 0.0,
        };
    }
}
