<?php

declare(strict_types=1);

namespace Capell\Tests\Fixtures;

use Capell\Core\Support\Migration\MigrationFilesystemInterface;

class FakeMigrationFileManager implements MigrationFilesystemInterface
{
    /**
     * @var array<array-key, mixed>
     */
    public array $calls = [];

    /**
     * @param  array<array-key, mixed>  $overrides
     */
    public function __construct(private array $overrides = []) {}

    public function fileExists(string $path): bool
    {
        $this->calls[] = ['fileExists', $path];

        return $this->overrides['fileExists'][$path] ?? false;
    }

    public function isDir(string $path): bool
    {
        $this->calls[] = ['isDir', $path];

        return $this->overrides['isDir'][$path] ?? true;
    }

    public function isWritable(string $path): bool
    {
        $this->calls[] = ['isWritable', $path];

        return $this->overrides['isWritable'][$path] ?? true;
    }

    public function makeDir(string $path): void
    {
        $this->calls[] = ['makeDir', $path];
    }

    public function copy(string $from, string $to): void
    {
        $this->calls[] = ['copy', $from, $to];
    }

    /**
     * @return list<string>
     */
    public function glob(string $pattern): array
    {
        $this->calls[] = ['glob', $pattern];

        $result = $this->overrides['glob'][$pattern] ?? [];

        return is_array($result) ? array_values(array_filter($result, is_string(...))) : [];
    }

    public function delete(string $path): bool
    {
        $this->calls[] = ['delete', $path];

        return $this->overrides['delete'][$path] ?? true;
    }
}
