<?php

declare(strict_types=1);

namespace Capell\Tests\Packages\Fixtures;

use Capell\DemoKit\Console\Commands\Concerns\HasLanguagesOption;
use Capell\DemoKit\Console\Commands\Concerns\HasSitesOption;
use Illuminate\Console\Command;
use Override;

final class CoverageGapDemoKitOptionsCommand extends Command
{
    use HasLanguagesOption;
    use HasSitesOption;

    protected $signature = 'coverage-gap:demo-kit-options';

    /** @param array<string, mixed> $options */
    public function __construct(private readonly array $options)
    {
        parent::__construct();
    }

    /**
     * @param  string|null  $key
     * @return ($key is null ? array<string, mixed> : mixed)
     */
    #[Override]
    public function option($key = null)
    {
        return $key === null ? $this->options : ($this->options[$key] ?? null);
    }

    /** @return array<int, string> */
    public function demoSites(): array
    {
        return $this->getDemoSites();
    }

    /** @return array<int, string> */
    public function demoLanguages(): array
    {
        return $this->getDemoLanguages();
    }
}
