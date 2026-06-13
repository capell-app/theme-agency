<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\EstateAgents\Console\Commands;

use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\ThemeStudio\EstateAgents\Actions\InstallEstateAgentsThemeDemoAction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

final class DemoCommand extends Command
{
    protected $signature = 'capell:theme-estate-agents-demo {--url=} {--languages=} {--sites=} {--force}';

    protected $description = 'Install Estate Agents theme demo content.';

    public function handle(): int
    {
        return (new InstallEstateAgentsThemeDemoAction)->handle(new ThemeDemoInstallData(
            siteNames: $this->parseCsvOption('sites'),
            languageCodes: $this->parseCsvOption('languages'),
            baseUrl: $this->resolveBaseUrl(),
            force: (bool) $this->option('force'),
        ));
    }

    /**
     * @return array<int, string>
     */
    private function parseCsvOption(string $option): array
    {
        $value = $this->option($option);

        if (is_array($value)) {
            return array_values(array_filter(
                array_map(
                    static fn (string $item): string => trim($item),
                    array_filter(
                        $value,
                        static fn (mixed $item): bool => is_string($item),
                    ),
                ),
                static fn (string $item): bool => $item !== '',
            ));
        }

        if (! is_string($value) || $value === '') {
            return [];
        }

        return array_values(array_filter(
            array_map(static fn (string $item): string => trim($item), explode(',', $value)),
            static fn (string $item): bool => $item !== '',
        ));
    }

    private function resolveBaseUrl(): string
    {
        $url = $this->option('url');

        if (is_string($url) && $url !== '') {
            return $url;
        }

        return Config::string('app.url', url('/'));
    }
}
