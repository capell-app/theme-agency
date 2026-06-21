<?php

declare(strict_types=1);

$shards = max(1, (int) ($_SERVER['PEST_SHARDS'] ?? getenv('PEST_SHARDS') ?: 6));
$phpBinary = PHP_BINARY;
$configuration = 'phpunit.xml';
try {
    $files = testFiles();
} catch (Throwable $throwable) {
    fwrite(STDERR, $throwable->getMessage() . PHP_EOL);

    return 1;
}
$timings = shardTimings();
$partitions = partitionFiles($files, $timings, $shards);
$processes = [];
$exitCode = 0;

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
    ];

    echo "[shard {$shard}] Running " . count($partitionFiles) . ' test files.' . PHP_EOL;
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

        unset($processes[$index]);
    }

    usleep(100_000);
}

return $exitCode;

/**
 * @return list<string>
 */
function testFiles(): array
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

    return array_values(array_unique($files));
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
