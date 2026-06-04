<?php

declare(strict_types=1);

use Capell\Diagnostics\Actions\CommandPalette\DiscoverCommandPaletteCommandsAction;
use Capell\Diagnostics\Actions\CommandPalette\ExecuteCommandPaletteCommandAction;
use Capell\Diagnostics\Actions\CommandPalette\RedactCommandPaletteOutputAction;
use Capell\Diagnostics\Actions\CommandPalette\ValidateCommandPaletteParametersAction;
use Capell\Diagnostics\Data\CommandPaletteCommandData;
use Capell\Diagnostics\Data\CommandPaletteParameterData;
use Capell\Diagnostics\Enums\CommandPaletteDanger;
use Capell\Diagnostics\Enums\CommandPaletteParameterType;
use Capell\Diagnostics\Enums\CommandPaletteType;
use Capell\Diagnostics\Models\CommandPaletteRun;
use Capell\Diagnostics\Palette\CapellArtisanPaletteCommandProvider;
use Capell\Diagnostics\Tests\Fixtures\Autoload\TestCommandPaletteProvider;
use Capell\Diagnostics\Tests\Fixtures\SecretOutputCommandPaletteProvider;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Console\Command\Command;

it('discovers command palette commands from tagged providers in sort order', function (): void {
    app()->instance(TestCommandPaletteProvider::class, new TestCommandPaletteProvider);
    app()->tag([TestCommandPaletteProvider::class], 'capell.diagnostics.command-palette-provider');

    $commands = DiscoverCommandPaletteCommandsAction::run();
    $navigateIndex = array_search('test.navigate', array_keys($commands), true);
    $artisanIndex = array_search('test.artisan', array_keys($commands), true);

    throw_if(! is_int($navigateIndex) || ! is_int($artisanIndex), RuntimeException::class, 'Expected test command palette commands to be discovered.');

    expect(array_keys($commands))->toContain('test.navigate')
        ->and(array_keys($commands))->toContain('test.artisan')
        ->and($navigateIndex)
        ->toBeLessThan($artisanIndex);
});

it('validates command palette parameters using command parameter metadata', function (): void {
    $command = testPaletteArtisanCommand();

    $validated = ValidateCommandPaletteParametersAction::run($command, [
        'name' => 'Ben',
        '--loud' => true,
    ]);

    expect($validated)->toBe([
        'name' => 'Ben',
        '--loud' => true,
    ]);

    ValidateCommandPaletteParametersAction::run($command, [
        '--loud' => true,
    ]);
})->throws(ValidationException::class);

it('executes navigation commands and records successful command palette runs', function (): void {
    app()->instance(TestCommandPaletteProvider::class, new TestCommandPaletteProvider);
    app()->tag([TestCommandPaletteProvider::class], 'capell.diagnostics.command-palette-provider');

    $user = $this->createUser();

    $result = ExecuteCommandPaletteCommandAction::run('test.navigate', [], $user);

    $run = CommandPaletteRun::query()->firstOrFail();

    expect($result->successful)->toBeTrue()
        ->and($result->url)->toBe('/admin/system-health')
        ->and($result->runId)->toBe($run->getKey())
        ->and($run->status)->toBe('succeeded')
        ->and($run->command_id)->toBe('test.navigate');
});

it('executes artisan commands with validated parameters and stores command output', function (): void {
    $user = $this->createUser();

    Artisan::command('capell:test-output {name} {--loud}', function (): int {
        $name = $this->argument('name');
        $message = 'Hello ' . (is_scalar($name) ? (string) $name : '');

        if ($this->option('loud') === true) {
            $message = strtoupper($message);
        }

        $this->line($message);

        return Command::SUCCESS;
    });
    app()->instance(TestCommandPaletteProvider::class, new TestCommandPaletteProvider);
    app()->tag([TestCommandPaletteProvider::class], 'capell.diagnostics.command-palette-provider');

    $result = ExecuteCommandPaletteCommandAction::run('test.artisan', [
        'name' => 'Ben',
        '--loud' => true,
    ], $user);

    $run = CommandPaletteRun::query()->latest('id')->firstOrFail();

    expect($result->successful)->toBeTrue()
        ->and($result->title)->toBe('Test output completed')
        ->and($result->body)->toContain('HELLO BEN')
        ->and($run->output)->toContain('HELLO BEN')
        ->and($run->exit_code)->toBe(0);
});

