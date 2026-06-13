<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\LiquidGlass\Console\Commands;

use Capell\FoundationTheme\Contracts\InstallsThemeDemo;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\ThemeStudio\LiquidGlass\Actions\InstallLiquidGlassThemeDemoAction;
use Illuminate\Console\Command;
use RuntimeException;

final class DemoCommand extends Command
{
    protected $signature = 'capell:theme-liquid-glass-demo {--url=} {--languages=} {--sites=} {--force}';

    protected $description = 'Install Liquid Glass theme demo content.';

    public function handle(): int
    {
        return $this->installer()->handle(new ThemeDemoInstallData(
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
            $items = [];

            foreach ($value as $item) {
                if (! is_scalar($item)) {
                    continue;
                }

                $item = trim((string) $item);

                if ($item !== '') {
                    $items[] = $item;
                }
            }

            return $items;
        }

        if (! is_string($value) || $value === '') {
            return [];
        }

        return array_values(array_filter(
            array_map(trim(...), explode(',', $value)),
            static fn (string $item): bool => $item !== '',
        ));
    }

    private function resolveBaseUrl(): string
    {
        $url = $this->option('url');

        if (is_string($url) && $url !== '') {
            return $url;
        }

        $appUrl = config('app.url');

        return is_string($appUrl) && $appUrl !== '' ? $appUrl : 'http://localhost';
    }

    private function installer(): InstallsThemeDemo
    {
        $installer = app(InstallLiquidGlassThemeDemoAction::class);

        if (! $installer instanceof InstallsThemeDemo) {
            throw new RuntimeException('Theme Liquid Glass demo installer is not registered.');
        }

        return $installer;
    }
}
