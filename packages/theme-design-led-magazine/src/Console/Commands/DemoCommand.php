<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\DesignLedMagazine\Console\Commands;

use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\ThemeStudio\DesignLedMagazine\Actions\InstallDesignLedMagazineThemeDemoAction;
use Illuminate\Console\Command;

final class DemoCommand extends Command
{
    protected $signature = 'capell:theme-design-led-magazine-demo {--url=} {--languages=} {--sites=} {--force}';

    protected $description = 'Install Design Led Magazine theme demo content.';

    public function handle(): int
    {
        return InstallDesignLedMagazineThemeDemoAction::run(new ThemeDemoInstallData(
            siteNames: $this->parseCsvOption('sites'),
            languageCodes: $this->parseCsvOption('languages'),
            baseUrl: $this->resolveBaseUrl(),
            force: (bool) $this->option('force'),
        ));
    }

    /** @return array<int, string> */
    private function parseCsvOption(string $option): array
    {
        $value = $this->option($option);

        if (is_array($value)) {
            return array_values(array_filter(array_map(static fn (mixed $item): string => trim((string) $item), $value), static fn (string $item): bool => $item !== ''));
        }

        if (! is_string($value) || $value === '') {
            return [];
        }

        return array_values(array_filter(array_map(trim(...), explode(',', $value)), static fn (string $item): bool => $item !== ''));
    }

    private function resolveBaseUrl(): string
    {
        $url = $this->option('url');

        return is_string($url) && $url !== '' ? $url : (string) config('app.url');
    }
}
