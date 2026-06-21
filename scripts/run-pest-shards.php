<?php

declare(strict_types=1);

$shards = max(1, (int) ($_SERVER['PEST_SHARDS'] ?? getenv('PEST_SHARDS') ?: 10));
$phpBinary = PHP_BINARY;
$configuration = 'phpunit.xml';
$scope = (string) ($argv[1] ?? $_SERVER['PEST_PREFLIGHT_SCOPE'] ?? getenv('PEST_PREFLIGHT_SCOPE') ?: 'focused');
try {
    $files = testFiles($scope);
} catch (Throwable $throwable) {
    fwrite(STDERR, $throwable->getMessage() . PHP_EOL);

    return 1;
}
$timings = shardTimings();
$partitions = partitionFiles($files, $timings, $shards);
$partitionWeights = partitionWeights($partitions, $timings);
$processes = [];
$exitCode = 0;
$startedAt = microtime(true);

printf("[pest-shards] Scope: %s; files: %d; shards: %d.\n", $scope, count($files), $shards);

if (count(array_unique(array_values($timings))) <= 1) {
    echo '[pest-shards] Timing manifest is unweighted; run composer test:profile for slow-file evidence.' . PHP_EOL;
}

foreach ($partitions as $index => $partitionFiles) {
    $shard = $index + 1;

    if ($partitionFiles === []) {
        continue;
    }

    $command = [
        $phpBinary,
        '-d',
        'memory_limit=1536M',
        '-d',
        'max_execution_time=0',
        '-d',
        'pcov.enabled=0',
        'vendor/bin/pest',
        ...$partitionFiles,
        '--colors=always',
        '--compact',
        '--stop-on-error',
        '--stop-on-failure',
        "--configuration={$configuration}",
    ];

    $process = proc_open(
        $command,
        [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ],
        $pipes,
    );

    if (! is_resource($process)) {
        fwrite(STDERR, "Unable to start Pest shard {$shard}/{$shards}." . PHP_EOL);

        return 1;
    }

    stream_set_blocking($pipes[1], false);
    stream_set_blocking($pipes[2], false);

    $processes[$shard] = [
        'process' => $process,
        'pipes' => $pipes,
        'started_at' => microtime(true),
        'files' => count($partitionFiles),
        'weight' => $partitionWeights[$index] ?? 0.0,
    ];

    printf(
        "[shard %d] Running %d test files; estimated weight %.2f.\n",
        $shard,
        count($partitionFiles),
        $partitionWeights[$index] ?? 0.0,
    );
}

while ($processes !== []) {
    foreach ($processes as $index => $process) {
        $stdout = stream_get_contents($process['pipes'][1]);
        $stderr = stream_get_contents($process['pipes'][2]);

        if ($stdout !== false && $stdout !== '') {
            echo "[shard {$index}] {$stdout}";
        }

        if ($stderr !== false && $stderr !== '') {
            fwrite(STDERR, "[shard {$index}] {$stderr}");
        }

        $status = proc_get_status($process['process']);

        if ($status['running']) {
            continue;
        }

        fclose($process['pipes'][1]);
        fclose($process['pipes'][2]);

        $code = proc_close($process['process']);

        if ($code === -1 && isset($status['exitcode']) && $status['exitcode'] !== -1) {
            $code = $status['exitcode'];
        }

        if ($code !== 0) {
            $exitCode = $code;
        }

        printf(
            "[shard %d] Finished in %.2fs with exit code %d.\n",
            $index,
            microtime(true) - $process['started_at'],
            $code,
        );

        unset($processes[$index]);
    }

    usleep(100_000);
}

printf("[pest-shards] Finished %d shards in %.2fs.\n", $shards, microtime(true) - $startedAt);

return $exitCode;

/**
 * @return list<string>
 */
function testFiles(string $scope): array
{
    $files = [];

    foreach (['tests', 'packages'] as $directory) {
        if (! is_dir($directory)) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if (! $file->isFile() || ! str_ends_with($file->getFilename(), 'Test.php')) {
                continue;
            }

            $files[] = $file->getPathname();
        }
    }

    sort($files);
    $files = array_values(array_unique($files));

    if ($scope === 'full') {
        return $files;
    }

    if ($scope !== 'focused') {
        throw new InvalidArgumentException("Unsupported Pest preflight scope [{$scope}].");
    }

    return focusedPreflightFiles($files);
}

