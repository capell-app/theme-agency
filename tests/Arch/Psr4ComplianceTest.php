<?php

declare(strict_types=1);

namespace Capell\Tests\Arch;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

it('all named classes in the test suite live in their own PSR-4 file', function (): void {
    $testsPath = realpath(__DIR__ . '/..');

    throw_if(
        in_array($testsPath, ['', '0', false], true) || ! is_dir($testsPath),
        RuntimeException::class,
        'Tests path does not exist: ' . $testsPath,
    );

    $violations = [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($testsPath, RecursiveDirectoryIterator::SKIP_DOTS),
    );

    foreach ($iterator as $file) {
        if (! $file instanceof SplFileInfo) {
            continue;
        }

        if ($file->getExtension() !== 'php') {
            continue;
        }

        // Laravel migrations are anonymous-class returning files keyed by an
        // intentionally non-PSR-4 timestamped filename — never PSR-4 classes.
        if (str_contains($file->getPathname(), '/database/migrations/')) {
            continue;
        }

        $expectedBasename = $file->getBasename('.php');
        $tokens = token_get_all((string) file_get_contents($file->getPathname()));
        $count = count($tokens);

        for ($i = 0; $i < $count; $i++) {
            if (! is_array($tokens[$i]) || $tokens[$i][0] !== T_CLASS) {
                continue;
            }

            // Find the first meaningful token after `class`, skipping whitespace
            // and comments. A named class is followed by a T_STRING (its name);
            // an anonymous class (`new class extends X`, `new class(...)`,
            // `new class {`) is followed by `extends`/`implements`/`(`/`{`.
            $name = null;

            for ($j = $i + 1; $j < $count; $j++) {
                if (is_array($tokens[$j])) {
                    if (in_array($tokens[$j][0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                        continue;
                    }

                    if ($tokens[$j][0] === T_STRING) {
                        $name = $tokens[$j][1];
                    }
                }

                // Any other token (T_EXTENDS, T_IMPLEMENTS, `(`, `{`, …) ends the
                // lookahead — for an anonymous class $name stays null.
                break;
            }

            if ($name === null || $name === $expectedBasename) {
                continue;
            }

            $relative = str_replace($testsPath . '/', '', $file->getPathname());
            $violations[] = "Class {$name} in {$relative} — move to {$name}.php in a Fixtures/ subdirectory";
        }
    }

    expect($violations)->toBeEmpty();
});
