<?php

declare(strict_types=1);

$testbenchCacheDirectory = dirname(__DIR__) . '/vendor/orchestra/testbench-core/laravel/bootstrap/cache';

if (! is_dir($testbenchCacheDirectory)) {
    mkdir($testbenchCacheDirectory, 0777, true);
}

$packagesCachePath = $testbenchCacheDirectory . '/packages.php';
$servicesCachePath = $testbenchCacheDirectory . '/services.php';

if (! is_file($packagesCachePath)) {
    file_put_contents($packagesCachePath, '<?php return [];');
}

if (! is_file($servicesCachePath)) {
    file_put_contents($servicesCachePath, "<?php return ['providers' => [], 'eager' => [], 'deferred' => [], 'when' => []];");
}
