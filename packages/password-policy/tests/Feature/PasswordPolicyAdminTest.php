<?php

declare(strict_types=1);

use Capell\Admin\Contracts\Extenders\AdminPanelExtender;
use Capell\Admin\Contracts\Extenders\UserFormExtender;
use Capell\Admin\Contracts\Extenders\UserTableExtender;
use Capell\Admin\Data\Bridges\AdminBridgeContextData;
use Capell\Admin\Facades\CapellAdmin;
use Capell\Admin\Filament\Pages\SettingsPage;
use Capell\Admin\Filament\Resources\Users\Pages\CreateUser;
use Capell\Admin\Filament\Resources\Users\Pages\EditUser;
use Capell\Admin\Filament\Widgets\Extensions\InstalledExtensionsWidget;
use Capell\Admin\Support\Bridges\AdminBridgeRegistrar;
use Capell\Admin\Support\CapellAdminManager;
use Capell\Admin\Support\Extensions\ExtensionManagementSurfaceRegistry;
use Capell\Core\Database\Factories\UserFactory;
use Capell\Core\Support\Settings\SettingsSchemaRegistry;
use Capell\PasswordPolicy\Actions\MarkUserForPasswordChangeAction;
use Capell\PasswordPolicy\Bridges\PasswordPolicyAdminBridge;
use Capell\PasswordPolicy\Filament\Extenders\PasswordPolicyPanelExtender;
use Capell\PasswordPolicy\Filament\Extenders\PasswordPolicyUserFormExtender;
use Capell\PasswordPolicy\Filament\Extenders\PasswordPolicyUserTableExtender;
use Capell\PasswordPolicy\Filament\Pages\ForcedPasswordChangePage;
use Capell\PasswordPolicy\Filament\Pages\PasswordPolicySettingsPage;
use Capell\PasswordPolicy\Providers\PasswordPolicyServiceProvider;
use Capell\PasswordPolicy\Settings\PasswordPolicySettings;
use Capell\Tests\Support\Concerns\CreatesAdminUser;
use Capell\Tests\Support\LegacyAdminBridgeFallbackHost;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

use function Pest\Laravel\get;

use Spatie\LaravelPackageTools\Package;
use Spatie\Permission\Models\Permission;

uses(CreatesAdminUser::class)->group('password-policy');

beforeEach(function (): void {
    Permission::create(['name' => 'View:SettingsPage', 'guard_name' => 'web']);
    test()->actingAsAdmin();
    auth()->user()->givePermissionTo('View:SettingsPage');
});

function invokePasswordPolicyProviderMethod(object $provider, string $method): void
{
    $reflection = new ReflectionMethod($provider, $method);
    $reflection->invoke($provider);
}

function resetPasswordPolicyAdminBridgeState(): void
{
    app()->forgetInstance(CapellAdminManager::class);
    app()->forgetInstance(ExtensionManagementSurfaceRegistry::class);
    CapellAdmin::clearResolvedInstance(CapellAdminManager::class);
    CapellAdmin::clearAdminSurfaceContributions();
}

it('keeps package settings out of the global settings page', function (): void {
    $registry = resolve(SettingsSchemaRegistry::class);

    if (! method_exists($registry, 'getFirstPartyGroups')) {
        test()->markTestSkipped('This Capell Core checkout does not expose first-party settings groups.');
    }

    expect($registry->getFirstPartyGroups())->not->toContain('password_policy');

    get(SettingsPage::getUrl())
        ->assertSuccessful()
        ->assertDontSeeHtml('password_expiry_enabled');
});

it('declares its installable database migrations', function (): void {
    $package = new Package;

    (new PasswordPolicyServiceProvider(app()))->configurePackage($package);

    expect($package->migrationFileNames)->toBe([
        '2026_05_10_190863_01_add_password_policy_columns_to_users_table',
        '2026_05_10_190863_02_create_password_policy_password_histories_table',
    ]);
});

it('registers password policy settings as an extension management surface', function (): void {
    $settingsSurfaces = resolve(ExtensionManagementSurfaceRegistry::class)
        ->surfacesForPackage(PasswordPolicyServiceProvider::$packageName);

    expect($settingsSurfaces[0]->settingsGroup ?? null)->toBe('password_policy');
});

