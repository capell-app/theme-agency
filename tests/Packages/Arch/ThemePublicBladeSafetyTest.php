<?php

declare(strict_types=1);

require_once __DIR__ . '/../Support/ThemeFrontendTestSupport.php';

it('keeps public theme blade views free of queries and authoring surface', function (): void {
    $viewRoots = [
        base_path('packages/foundation-theme/resources/views'),
        ...glob(base_path('packages/theme-*/resources/views')) ?: [],
    ];
    $viewPaths = [];

    foreach ($viewRoots as $viewRoot) {
        if (! is_dir($viewRoot)) {
            continue;
        }

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewRoot));

        foreach ($files as $file) {
            if ($file instanceof SplFileInfo && $file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                $viewPaths[] = $file->getPathname();
            }
        }
    }
    $forbiddenPatterns = [
        '::query(',
        'DB::',
        'loadMissing(',
        'authoring/regions',
        'edit_url',
        'recordKey',
        'CapellFrontendAuthoring',
        'capell-frontend-authoring',
        'signed editor',
        'signed_editor',
        'data-capell-authoring',
        'capell-authoring',
    ];
    $failures = [];

    foreach ($viewPaths as $viewPath) {
        $contents = file_get_contents($viewPath);

        if (! is_string($contents)) {
            continue;
        }

        foreach ($forbiddenPatterns as $pattern) {
            if (str_contains($contents, $pattern)) {
                $failures[] = str_replace(base_path() . '/', '', $viewPath) . ' contains ' . $pattern;
            }
        }
    }

    expect($failures)->toBe([]);
});
