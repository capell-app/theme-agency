<?php

declare(strict_types=1);

use Capell\Core\Database\Factories\UserFactory;
use Capell\PasswordPolicy\Actions\EvaluatePasswordPolicyAction;
use Capell\PasswordPolicy\Actions\MarkUserForPasswordChangeAction;
use Capell\PasswordPolicy\Actions\ValidatePasswordChangeAction;
use Capell\PasswordPolicy\Data\PasswordChangeData;
use Capell\PasswordPolicy\Data\ResolvedPasswordPolicySettingsData;
use Capell\PasswordPolicy\Filament\Pages\PasswordPolicySettingsPage;
use Capell\PasswordPolicy\Filament\Settings\PasswordPolicySettingsSchema;
use Capell\PasswordPolicy\Health\PasswordPolicyHealthCheck;
use Capell\PasswordPolicy\Settings\PasswordPolicySettings;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

it('builds settings schema controls and exposes package metadata', function (): void {
    $components = PasswordPolicySettingsSchema::make(Schema::make());

    expect(PasswordPolicySettings::group())->toBe('password_policy')
        ->and(PasswordPolicySettings::schema())->toBe(PasswordPolicySettingsSchema::class)
        ->and(PasswordPolicyHealthCheck::compatibleCapellApiVersion())->toBe('^4.0')
        ->and($components)->toHaveCount(3)
        ->and($components[0])->toBeInstanceOf(Grid::class)
        ->and($components[1])->toBeInstanceOf(Grid::class)
        ->and($components[2])->toBeInstanceOf(Grid::class);

    $firstGridComponents = rawPasswordPolicySettingsChildComponents($components[0]);
    $thirdGridComponents = rawPasswordPolicySettingsChildComponents($components[2]);

    expect($firstGridComponents[0])->toBeInstanceOf(Toggle::class)
        ->and($firstGridComponents[1])->toBeInstanceOf(TextInput::class)
        ->and($thirdGridComponents[0])->toBeInstanceOf(Toggle::class)
        ->and($thirdGridComponents[1])->toBeInstanceOf(Toggle::class)
        ->and($thirdGridComponents[2])->toBeInstanceOf(TextInput::class);
});

it('marks missing password change timestamps as expired when expiry is enabled', function (): void {
    $settings = PasswordPolicySettings::instance();
    $settings->password_expiry_enabled = true;
    $settings->password_expiry_days = 30;
    $settings->force_change_enabled = false;
    $settings->save();

    $user = UserFactory::new()->create([
        'password_changed_at' => null,
    ]);

    $status = EvaluatePasswordPolicyAction::run($user);

    expect($status->passwordExpired)->toBeTrue()
        ->and($status->reason)->toBe('missing_password_changed_at')
        ->and($status->shouldRedirect())->toBeTrue();
});

it('keeps password change data and resolved settings as typed boundaries', function (): void {
    $input = new PasswordChangeData(
        password: 'new-password',
        passwordConfirmation: 'new-password',
        currentPassword: 'old-password',
        requireCurrentPassword: false,
    );
    $settings = new ResolvedPasswordPolicySettingsData(
        passwordExpiryEnabled: true,
        passwordExpiryDays: 60,
        forceChangeEnabled: true,
        compromisedPasswordChecksEnabled: false,
        passwordHistoryEnabled: true,
        passwordHistoryCount: 4,
    );

    expect($input->password)->toBe('new-password')
        ->and($input->requireCurrentPassword)->toBeFalse()
        ->and($settings->passwordExpiryDays)->toBe(60)
        ->and($settings->passwordHistoryCount)->toBe(4);
});

it('validates current password when required', function (): void {
    $user = UserFactory::new()->create([
        'password' => Hash::make('old-password'),
    ]);

    expect(fn (): mixed => ValidatePasswordChangeAction::run(
        $user,
        new PasswordChangeData(
            password: 'new-password',
            passwordConfirmation: 'new-password',
            currentPassword: 'wrong-password',
        ),
        false,
    ))->toThrow(ValidationException::class);
});

it('marks users for password changes when the backing column exists', function (): void {
    $user = UserFactory::new()->create([
        'must_change_password' => false,
    ]);

    MarkUserForPasswordChangeAction::run($user);
    $user->refresh();

    expect((bool) $user->getAttribute('must_change_password'))->toBeTrue();
});

it('exposes password policy settings page labels and form schema', function (): void {
    $page = new PasswordPolicySettingsPage;
    $schema = $page->form(Schema::make());

    expect(PasswordPolicySettingsPage::getNavigationLabel())->toBeString()
        ->and(PasswordPolicySettingsPage::getNavigationGroup())->toBeString()
        ->and($page->getTitle())->toBeString()
        ->and($schema)->toBeInstanceOf(Schema::class);
});

/**
 * @return array<int, object>
 */
function rawPasswordPolicySettingsChildComponents(Grid $grid): array
{
    $reflectionProperty = new ReflectionProperty($grid, 'childComponents');
    $childComponents = $reflectionProperty->getValue($grid);

    return $childComponents['default'] ?? [];
}
