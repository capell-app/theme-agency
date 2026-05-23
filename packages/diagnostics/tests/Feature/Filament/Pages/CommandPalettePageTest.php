<?php

declare(strict_types=1);

use Capell\Diagnostics\Data\CommandPaletteCommandData;
use Capell\Diagnostics\Enums\CommandPaletteDanger;
use Capell\Diagnostics\Enums\CommandPaletteType;
use Capell\Diagnostics\Enums\DiagnosticsPermission;
use Capell\Diagnostics\Filament\Pages\CommandPalettePage;
use Capell\Diagnostics\Tests\Fixtures\CommandPalettePageTestProvider;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Role::findOrCreate(config('capell.roles.super_admin', 'super_admin'));
});

it('allows super admins to access the command palette', function (): void {
    $user = $this->createUserWithRole(config('capell.roles.super_admin', 'super_admin'));

    $this->actingAs($user);

    expect(CommandPalettePage::canAccess())->toBeTrue();
});

it('allows users through diagnostics gates', function (DiagnosticsPermission $permission): void {
    $this->actingAs($this->createUser());

    Gate::define($permission->value, fn (): bool => true);

    expect(CommandPalettePage::canAccess())->toBeTrue();
})->with([
    DiagnosticsPermission::AccessDiagnostics,
    DiagnosticsPermission::ViewDiagnostics,
]);

it('groups, filters, selects, and clears visible command palette commands', function (): void {
    app()->instance(CommandPalettePageTestProvider::class, new CommandPalettePageTestProvider);
    app()->tag([CommandPalettePageTestProvider::class], 'capell.diagnostics.command-palette-provider');

    $this->actingAs($this->createUser());

    $page = new CommandPalettePage;

    expect(CommandPalettePage::getNavigationLabel())->toBe('Command Palette')
        ->and(CommandPalettePage::getNavigationGroup())->toBeString()
        ->and($page->getTitle())->toBe('Command Palette')
        ->and($page->selectedCommand())->toBeNull();

    $page->query = 'cache';

    expect($page->groupedCommands())->toHaveKey('Operations')
        ->and($page->groupedCommands()['Operations'])->toHaveCount(1);

    $page->selectCommand('page.cache-clear');

    expect($page->selectedCommand()?->id)->toBe('page.cache-clear')
        ->and($page->parameters)->toBe(['--force' => false]);

    $page->clearSelection();

    expect($page->selectedCommandId)->toBeNull()
        ->and($page->parameters)->toBe([])
        ->and($page->warningFor(new CommandPaletteCommandData(
            id: 'safe',
            label: 'Safe',
            type: CommandPaletteType::Navigation,
        )))->toBeNull()
        ->and($page->warningFor(new CommandPaletteCommandData(
            id: 'confirm',
            label: 'Confirm',
            type: CommandPaletteType::Navigation,
            danger: CommandPaletteDanger::Confirm,
        )))->toContain('requires confirmation')
        ->and($page->warningFor(new CommandPaletteCommandData(
            id: 'dangerous',
            label: 'Dangerous',
            type: CommandPaletteType::Navigation,
            danger: CommandPaletteDanger::Dangerous,
        )))->toContain('destructive');
});
