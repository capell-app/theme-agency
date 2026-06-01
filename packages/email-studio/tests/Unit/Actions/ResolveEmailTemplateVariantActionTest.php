<?php

declare(strict_types=1);

use Capell\Core\Models\Site;
use Capell\EmailStudio\Actions\ResolveEmailTemplateVariantAction;
use Capell\EmailStudio\Enums\EmailVariantStatus;
use Capell\EmailStudio\Models\EmailTemplate;
use Capell\EmailStudio\Models\EmailTemplateVariant;

it('resolves an active variant from the requested site and locale before fallbacks', function (): void {
    $template = EmailTemplate::factory()->create();
    $requestedSite = Site::factory()->create();
    $otherSite = Site::factory()->create();

    EmailTemplateVariant::factory()->for($template, 'template')->create([
        'site_id' => $otherSite->getKey(),
        'site_scope_key' => 'site:' . $otherSite->getKey(),
        'locale' => 'en',
        'version' => 99,
        'subject' => 'Other site',
    ]);

    EmailTemplateVariant::factory()->for($template, 'template')->create([
        'site_id' => $requestedSite->getKey(),
        'site_scope_key' => 'site:' . $requestedSite->getKey(),
        'locale' => null,
        'version' => 10,
        'subject' => 'Site fallback',
    ]);

    EmailTemplateVariant::factory()->for($template, 'template')->create([
        'site_id' => $requestedSite->getKey(),
        'site_scope_key' => 'site:' . $requestedSite->getKey(),
        'locale' => 'en',
        'status' => EmailVariantStatus::Retired,
        'version' => 20,
        'subject' => 'Retired site variant',
    ]);

    $expectedVariant = EmailTemplateVariant::factory()->for($template, 'template')->create([
        'site_id' => $requestedSite->getKey(),
        'site_scope_key' => 'site:' . $requestedSite->getKey(),
        'locale' => 'en',
        'version' => 1,
        'subject' => 'Expected site variant',
    ]);

    $resolvedVariant = ResolveEmailTemplateVariantAction::run($template, 'site:' . $requestedSite->getKey(), 'en');

    expect($resolvedVariant?->is($expectedVariant))->toBeTrue();

    $templateWithoutRequestedLocale = EmailTemplate::factory()->create();

    $neutralVariant = EmailTemplateVariant::factory()->for($templateWithoutRequestedLocale, 'template')->create([
        'site_id' => $requestedSite->getKey(),
        'site_scope_key' => 'site:' . $requestedSite->getKey(),
        'locale' => null,
        'version' => 1,
        'subject' => 'Neutral fallback',
    ]);

    EmailTemplateVariant::factory()->for($templateWithoutRequestedLocale, 'template')->create([
        'site_id' => $requestedSite->getKey(),
        'site_scope_key' => 'site:' . $requestedSite->getKey(),
        'locale' => 'en',
        'version' => 50,
        'subject' => 'Locale-specific variant',
    ]);

    $resolvedNeutralVariant = ResolveEmailTemplateVariantAction::run($templateWithoutRequestedLocale, 'site:' . $requestedSite->getKey());

    expect($resolvedNeutralVariant?->is($neutralVariant))->toBeTrue();
});
