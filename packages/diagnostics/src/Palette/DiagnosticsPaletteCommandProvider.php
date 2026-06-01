<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Palette;

use Capell\Diagnostics\Contracts\CommandPaletteProvider;
use Capell\Diagnostics\Data\CommandPaletteCommandData;
use Capell\Diagnostics\Enums\CommandPaletteType;
use Capell\Diagnostics\Filament\Pages\DiagnosticsPage;
use Capell\Diagnostics\Filament\Pages\QueueHealthPage;
use Capell\Diagnostics\Filament\Pages\SystemHealthPage;
use Throwable;

final class DiagnosticsPaletteCommandProvider implements CommandPaletteProvider
{
    /**
     * @return array<string, CommandPaletteCommandData>
     */
    public function commandPaletteCommands(): array
    {
        $commands = array_filter([
            $this->navigationCommand(
                id: 'diagnostics.open',
                label: (string) __('capell-diagnostics::package.command_palette_open_developer_tools'),
                description: (string) __('capell-diagnostics::package.command_palette_open_developer_tools_description'),
                page: DiagnosticsPage::class,
                keywords: ['developer', 'tools', 'registry', 'makers'],
                sort: 10,
            ),
            $this->navigationCommand(
                id: 'diagnostics.system-health',
                label: (string) __('capell-diagnostics::package.command_palette_open_system_health'),
                description: (string) __('capell-diagnostics::package.command_palette_open_system_health_description'),
                page: SystemHealthPage::class,
                keywords: ['health', 'cache', 'package', 'registry', 'migration'],
                sort: 11,
            ),
            $this->navigationCommand(
                id: 'diagnostics.queue-health',
                label: (string) __('capell-diagnostics::package.command_palette_view_failed_jobs'),
                description: (string) __('capell-diagnostics::package.command_palette_view_failed_jobs_description'),
                page: QueueHealthPage::class,
                keywords: ['queue', 'failed', 'jobs'],
                sort: 12,
            ),
        ]);

        return collect($commands)
            ->mapWithKeys(fn (CommandPaletteCommandData $command): array => [$command->id => $command])
            ->all();
    }

    /**
     * @param  class-string  $page
     * @param  array<int, string>  $keywords
     */
    private function navigationCommand(
        string $id,
        string $label,
        string $description,
        string $page,
        array $keywords,
        int $sort,
    ): ?CommandPaletteCommandData {
        try {
            return new CommandPaletteCommandData(
                id: $id,
                label: $label,
                type: CommandPaletteType::Navigation,
                description: $description,
                url: $page::getUrl(panel: 'admin'),
                keywords: [$page, ...$keywords],
                group: (string) __('capell-diagnostics::package.command_palette_group_developer_tools'),
                sort: $sort,
            );
        } catch (Throwable) {
            return null;
        }
    }
}
