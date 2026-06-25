<?php

declare(strict_types=1);

namespace Capell\FrontendOptimizer\Actions;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use RuntimeException;

final class BuildFrontendBundleReportAction
{
    /**
     * @return array{
     *     built_at: string,
     *     git_sha: string|null,
     *     release_id: string|null,
     *     entries: list<array{source: string, file: string, type: string, entrypoint: string, raw_bytes: int, gzip_bytes: int}>,
     *     totals: array<string, array{raw_bytes: int, gzip_bytes: int, files: int}>
     * }
     */
    public function handle(?string $buildPath = null): array
    {
        $buildPath ??= public_path('build');
        $manifestPath = $buildPath . '/manifest.json';

        if (! File::exists($manifestPath)) {
            throw new RuntimeException('Vite manifest not found at ' . $manifestPath . '. Run npm run build first.');
        }

        $manifest = json_decode(File::get($manifestPath), true);

        if (! is_array($manifest)) {
            throw new RuntimeException('Vite manifest is not valid JSON.');
        }

        $entries = [];

        foreach ($manifest as $source => $asset) {
            if (! is_array($asset) || ! is_string($asset['file'] ?? null)) {
                continue;
            }

            $file = $asset['file'];
            $absolutePath = $buildPath . '/' . $file;

            if (! File::exists($absolutePath)) {
                continue;
            }

            $contents = File::get($absolutePath);
            $type = $this->typeFor($file);
            $gzip = gzencode($contents);

            $entries[] = [
                'source' => (string) $source,
                'file' => $file,
                'type' => $type,
                'entrypoint' => $this->entrypointFor($asset),
                'raw_bytes' => strlen($contents),
                'gzip_bytes' => strlen(is_string($gzip) ? $gzip : ''),
            ];
        }

        return [
            'built_at' => (string) now()->toISOString(),
            'git_sha' => $this->gitSha(),
            'release_id' => $this->releaseId(),
            'entries' => $entries,
            'totals' => $this->totals($entries),
        ];
    }

    /**
     * @param  array<string, mixed>  $asset
     */
    private function entrypointFor(array $asset): string
    {
        if (($asset['isEntry'] ?? false) === true) {
            return 'entry';
        }

        if (($asset['isDynamicEntry'] ?? false) === true) {
            return 'dynamic';
        }

        return 'asset';
    }

    private function typeFor(string $file): string
    {
        return match (Str::afterLast($file, '.')) {
            'css' => 'css',
            'js', 'mjs' => 'js',
            default => 'asset',
        };
    }

    /**
     * @param  list<array{type: string, entrypoint: string, raw_bytes: int, gzip_bytes: int}>  $entries
     * @return array<string, array{raw_bytes: int, gzip_bytes: int, files: int}>
     */
    private function totals(array $entries): array
    {
        $totals = [];

        foreach ($entries as $entry) {
            foreach ([$entry['type'], $entry['entrypoint'] . ':' . $entry['type']] as $key) {
                $totals[$key] ??= ['raw_bytes' => 0, 'gzip_bytes' => 0, 'files' => 0];
                $totals[$key]['raw_bytes'] += $entry['raw_bytes'];
                $totals[$key]['gzip_bytes'] += $entry['gzip_bytes'];
                $totals[$key]['files']++;
            }
        }

        ksort($totals);

        return $totals;
    }

    private function gitSha(): ?string
    {
        $result = Process::path(base_path())->run(['git', 'rev-parse', '--short', 'HEAD']);

        if (! $result->successful()) {
            return null;
        }

        $sha = trim($result->output());

        return $sha !== '' ? $sha : null;
    }

    private function releaseId(): ?string
    {
        $release = config('app.release');

        return is_scalar($release) && (string) $release !== '' ? (string) $release : null;
    }
}
