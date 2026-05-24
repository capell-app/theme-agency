<?php

declare(strict_types=1);

use Capell\Core\Models\SiteDomain;
use Capell\SeoSuite\Enums\AiDiscoveryStatusEnum;
use Capell\SeoSuite\Models\AiDiscoverySiteProfile;
use Capell\SeoSuite\Support\AiDiscovery\AiDiscoveryDiscoveryOutputSource;

it('advertises enabled ai discovery public outputs', function (): void {
    $source = new AiDiscoveryDiscoveryOutputSource;
    $profile = new AiDiscoverySiteProfile([
        'llms_txt_enabled' => true,
        'llms_full_txt_enabled' => true,
        'markdown_pages_enabled' => true,
        'status' => AiDiscoveryStatusEnum::Enabled,
    ]);
    $domain = new SiteDomain([
        'domain' => 'example.com',
        'scheme' => 'https',
        'path' => '/docs',
    ]);

    $outputs = $source->outputsForProfile($profile, $domain);

    expect($outputs)->toHaveCount(3)
        ->and($outputs->pluck('key')->all())->toBe([
            'seo-suite.llms-txt',
            'seo-suite.llms-full-txt',
            'seo-suite.page-markdown-index',
        ])
        ->and($outputs->pluck('url')->all())->toBe([
            'https://example.com/docs/llms.txt',
            'https://example.com/docs/llms-full.txt',
            'https://example.com/docs/index.md',
        ]);
});

it('omits disabled ai discovery outputs', function (): void {
    $source = new AiDiscoveryDiscoveryOutputSource;
    $domain = new SiteDomain([
        'domain' => 'example.com',
        'scheme' => 'https',
    ]);

    $disabledProfile = new AiDiscoverySiteProfile([
        'llms_txt_enabled' => true,
        'llms_full_txt_enabled' => true,
        'markdown_pages_enabled' => true,
        'status' => AiDiscoveryStatusEnum::Disabled,
    ]);

    $partialProfile = new AiDiscoverySiteProfile([
        'llms_txt_enabled' => true,
        'llms_full_txt_enabled' => false,
        'markdown_pages_enabled' => false,
        'status' => AiDiscoveryStatusEnum::Enabled,
    ]);

    expect($source->outputsForProfile($disabledProfile, $domain))->toHaveCount(0)
        ->and($source->outputsForProfile($partialProfile, $domain)->pluck('key')->all())->toBe([
            'seo-suite.llms-txt',
        ]);
});
