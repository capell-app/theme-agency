<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Support;

use Illuminate\Support\Facades\File;
use SplFileInfo;

final class KnowledgeRepository
{
    /** @return array<int, array<string, mixed>> */
    public function packages(): array
    {
        $packageFiles = File::glob(base_path('packages/**/capell.json'));
        $packages = [];

        foreach ($packageFiles as $packageFile) {
            $decoded = json_decode(File::get($packageFile), true);

            if (! is_array($decoded)) {
                continue;
            }

            $product = is_array($decoded['product'] ?? null) ? $decoded['product'] : [];

            $packages[] = [
                'name' => $decoded['name'] ?? basename(dirname((string) $packageFile)),
                'description' => $decoded['description'] ?? null,
                'productGroup' => $product['group'] ?? null,
                'tier' => $product['tier'] ?? null,
                'bundle' => $product['bundle'] ?? null,
                'contexts' => $decoded['surfaces'] ?? $decoded['contexts'] ?? [],
                'capabilities' => $decoded['capabilities'] ?? [],
                'requires' => $decoded['dependencies']['requires'] ?? $decoded['requires'] ?? [],
                'path' => dirname((string) $packageFile),
            ];
        }

        return $packages;
    }

    /** @return array<int, array<string, string|null>> */
    public function documents(): array
    {
        $paths = [];

        foreach (config('capell-agent-bridge.public_docs_paths', []) as $configuredPath) {
            foreach (File::glob($configuredPath) as $path) {
                if (is_dir($path)) {
                    foreach (File::allFiles($path) as $file) {
                        $this->appendMarkdownDocument($paths, $file);
                    }

                    continue;
                }

                $this->appendMarkdownPath($paths, $path);
            }
        }

        return array_values($paths);
    }

    public function readDocument(string $relativePath): ?string
    {
        $normalized = trim(str_replace('\\', '/', $relativePath), '/');

        foreach ($this->documents() as $document) {
            if (($document['path'] ?? null) !== $normalized) {
                continue;
            }

            $absolutePath = base_path($normalized);
            $resolvedPath = realpath($absolutePath);

            if ($resolvedPath === false) {
                return null;
            }

            $basePath = realpath(base_path());

            if ($basePath === false || ! str_starts_with($resolvedPath, $basePath . '/')) {
                return null;
            }

            return File::get($resolvedPath);
        }

        return null;
    }

    /**
     * @param  array<int, array<string, string|null>>  $paths
     */
    private function appendMarkdownDocument(array &$paths, SplFileInfo $file): void
    {
        $this->appendMarkdownPath($paths, $file->getPathname());
    }

    /**
     * @param  array<int, array<string, string|null>>  $paths
     */
    private function appendMarkdownPath(array &$paths, string $path): void
    {
        if (! str_ends_with($path, '.md')) {
            return;
        }

        $paths[] = [
            'path' => str_replace(base_path() . '/', '', $path),
            'title' => basename($path, '.md'),
        ];
    }
}
