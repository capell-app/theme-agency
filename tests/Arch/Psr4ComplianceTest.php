<?php

declare(strict_types=1);

namespace Capell\Tests\Arch;

use RuntimeException;

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
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $expectedBasename = $file->getBasename('.php');
        $tokens = token_get_all((string) file_get_contents($file->getPathname()));
        $count = count($tokens);

        for ($i = 0; $i < $count; $i++) {
            if (! is_array($tokens[$i]) || $tokens[$i][0] !== T_CLASS) {
                continue;
            }

            for ($j = $i + 1; $j < $count; $j++) {
                if (! is_array($tokens[$j])) {
                    break;
                }

                if ($tokens[$j][0] === T_STRING) {
                    $className = $tokens[$j][1];

                    if ($className !== $expectedBasename) {
                        $relative = str_replace($testsPath . '/', '', $file->getPathname());
                        $violations[] = "Class {$className} in {$relative} — move to {$className}.php in a Fixtures/ subdirectory";
                    }

                    break;
                }
            }
        }
    }

    expect($violations)->toBeEmpty();
});
