<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Tests\Fixtures;

use Capell\Diagnostics\Contracts\CommandPaletteProvider;
use Capell\Diagnostics\Data\CommandPaletteCommandData;
use Capell\Diagnostics\Enums\CommandPaletteType;

final class SecretOutputCommandPaletteProvider implements CommandPaletteProvider
{
    /**
     * @return array<string, CommandPaletteCommandData>
     */
    public function commandPaletteCommands(): array
    {
        return [
            'test.secret-output' => new CommandPaletteCommandData(
                id: 'test.secret-output',
                label: 'Test secret output',
                type: CommandPaletteType::Artisan,
                command: 'capell:test-secret-output',
                sort: 20,
            ),
        ];
    }
}