it('redacts secrets before returning and storing command output', function (): void {
    $user = $this->createUser();

    Artisan::command('capell:test-secret-output', function (): int {
        $this->line('DB_PASSWORD=super-secret');
        $this->line('Authorization: Bearer sk_test_1234567890abcdef');
        $this->line('DATABASE_URL=mysql://user:secret-pass@example.test/db');
        $this->line('Public value stays visible.');

        return Command::SUCCESS;
    });

    app()->instance(SecretOutputCommandPaletteProvider::class, new SecretOutputCommandPaletteProvider);
    app()->tag([SecretOutputCommandPaletteProvider::class], 'capell.diagnostics.command-palette-provider');

    $result = ExecuteCommandPaletteCommandAction::run('test.secret-output', [], $user);
    $run = CommandPaletteRun::query()->latest('id')->firstOrFail();

    expect($result->body)->toContain('DB_PASSWORD=[redacted]')
        ->and($result->body)->toContain('Bearer [redacted]')
        ->and($result->body)->toContain('mysql://user:[redacted]@example.test/db')
        ->and($result->body)->toContain('Public value stays visible.')
        ->and($result->body)->not->toContain('super-secret')
        ->and($result->body)->not->toContain('secret-pass')
        ->and($run->output)->toBe($result->body);
});

it('redacts common secret output formats directly', function (): void {
    $output = RedactCommandPaletteOutputAction::run(implode(PHP_EOL, [
        'api_key: abc123secret',
        "'client_secret' => hunter2",
        'Nothing sensitive',
    ]));

    expect($output)->toContain('api_key: [redacted]')
        ->and($output)->toContain("'client_secret' => [redacted]")
        ->and($output)->toContain('Nothing sensitive')
        ->and($output)->not->toContain('hunter2');
});

it('requires confirmation before executing commands marked for confirmation', function (): void {
    app()->instance(TestCommandPaletteProvider::class, new TestCommandPaletteProvider);
    app()->tag([TestCommandPaletteProvider::class], 'capell.diagnostics.command-palette-provider');

    ExecuteCommandPaletteCommandAction::run('test.confirmed', [], $this->createUser());
})->throws(AuthorizationException::class);

it('exposes capell artisan commands as palette commands with parameter metadata', function (): void {
    Artisan::command('capell:test-provider {name} {--force}', fn (): int => Command::SUCCESS)->describe('Run the test provider command.');

    $commands = (new CapellArtisanPaletteCommandProvider)->commandPaletteCommands();
    $command = $commands['artisan.capell:test-provider'];

    expect($command->label)->toBe('Test Provider')
        ->and($command->description)->toBe('Run the test provider command.')
        ->and($command->command)->toBe('capell:test-provider')
        ->and($command->parameters)->toHaveCount(2)
        ->and($command->parameters[0]->name)->toBe('name')
        ->and($command->parameters[0]->required)->toBeTrue()
        ->and($command->parameters[1]->name)->toBe('--force')
        ->and($command->parameters[1]->type)->toBe(CommandPaletteParameterType::Boolean);
});

it('requires confirmation for unmapped capell artisan commands', function (): void {
    Artisan::command('capell:test-untagged-destructive', fn (): int => Command::SUCCESS);

    $commands = (new CapellArtisanPaletteCommandProvider)->commandPaletteCommands();
    $command = $commands['artisan.capell:test-untagged-destructive'];

    expect($command->danger)->toBe(CommandPaletteDanger::Confirm)
        ->and($command->requiresConfirmation)->toBeTrue();
});

it('uses explicit risk mapping for known capell artisan commands', function (string $artisanCommand, CommandPaletteDanger $expectedDanger, bool $expectedConfirmation): void {
    ensurePaletteArtisanCommandExists($artisanCommand);
    $commands = (new CapellArtisanPaletteCommandProvider)->commandPaletteCommands();
    $command = $commands['artisan.' . $artisanCommand];

    expect($command->danger)->toBe($expectedDanger)
        ->and($command->requiresConfirmation)->toBe($expectedConfirmation);
})->with([
    'safe diagnostics health' => ['capell:diagnostics:health', CommandPaletteDanger::Safe, false],
    'confirm html cache clear' => ['capell:html-cache:clear', CommandPaletteDanger::Confirm, true],
    'dangerous install' => ['capell:install', CommandPaletteDanger::Dangerous, true],
    'dangerous demo' => ['capell:demo', CommandPaletteDanger::Dangerous, true],
]);

function testPaletteArtisanCommand(): CommandPaletteCommandData
{
    return new CommandPaletteCommandData(
        id: 'test.artisan',
        label: 'Test output',
        type: CommandPaletteType::Artisan,
        command: 'capell:test-output',
        parameters: [
            new CommandPaletteParameterData(
                name: 'name',
                label: 'Name',
                type: CommandPaletteParameterType::String,
                required: true,
            ),
            new CommandPaletteParameterData(
                name: '--loud',
                label: 'Loud',
                type: CommandPaletteParameterType::Boolean,
            ),
        ],
        sort: 20,
    );
}

function ensurePaletteArtisanCommandExists(string $artisanCommand): void
{
    if (array_key_exists($artisanCommand, Artisan::all())) {
        return;
    }

    Artisan::registerCommand(
        new ClosureCommand($artisanCommand, fn (): int => Command::SUCCESS),
    );
}
