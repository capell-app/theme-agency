<?php

declare(strict_types=1);

use Symfony\Component\Finder\Finder;

it('keeps payments controllers thin', function (): void {
    $packagePath = dirname(__DIR__, 2);
    $violations = [];
    $forbiddenFragments = [
        '::query(',
        'DB::',
        'Http::',
        'Storage::',
        'forceFill(',
        '->save(',
        'updateOrCreate(',
        'firstOrCreate(',
    ];

    $files = (new Finder)
        ->files()
        ->in($packagePath . '/src/Http/Controllers')
        ->name('*.php');

    foreach ($files as $file) {
        $contents = (string) file_get_contents($file->getPathname());

        foreach ($forbiddenFragments as $fragment) {
            if (str_contains($contents, $fragment)) {
                $violations[] = sprintf('%s contains %s', $file->getRelativePathname(), $fragment);
            }
        }
    }

    expect($violations)->toBeEmpty();
});

it('marks payments frontend surfaces as sensitive and non-cacheable', function (): void {
    $manifest = paymentsPackageManifest();

    expect($manifest['performance']['frontendRenderBudgetMs'])->toBe(0)
        ->and($manifest['performance']['cacheSafety']['cacheable'])->toBeFalse()
        ->and($manifest['performance']['cacheSafety']['sensitiveOutput'])->toBeTrue()
        ->and($manifest['performance']['cacheSafety']['variesBy'])->toContain('site', 'customer');
});

it('does not ship public output files with frontend authoring markers', function (): void {
    $packagePath = dirname(__DIR__, 2);
    $publicOutputPaths = array_filter([
        $packagePath . '/resources/views',
        $packagePath . '/resources/js',
        $packagePath . '/resources/css',
        $packagePath . '/public',
    ], is_dir(...));
    $forbiddenFragments = [
        'frontend-authoring',
        'capell-authoring',
        'data-capell-edit',
        'data-field-path',
        'data-model-id',
        'signed-editor',
        'signedEditor',
    ];
    $violations = [];

    if ($publicOutputPaths === []) {
        expect($violations)->toBeEmpty();

        return;
    }

    $files = (new Finder)
        ->files()
        ->in($publicOutputPaths)
        ->name(['*.blade.php', '*.php', '*.js', '*.css', '*.html']);

    foreach ($files as $file) {
        $contents = (string) file_get_contents($file->getPathname());

        foreach ($forbiddenFragments as $fragment) {
            if (str_contains($contents, $fragment)) {
                $violations[] = sprintf('%s contains %s', $file->getRelativePathname(), $fragment);
            }
        }
    }

    expect($violations)->toBeEmpty();
});

arch()
    ->expect('Capell\Payments')
    ->classes()
    ->toUseStrictEquality();

/**
 * @return array<string, mixed>
 */
function paymentsPackageManifest(): array
{
    $contents = file_get_contents(dirname(__DIR__, 2) . '/capell.json');

    return json_decode(
        $contents === false ? '[]' : $contents,
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );
}
