<?php

declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $prefixes = [
        'Capell\\Experiments\\Tests\\' => __DIR__ . '/',
        'Capell\\Experiments\\' => __DIR__ . '/../src/',
    ];

    foreach ($prefixes as $prefix => $basePath) {
        if (! str_starts_with($class, $prefix)) {
            continue;
        }

        $relativeClass = substr($class, strlen($prefix));
        $file = $basePath . str_replace('\\', '/', $relativeClass) . '.php';

        if (is_file($file)) {
            require $file;
        }
    }
});

use Capell\Experiments\Tests\ExperimentsTestCase;

pest()->extend(ExperimentsTestCase::class)->group('experiments')->in(__DIR__);
