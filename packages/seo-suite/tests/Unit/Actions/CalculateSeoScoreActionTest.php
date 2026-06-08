<?php

declare(strict_types=1);

use Capell\SeoSuite\Actions\CalculateSeoScoreAction;
use Capell\SeoSuite\Data\SeoIssueData;
use Capell\SeoSuite\Enums\SeoCheckKeyEnum;
use Capell\SeoSuite\Enums\SeoIssueSeverityEnum;

it('calculates an explainable seo score from issue severity', function (): void {
    $score = CalculateSeoScoreAction::run([
        new SeoIssueData(
            key: SeoCheckKeyEnum::MetaTitle,
            severity: SeoIssueSeverityEnum::Critical,
            message: 'Missing meta title.',
        ),
        new SeoIssueData(
            key: SeoCheckKeyEnum::MetaDescription,
            severity: SeoIssueSeverityEnum::Warning,
            message: 'Meta description is short.',
        ),
        new SeoIssueData(
            key: SeoCheckKeyEnum::InternalLinks,
            severity: SeoIssueSeverityEnum::Notice,
            message: 'Add more internal links.',
        ),
    ]);

    expect($score)->toBe(76);
});

it('weights check categories instead of treating same-severity checks equally', function (): void {
    $action = resolve(CalculateSeoScoreAction::class);

    $canonicalBreakdown = $action->breakdown([
        new SeoIssueData(
            key: SeoCheckKeyEnum::Canonical,
            severity: SeoIssueSeverityEnum::Warning,
            message: 'Missing canonical.',
        ),
    ]);

    $altBreakdown = $action->breakdown([
        new SeoIssueData(
            key: SeoCheckKeyEnum::ImageAltText,
            severity: SeoIssueSeverityEnum::Warning,
            message: 'Missing alt text.',
        ),
    ]);

    expect($canonicalBreakdown->score)->toBeLessThan($altBreakdown->score)
        ->and($canonicalBreakdown->category('technical')?->score)->toBe(86)
        ->and($altBreakdown->category('on_page')?->score)->toBe(95);
});

it('never returns a score below zero', function (): void {
    $issues = [
        new SeoIssueData(
            key: SeoCheckKeyEnum::MetaTitle,
            severity: SeoIssueSeverityEnum::Critical,
            message: 'Critical issue.',
        ),
        new SeoIssueData(
            key: SeoCheckKeyEnum::MetaDescription,
            severity: SeoIssueSeverityEnum::Critical,
            message: 'Critical issue.',
        ),
        new SeoIssueData(
            key: SeoCheckKeyEnum::OnPageContent,
            severity: SeoIssueSeverityEnum::Critical,
            message: 'Critical issue.',
        ),
        new SeoIssueData(
            key: SeoCheckKeyEnum::Canonical,
            severity: SeoIssueSeverityEnum::Critical,
            message: 'Critical issue.',
        ),
        new SeoIssueData(
            key: SeoCheckKeyEnum::Robots,
            severity: SeoIssueSeverityEnum::Critical,
            message: 'Critical issue.',
        ),
        new SeoIssueData(
            key: SeoCheckKeyEnum::Sitemap,
            severity: SeoIssueSeverityEnum::Critical,
            message: 'Critical issue.',
        ),
        new SeoIssueData(
            key: SeoCheckKeyEnum::TranslationCoverage,
            severity: SeoIssueSeverityEnum::Critical,
            message: 'Critical issue.',
        ),
        new SeoIssueData(
            key: SeoCheckKeyEnum::LlmsTxt,
            severity: SeoIssueSeverityEnum::Critical,
            message: 'Critical issue.',
        ),
        new SeoIssueData(
            key: SeoCheckKeyEnum::Schema,
            severity: SeoIssueSeverityEnum::Critical,
            message: 'Critical issue.',
        ),
        new SeoIssueData(
            key: SeoCheckKeyEnum::InternalLinks,
            severity: SeoIssueSeverityEnum::Critical,
            message: 'Critical issue.',
        ),
        new SeoIssueData(
            key: SeoCheckKeyEnum::BrokenLinks,
            severity: SeoIssueSeverityEnum::Critical,
            message: 'Critical issue.',
        ),
        new SeoIssueData(
            key: SeoCheckKeyEnum::Redirects,
            severity: SeoIssueSeverityEnum::Critical,
            message: 'Critical issue.',
        ),
    ];

    expect(CalculateSeoScoreAction::run($issues))->toBe(0);
});

it('returns full score when there are no issues', function (): void {
    expect(CalculateSeoScoreAction::run([]))->toBe(100);
});
