<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Tests\Fixtures;

use Capell\Diagnostics\Contracts\CommandPaletteProvider;
use Capell\Diagnostics\Data\CommandPaletteCommandData;
use Capell\Diagnostics\Data\CommandPaletteParameterData;
use Capell\Diagnostics\Enums\CommandPaletteParameterType;
use Capell\Diagnostics\Enums\CommandPaletteType;

final class CommandPalettePageTestProvider implements CommandPaletteProvider
{
    /**
     * @return array<string, CommandPaletteCommandData>
     */
    public function commandPaletteCommands(): array
    {
        return [
            'page.cache-clear' => new CommandPaletteCommandData(
                id: 'page.cache-clear',
                label: 'Clear cache',
                type: CommandPaletteType::Navigation,
                description: 'Refresh runtime caches',
                url: '/admin/cache',
                parameters: [
                    new CommandPaletteParameterData(
                        name: '--force',
                        label: 'Force',
                        type: CommandPaletteParameterType::Boolean,
                        default: false,
                    ),
                ],
                keywords: ['cache'],
                group: 'Operations',
            ),
            'page.hidden' => new CommandPaletteCommandData(
                id: 'page.hidden',
                label: 'Hidden command',
                type: CommandPaletteType::Navigation,
                ability: 'missing-command-ability',
                group: 'Operations',
            ),
        ];
    }
}
