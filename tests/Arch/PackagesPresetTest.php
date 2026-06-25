<?php

declare(strict_types=1);

use Capell\BlockLibrary\Support\BlockRegistryManifestStore;
use Capell\BlockLibrary\Support\BuilderBlockDiscovery;
use Capell\MigrationAssistant\Services\Import\ManifestValidator;
use Capell\TranslationManager\Support\FileTranslationFileStore;

/*
|--------------------------------------------------------------------------
| Central arch preset for the feature packages
|--------------------------------------------------------------------------
|
| Mirrors capell-4's packages/core/tests/Arch/PackagesTest.php so every Capell
| feature package is held to the same baseline: the Pest php/laravel/security
| presets, no leftover debug/forbidden functions, and strict equality. Prior to
| this only ~16/141 packages had any tests/Arch coverage.
|
| Deliberate, reviewed exceptions go in the ->ignoring([...]) allow-lists with a
| comment, exactly as core does.
|
*/

// These classes legitimately use var_export to render PHP files (block-registry
// manifests, builder-block discovery caches, import manifests, and language
// files that all write `return [...]` arrays to disk), so they are scoped out of
// the var_export checks below — the same kind of reviewed exception core makes.
$varExportWriters = [
    BlockRegistryManifestStore::class,
    BuilderBlockDiscovery::class,
    ManifestValidator::class,
    FileTranslationFileStore::class,
];

// core/admin/frontend ship as Composer path repos, so their classes resolve into
// the Capell\* namespace and get swept into these presets even though they live in
// capell-4 and are governed by capell-4's own packages/core/tests/Arch/PackagesTest.php.
// This test is for the 141 feature packages only, so exclude the sibling-repo
// namespaces wholesale rather than chasing individual classes.
$siblingRepos = ['Capell\Core', 'Capell\Admin', 'Capell\Frontend'];

arch()->preset()->php()->ignoring([...$varExportWriters, ...$siblingRepos]);

arch()->preset()->laravel()->ignoring($siblingRepos);

arch()->preset()->security()->ignoring($siblingRepos);

it('does not allow debug or forbidden functions in feature packages')
    ->expect(['dd', 'dump', 'print_r', 'die', 'ray', 'rd', 'var_dump', 'var_export', 'exit', 'sleep', 'usleep'])
    ->toBeUsedInNothing()
    ->ignoring([
        BlockRegistryManifestStore::class,
        BuilderBlockDiscovery::class,
        ManifestValidator::class,
        FileTranslationFileStore::class,
        ...$siblingRepos, // core/admin/frontend are governed by capell-4 (see note above)
    ]);

arch('feature package classes use strict equality')
    ->expect('Capell')
    ->classes()
    ->toUseStrictEquality();