/**
 * @return array<string, float>
 */
function shardTimings(): array
{
    $path = 'tests/.pest/shards.json';

    if (! is_file($path)) {
        return [];
    }

    $contents = file_get_contents($path);

    if ($contents === false) {
        return [];
    }

    $data = json_decode($contents, true);

    if (! is_array($data) || ! isset($data['timings']) || ! is_array($data['timings'])) {
        return [];
    }

    $timings = [];

    foreach ($data['timings'] as $file => $timing) {
        if (! is_string($file) || (! is_int($timing) && ! is_float($timing))) {
            continue;
        }

        $timings[$file] = (float) $timing;
    }

    return $timings;
}

/**
 * @param  list<string>  $files
 * @param  array<string, float>  $timings
 * @return list<list<string>>
 */
function partitionFiles(array $files, array $timings, int $shards): array
{
    $filesWithTimings = array_map(
        static fn (string $file): array => ['file' => $file, 'time' => (float) ($timings[$file] ?? 1.0)],
        $files,
    );

    usort($filesWithTimings, static fn (array $left, array $right): int => $right['time'] <=> $left['time']);

    $partitions = array_fill(0, $shards, []);
    $partitionTimes = array_fill(0, $shards, 0.0);

    foreach ($filesWithTimings as $fileWithTiming) {
        $partition = array_search(min($partitionTimes), $partitionTimes, strict: true);
        assert(is_int($partition));

        $partitions[$partition][] = $fileWithTiming['file'];
        $partitionTimes[$partition] += $fileWithTiming['time'];
    }

    return $partitions;
}

/**
 * @param  list<list<string>>  $partitions
 * @param  array<string, float>  $timings
 * @return list<float>
 */
function partitionWeights(array $partitions, array $timings): array
{
    return array_map(
        static fn (array $files): float => array_reduce(
            $files,
            static fn (float $weight, string $file): float => $weight + (float) ($timings[$file] ?? 1.0),
            0.0,
        ),
        $partitions,
    );
}

/**
 * @param  list<string>  $files
 * @return list<string>
 */
function focusedPreflightFiles(array $files): array
{
    $patterns = [
        'tests/Feature/Manifest*Test.php',
        'tests/Packages/Arch/*Test.php',
        'tests/Packages/Security/*Test.php',
        'tests/Packages/BoostResourcesTest.php',
        'tests/Packages/Feature/Admin*Test.php',
        'tests/Packages/Feature/CoverageGapBehaviorTest.php',
        'tests/Packages/Feature/Package*Test.php',
        'tests/Packages/Feature/PremiumThemeContractTest.php',
        'tests/Packages/Feature/ThemeFrontend*Test.php',
        'tests/Packages/Integration/CrossPackageBootTest.php',
        'tests/Packages/Integration/FilamentPackageNavigationTest.php',
        'tests/Packages/ManifestTruthTest.php',
        'packages/*/tests/Arch/*Test.php',
        'packages/*/tests/*/ManifestRequirementsTest.php',
        'packages/*/tests/*/*/ManifestRequirementsTest.php',
        'packages/*/tests/*/*/*/ManifestRequirementsTest.php',
        'packages/*/tests/*/*HealthCheckTest.php',
        'packages/*/tests/*/*/*HealthCheckTest.php',
        'packages/*/tests/*/Providers/*ServiceProviderTest.php',
        'packages/*/tests/*/*/Providers/*ServiceProviderTest.php',
    ];

    return array_values(array_filter(
        $files,
        static fn (string $file): bool => matchesAnyPattern($file, $patterns),
    ));
}

/**
 * @param  list<string>  $patterns
 */
function matchesAnyPattern(string $file, array $patterns): bool
{
    foreach ($patterns as $pattern) {
        if (fnmatch($pattern, $file)) {
            return true;
        }
    }

    return false;
}