it('registers the current password policy admin bridge surface', function (): void {
    resetPasswordPolicyAdminBridgeState();

    (new PasswordPolicyAdminBridge)->register(
        new AdminBridgeRegistrar,
        AdminBridgeContextData::forPackage(PasswordPolicyServiceProvider::$packageName),
    );

    $settingsSurfaces = resolve(ExtensionManagementSurfaceRegistry::class)
        ->surfacesForPackage(PasswordPolicyServiceProvider::$packageName);

    expect($settingsSurfaces[0]->settingsGroup ?? null)
        ->toBe('password_policy')
        ->and(CapellAdmin::getAdminSurfaceRegistry()->pages())->toContain(ForcedPasswordChangePage::class)
        ->and(CapellAdmin::getAdminSurfaceRegistry()->panelExtenders())->toContain(PasswordPolicyPanelExtender::class)
        ->and(collect(app()->tagged(UserFormExtender::TAG))->contains(
            fn (object $extender): bool => $extender instanceof PasswordPolicyUserFormExtender,
        ))->toBeTrue()
        ->and(collect(app()->tagged(UserTableExtender::TAG))->contains(
            fn (object $extender): bool => $extender instanceof PasswordPolicyUserTableExtender,
        ))->toBeTrue();
});

it('opens password policy settings from the extensions page action modal', function (): void {
    Permission::create(['name' => 'View:ExtensionsPage', 'guard_name' => 'web']);
    auth()->user()->givePermissionTo('View:ExtensionsPage');

    Livewire::test(InstalledExtensionsWidget::class)
        ->assertSuccessful()
        ->assertSee(__('capell-password-policy::settings.title'))
        ->mountTableAction('manageExtension', PasswordPolicyServiceProvider::$packageName)
        ->assertMountedActionModalSee(__('capell-password-policy::settings.title'))
        ->assertMountedActionModalSee(__('capell-password-policy::settings.password_expiry_enabled'));
});

it('keeps the legacy admin fallback when the bridge host is unavailable', function (): void {
    $host = new LegacyAdminBridgeFallbackHost;
    CapellAdmin::swap($host);

    try {
        invokePasswordPolicyProviderMethod(new PasswordPolicyServiceProvider(app()), 'registerAdminSurface');

        $adminPanelExtenders = collect(app()->tagged(AdminPanelExtender::TAG))
            ->map(fn (object $extender): string => $extender::class);
        $userFormExtenders = collect(app()->tagged(UserFormExtender::TAG))
            ->map(fn (object $extender): string => $extender::class);
        $userTableExtenders = collect(app()->tagged(UserTableExtender::TAG))
            ->map(fn (object $extender): string => $extender::class);

        expect($host->extensionPages[PasswordPolicyServiceProvider::$packageName] ?? [])
            ->toBe([])
            ->and(collect($host->surfaceContributions)->pluck('class'))->toContain(ForcedPasswordChangePage::class)
            ->and($adminPanelExtenders)->toContain(PasswordPolicyPanelExtender::class)
            ->and($userFormExtenders)->toContain(PasswordPolicyUserFormExtender::class)
            ->and($userTableExtenders)->toContain(PasswordPolicyUserTableExtender::class);
    } finally {
        app()->forgetInstance(CapellAdminManager::class);
        CapellAdmin::clearResolvedInstance(CapellAdminManager::class);
    }
});

