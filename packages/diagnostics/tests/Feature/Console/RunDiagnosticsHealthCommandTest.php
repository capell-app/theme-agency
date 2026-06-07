<?php

declare(strict_types=1);

use Symfony\Component\Console\Command\Command as SymfonyCommand;

it('requires a single health export format', function (): void {
    $this->artisan('capell:diagnostics:health', [
        '--json' => true,
        '--csv' => true,
    ])
        ->expectsOutputToContain((string) __('capell-diagnostics::package.health_command_single_export_format'))
        ->assertExitCode(SymfonyCommand::FAILURE);
});
