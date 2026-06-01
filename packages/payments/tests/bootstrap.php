<?php

declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $testPrefix = 'Capell\\Payments\\Tests\\';

    if (str_starts_with($class, $testPrefix)) {
        $relativeClass = substr($class, strlen($testPrefix));
        $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';
        $testPath = __DIR__ . '/' . $relativePath;

        if (is_file($testPath)) {
            require_once $testPath;
        }

        return;
    }

    $prefix = 'Capell\\Payments\\';

    if (! str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';
    $sourcePath = dirname(__DIR__) . '/src/' . $relativePath;
    $testPath = __DIR__ . '/' . $relativePath;

    if (is_file($sourcePath)) {
        require_once $sourcePath;

        return;
    }

    if (is_file($testPath)) {
        require_once $testPath;
    }
});
