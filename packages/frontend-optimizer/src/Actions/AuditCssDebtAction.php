<?php

declare(strict_types=1);

namespace Capell\FrontendOptimizer\Actions;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final class AuditCssDebtAction
{
    /**
     * @param  list<string>  $paths
     * @return array{
     *     files: list<string>,
     *     duplicate_tokens: list<array<string, mixed>>,
     *     repeated_selectors: list<array<string, mixed>>,
     *     important_hotspots: list<array{file: string, count: int}>,
     *     largest_blocks: list<array{selector: string, file: string, declarations: int, bytes: int}>
     * }
     */
    public function handle(array $paths = []): array
    {
        $files = $this->cssFiles($paths === [] ? [
            resource_path('css'),
            public_path('build/assets'),
        ] : $paths);

        $tokens = [];
        $selectors = [];
        $importantHotspots = [];
        $largestBlocks = [];

        foreach ($files as $file) {
            $css = File::get($file);
            $relativeFile = $this->relativePath($file);

            preg_match_all('/(--[a-zA-Z0-9_-]+)\s*:/', $css, $tokenMatches);

            foreach ($tokenMatches[1] as $token) {
                $tokens[$token]['count'] = ($tokens[$token]['count'] ?? 0) + 1;
                $tokens[$token]['files'][$relativeFile] = true;
            }

            preg_match_all('/([^{}@][^{}]*)\{([^{}]*)\}/m', $css, $blockMatches, PREG_SET_ORDER);

            foreach ($blockMatches as $match) {
                $selector = trim(preg_replace('/\s+/', ' ', $match[1]) ?? '');

                if ($selector === '' || str_contains($selector, 'from ') || str_contains($selector, 'to ')) {
                    continue;
                }

                $body = trim($match[2]);
                $declarations = max(0, substr_count($body, ';'));

                $selectors[$selector]['count'] = ($selectors[$selector]['count'] ?? 0) + 1;
                $selectors[$selector]['files'][$relativeFile] = true;

                $largestBlocks[] = [
                    'selector' => $selector,
                    'file' => $relativeFile,
                    'declarations' => $declarations,
                    'bytes' => strlen($match[0]),
                ];
            }

            $importantCount = substr_count($css, '!important');

            if ($importantCount > 0) {
                $importantHotspots[] = [
                    'file' => $relativeFile,
                    'count' => $importantCount,
                ];
            }
        }

        usort($importantHotspots, static fn (array $a, array $b): int => $b['count'] <=> $a['count']);
        usort($largestBlocks, static fn (array $a, array $b): int => [$b['declarations'], $b['bytes']] <=> [$a['declarations'], $a['bytes']]);

        return [
            'files' => array_map(fn (string $file): string => $this->relativePath($file), $files),
            'duplicate_tokens' => $this->duplicates($tokens, 'name'),
            'repeated_selectors' => $this->duplicates($selectors, 'selector'),
            'important_hotspots' => array_slice($importantHotspots, 0, 20),
            'largest_blocks' => array_slice($largestBlocks, 0, 20),
        ];
    }

    /**
     * @param  list<string>  $paths
     * @return list<string>
     */
    private function cssFiles(array $paths): array
    {
        $files = [];

        foreach ($paths as $path) {
            if (File::isFile($path) && Str::endsWith($path, '.css')) {
                $files[] = $path;

                continue;
            }

            if (! File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if ($file->getExtension() === 'css') {
                    $files[] = $file->getPathname();
                }
            }
        }

        sort($files);

        return array_values(array_unique($files));
    }

    /**
     * @param  array<string, array{count: int, files: array<string, bool>}>  $items
     * @return list<array<string, mixed>>
     */
    private function duplicates(array $items, string $key): array
    {
        $duplicates = [];

        foreach ($items as $value => $item) {
            if ($item['count'] < 2) {
                continue;
            }

            $duplicates[] = [
                $key => $value,
                'count' => $item['count'],
                'files' => array_keys($item['files']),
            ];
        }

        usort($duplicates, static fn (array $a, array $b): int => [$b['count'], $a[$key]] <=> [$a['count'], $b[$key]]);

        return array_slice($duplicates, 0, 50);
    }

    private function relativePath(string $path): string
    {
        return Str::of($path)
            ->replace(base_path() . DIRECTORY_SEPARATOR, '')
            ->replace(DIRECTORY_SEPARATOR, '/')
            ->toString();
    }
}
