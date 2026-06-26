<?php

declare(strict_types=1);

use Capell\AiCreator\Actions\BuildSiteFromSpecFileAction;
use Capell\AiCreator\Data\SiteSpec\CapellSiteSpecColorsData;
use Capell\AiCreator\Data\SiteSpec\CapellSiteSpecData;
use Capell\AiCreator\Data\SiteSpec\CapellSiteSpecLanguageData;
use Capell\AiCreator\Data\SiteSpec\CapellSiteSpecPageData;
use Capell\AiCreator\Data\SiteSpec\CapellSiteSpecSectionData;
use Capell\AiCreator\Data\SiteSpec\CapellSiteSpecSiteData;
use Capell\AiCreator\Data\SiteSpec\CapellSiteSpecThemeData;
use Capell\Core\Actions\CreateDefaultLanguagesAction;
use Capell\Core\Events\CapellInstalled;
use Capell\Core\Models\Site;
use Capell\FoundationTheme\Actions\InstallFoundationThemeLayoutDefaultsAction;
use Illuminate\Validation\ValidationException;

beforeEach(function (): void {
    InstallFoundationThemeLayoutDefaultsAction::run();
    CreateDefaultLanguagesAction::run(['en']);
});

function installedSpecFixturePath(): string
{
    $spec = new CapellSiteSpecData(
        site: new CapellSiteSpecSiteData(name: 'Harbour Books'),
        theme: new CapellSiteSpecThemeData(
            key: 'harbour',
            colors: new CapellSiteSpecColorsData(primary: '#123456'),
        ),
        pages: [
            new CapellSiteSpecPageData(
                name: 'Home',
                slug: 'home',
                title: 'Welcome',
                pageType: 'default',
                order: 0,
                sections: [
                    new CapellSiteSpecSectionData(type: 'content', content: '<p>Hello.</p>'),
                ],
            ),
        ],
        language: new CapellSiteSpecLanguageData,
    );

    $path = tempnam(sys_get_temp_dir(), 'capell-installed-spec-') . '.json';
    file_put_contents($path, json_encode($spec->toArray(), JSON_THROW_ON_ERROR));

    return $path;
}

it('builds a site when the core CapellInstalled event fires with a spec path', function (): void {
    $specPath = installedSpecFixturePath();

    try {
        event(new CapellInstalled($specPath, true));
    } finally {
        @unlink($specPath);
    }

    expect(Site::query()->where('name', 'Harbour Books')->exists())->toBeTrue();
});

it('builds a site from a spec file via the shared reader action', function (): void {
    $specPath = installedSpecFixturePath();

    try {
        $site = BuildSiteFromSpecFileAction::run($specPath);
    } finally {
        @unlink($specPath);
    }

    expect($site)->toBeInstanceOf(Site::class)
        ->and($site->name)->toBe('Harbour Books');
});

it('throws when the spec file cannot be read', function (): void {
    BuildSiteFromSpecFileAction::run('/no/such/spec.json');
})->throws(RuntimeException::class, 'Could not read a spec file');

it('rejects a spec file that is not valid JSON', function (): void {
    // JsonCodec::decodeArray swallows malformed JSON to [], which then fails
    // CapellSiteSpecData validation (missing required site/theme/pages).
    $path = tempnam(sys_get_temp_dir(), 'capell-bad-spec-') . '.json';
    file_put_contents($path, 'not json {');

    try {
        BuildSiteFromSpecFileAction::run($path);
    } finally {
        @unlink($path);
    }
})->throws(ValidationException::class);
