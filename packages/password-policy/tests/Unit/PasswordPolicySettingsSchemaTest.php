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
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

it('builds settings schema controls and exposes package metadata', function (): void {
    $components = PasswordPolicySettingsSchema::make(Schema::make());

    expect(PasswordPolicySettings::group())->toBe('password_policy')
        ->and(PasswordPolicySettings::schema())->toBe(PasswordPolicySettingsSchema::class)
        ->and(PasswordPolicyHealthCheck::compatibleCapellApiVersion())->toBe('^4.0')
        ->and($components)->toHaveCount(4)
        ->and($components[0])->toBeInstanceOf(Grid::class)
        ->and($components[1])->toBeInstanceOf(Grid::class)
        ->and($components[2])->toBeInstanceOf(Grid::class)
        ->and($components[3])->toBeInstanceOf(Grid::class);

    $firstGridComponents = rawPasswordPolicySettingsChildComponents($components[0]);
    $thirdGridComponents = rawPasswordPolicySettingsChildComponents($components[2]);
    $fourthGridComponents = rawPasswordPolicySettingsChildComponents($components[3]);

    expect($firstGridComponents[0])->toBeInstanceOf(Toggle::class)
        ->and($firstGridComponents[1])->toBeInstanceOf(TextInput::class)
        ->and($thirdGridComponents[0])->toBeInstanceOf(TextInput::class)
        ->and($thirdGridComponents[1])->toBeInstanceOf(Toggle::class)
        ->and($thirdGridComponents[2])->toBeInstanceOf(Toggle::class)
        ->and($thirdGridComponents[3])->toBeInstanceOf(Toggle::class)
        ->and($fourthGridComponents[0])->toBeInstanceOf(Toggle::class)
        ->and($fourthGridComponents[1])->toBeInstanceOf(Toggle::class)
        ->and($fourthGridComponents[2])->toBeInstanceOf(TextInput::class);
});

it('does not expire missing legacy password change timestamps when expiry is enabled', function (): void {
    $settings = PasswordPolicySettings::instance();
    $settings->password_expiry_enabled = true;
    $settings->password_expiry_days = 30;
    $settings->force_change_enabled = false;
    $settings->save();

    $user = UserFactory::new()->create([
        'password_changed_at' => null,
    ]);

    $status = EvaluatePasswordPolicyAction::run($user);

    expect($status->passwordExpired)->toBeFalse()
        ->and($status->reason)->toBeNull()
        ->and($status->shouldRedirect())->toBeFalse();
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
        minimumPasswordLength: 12,
        requireMixedCase: true,
        requireNumbers: true,
        requireSymbols: true,
        compromisedPasswordChecksEnabled: false,
        passwordHistoryEnabled: true,
        passwordHistoryCount: 4,
    );

    expect($input->password)->toBe('new-password')
        ->and($input->requireCurrentPassword)->toBeFalse()
        ->and($settings->passwordExpiryDays)->toBe(60)
        ->and($settings->minimumPasswordLength)->toBe(12)
        ->and($settings->requireMixedCase)->toBeTrue()
        ->and($settings->requireNumbers)->toBeTrue()
        ->and($settings->requireSymbols)->toBeTrue()
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

it('enforces configured password complexity rules', function (): void {
    $settings = PasswordPolicySettings::instance();
    $settings->minimum_password_length = 12;
    $settings->require_mixed_case = true;
    $settings->require_numbers = true;
    $settings->require_symbols = true;
    $settings->save();

    expect(fn (): mixed => ValidatePasswordChangeAction::run(
        null,
        new PasswordChangeData(
            password: 'longpassword',
            passwordConfirmation: 'longpassword',
            requireCurrentPassword: false,
        ),
        false,
    ))->toThrow(ValidationException::class);

    ValidatePasswordChangeAction::run(
        null,
        new PasswordChangeData(
            password: 'Longpassword1!',
            passwordConfirmation: 'Longpassword1!',
            requireCurrentPassword: false,
        ),
        false,
    );

    expect(true)->toBeTrue();
});

it('checks compromised passwords through a faked hibp range request', function (): void {
    Http::preventStrayRequests();

    $password = 'KnownCompromised1!';
    $passwordHash = strtoupper(sha1($password));
    $hashPrefix = substr($passwordHash, 0, 5);
    $hashSuffix = substr($passwordHash, 5);

    Http::fake([
        'https://api.pwnedpasswords.com/range/' . $hashPrefix => Http::response($hashSuffix . ':12'),
    ]);

    expect(fn (): mixed => ValidatePasswordChangeAction::run(
        null,
        new PasswordChangeData(
            password: $password,
            passwordConfirmation: $password,
            requireCurrentPassword: false,
        ),
        true,
    ))->toThrow(ValidationException::class);

    Http::assertSentCount(1);
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
