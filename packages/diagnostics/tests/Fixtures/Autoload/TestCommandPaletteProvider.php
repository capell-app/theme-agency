<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Tests\Fixtures\Autoload;

use Capell\Diagnostics\Contracts\CommandPaletteProvider;
use Capell\Diagnostics\Data\CommandPaletteCommandData;
use Capell\Diagnostics\Enums\CommandPaletteType;

final class TestCommandPaletteProvider implements CommandPaletteProvider
{
    /**
     * @return array<string, CommandPaletteCommandData>
     */
    public function commandPaletteCommands(): array
    {
        return [
            'test.artisan' => testPaletteArtisanCommand(),
            'test.navigate' => new CommandPaletteCommandData(
                id: 'test.navigate',
                label: 'Open system health',
                type: CommandPaletteType::Navigation,
                url: '/admin/diagnostics/system-health',
                sort: 10,
            ),
            'test.confirmed' => new CommandPaletteCommandData(
                id: 'test.confirmed',
                label: 'Confirmed command',
                type: CommandPaletteType::Navigation,
                url: '/admin/diagnostics/system-health',
                requiresConfirmation: true,
                sort: 30,
            ),
        ];
    }
}