it('saves password security settings from the package settings page', function (): void {
    Livewire::test(PasswordPolicySettingsPage::class)
        ->assertSuccessful()
        ->fillForm([
            'password_expiry_enabled' => true,
            'password_expiry_days' => 45,
            'force_change_enabled' => true,
            'compromised_password_checks_enabled' => true,
            'password_history_enabled' => true,
            'password_history_count' => 6,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $settings = PasswordPolicySettings::instance();

    expect($settings->password_expiry_enabled)->toBeTrue()
        ->and($settings->password_expiry_days)->toBe(45)
        ->and($settings->force_change_enabled)->toBeTrue()
        ->and($settings->compromised_password_checks_enabled)->toBeTrue()
        ->and($settings->password_history_enabled)->toBeTrue()
        ->and($settings->password_history_count)->toBe(6);
});

it('redirects flagged admin users to the forced password change page', function (): void {
    $settings = PasswordPolicySettings::instance();
    $settings->force_change_enabled = true;
    $settings->save();

    MarkUserForPasswordChangeAction::run(auth()->user());

    get('/admin')
        ->assertRedirect(ForcedPasswordChangePage::getUrl());
});

it('uses the package policy when admin users are created with passwords', function (): void {
    if (! interface_exists(UserFormExtender::class)) {
        test()->markTestSkipped('Capell Admin user form extension points are not available in this checkout.');
    }

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Secure User',
            'email' => 'secure-user@example.test',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
            'roles' => [],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $userModel = config('auth.providers.users.model');
    $user = $userModel::query()
        ->where('email', 'secure-user@example.test')
        ->firstOrFail();

    expect(Hash::check('new-password', (string) $user->getAttribute('password')))->toBeTrue()
        ->and($user->getAttribute('password_changed_at'))->not->toBeNull()
        ->and((bool) $user->getAttribute('must_change_password'))->toBeFalse();
});

it('uses the package history policy when admin users are edited with passwords', function (): void {
    if (! interface_exists(UserFormExtender::class)) {
        test()->markTestSkipped('Capell Admin user form extension points are not available in this checkout.');
    }

    $settings = PasswordPolicySettings::instance();
    $settings->password_history_enabled = true;
    $settings->password_history_count = 5;
    $settings->save();

    $user = UserFactory::new()->create([
        'password' => Hash::make('old-password'),
        'password_changed_at' => null,
        'must_change_password' => true,
    ]);

    Livewire::test(EditUser::class, ['record' => $user->getKey()])
        ->fillForm([
            'name' => $user->getAttribute('name'),
            'email' => $user->getAttribute('email'),
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
            'roles' => [],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $user->refresh();

    expect(Hash::check('new-password', (string) $user->getAttribute('password')))->toBeTrue()
        ->and($user->getAttribute('password_changed_at'))->not->toBeNull()
        ->and((bool) $user->getAttribute('must_change_password'))->toBeFalse();

    Livewire::test(EditUser::class, ['record' => $user->getKey()])
        ->fillForm([
            'name' => $user->getAttribute('name'),
            'email' => $user->getAttribute('email'),
            'password' => 'old-password',
            'password_confirmation' => 'old-password',
            'roles' => [],
        ])
        ->call('save')
        ->assertHasErrors(['password']);
});

it('adds password policy user table columns, filters, and require-change actions', function (): void {
    $settings = PasswordPolicySettings::instance();
    $settings->force_change_enabled = true;
    $settings->password_expiry_days = 30;
    $settings->save();

    $extender = new PasswordPolicyUserTableExtender;
    $user = UserFactory::new()->create([
        'must_change_password' => false,
        'password_changed_at' => now()->subDays(60),
    ]);

    $markUser = new ReflectionMethod($extender, 'markUser');
    $expiredPasswordQuery = new ReflectionMethod($extender, 'expiredPasswordQuery');

    expect($extender->columns())->toHaveCount(2)
        ->and($extender->filters())->toHaveCount(3)
        ->and($extender->recordActions())->toHaveCount(1)
        ->and($extender->toolbarActions())->toHaveCount(1)
        ->and($expiredPasswordQuery->invoke($extender, $user->newQuery()))->toBeInstanceOf(Builder::class)
        ->and($markUser->invoke($extender, $user))->toBeNull();

    expect((bool) $user->refresh()->getAttribute('must_change_password'))->toBeTrue();
});

it('hides force-change table actions when force-change enforcement is disabled', function (): void {
    $settings = PasswordPolicySettings::instance();
    $settings->force_change_enabled = false;
    $settings->save();

    $extender = resolve(PasswordPolicyUserTableExtender::class);

    expect($extender->recordActions())->toBe([])
        ->and($extender->toolbarActions())->toBe([]);

    $settings->force_change_enabled = true;
    $settings->save();

    expect($extender->recordActions())->not->toBe([])
        ->and($extender->toolbarActions())->not->toBe([]);
});

it('renders the forced password change page through Filament', function (): void {
    Livewire::test(ForcedPasswordChangePage::class)
        ->assertSuccessful()
        ->assertSee(__('capell-password-policy::password_change.description'));
});
