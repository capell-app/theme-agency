<?php

declare(strict_types=1);

use Capell\Core\Database\Factories\UserFactory;
use Capell\PasswordPolicy\Actions\BuildPasswordSecurityPostureReportAction;
use Capell\PasswordPolicy\Actions\EvaluatePasswordPolicyAction;
use Capell\PasswordPolicy\Actions\UpdatePasswordAction;
use Capell\PasswordPolicy\Data\PasswordChangeData;
use Capell\PasswordPolicy\Health\PasswordPolicyHealthCheck;
use Capell\PasswordPolicy\Settings\PasswordPolicySettings;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

uses()->group('password-policy');

it('does nothing when all password security settings are disabled', function (): void {
    $user = UserFactory::new()->create([
        'password' => Hash::make('old-password'),
        'must_change_password' => true,
        'password_changed_at' => now()->subYears(2),
    ]);

    $status = EvaluatePasswordPolicyAction::run($user);

    expect($status->shouldRedirect())->toBeFalse();
});

it('requires a password change for flagged users when force change is enabled', function (): void {
    $settings = PasswordPolicySettings::instance();
    $settings->force_change_enabled = true;
    $settings->save();

    $user = UserFactory::new()->create([
        'must_change_password' => true,
    ]);

    $status = EvaluatePasswordPolicyAction::run($user);

    expect($status->mustChangePassword)->toBeTrue()
        ->and($status->reason)->toBe('forced');
});

it('requires a password change when password expiry is enabled and expired', function (): void {
    $settings = PasswordPolicySettings::instance();
    $settings->password_expiry_enabled = true;
    $settings->password_expiry_days = 30;
    $settings->save();

    $user = UserFactory::new()->create([
        'password_changed_at' => now()->subDays(31),
    ]);

    $status = EvaluatePasswordPolicyAction::run($user);

    expect($status->passwordExpired)->toBeTrue()
        ->and($status->reason)->toBe('expired');
});

it('still requires a forced change for flagged legacy users without a password timestamp', function (): void {
    $settings = PasswordPolicySettings::instance();
    $settings->force_change_enabled = true;
    $settings->password_expiry_enabled = true;
    $settings->password_expiry_days = 30;
    $settings->save();

    $user = UserFactory::new()->create([
        'must_change_password' => true,
        'password_changed_at' => null,
    ]);

    $status = EvaluatePasswordPolicyAction::run($user);

    expect($status->mustChangePassword)->toBeTrue()
        ->and($status->passwordExpired)->toBeFalse()
        ->and($status->reason)->toBe('forced');
});

it('updates the password, clears force change, and records the change time', function (): void {
    $user = UserFactory::new()->create([
        'password' => Hash::make('old-password'),
        'must_change_password' => true,
        'password_changed_at' => null,
    ]);

    UpdatePasswordAction::run(
        $user,
        new PasswordChangeData(
            password: 'new-password',
            passwordConfirmation: 'new-password',
            currentPassword: 'old-password',
        ),
    );

    $user->refresh();

    expect(Hash::check('new-password', $user->password))->toBeTrue()
        ->and((bool) $user->getAttribute('must_change_password'))->toBeFalse()
        ->and($user->getAttribute('password_changed_at'))->not->toBeNull();
});

it('blocks recently used passwords when password history is enabled', function (): void {
    $settings = PasswordPolicySettings::instance();
    $settings->password_history_enabled = true;
    $settings->password_history_count = 5;
    $settings->save();

    $user = UserFactory::new()->create([
        'password' => Hash::make('old-password'),
    ]);

    UpdatePasswordAction::run(
        $user,
        new PasswordChangeData(
            password: 'new-password',
            passwordConfirmation: 'new-password',
            currentPassword: 'old-password',
        ),
    );

    $user->refresh();

    expect(fn (): mixed => UpdatePasswordAction::run(
        $user,
        new PasswordChangeData(
            password: 'old-password',
            passwordConfirmation: 'old-password',
            currentPassword: 'new-password',
        ),
    ))->toThrow(ValidationException::class);
});

it('reports password security posture and panel-aware forced change urls', function (): void {
    $settings = PasswordPolicySettings::instance();
    $settings->force_change_enabled = true;
    $settings->password_expiry_enabled = true;
    $settings->password_history_enabled = true;
    $settings->compromised_password_checks_enabled = true;
    $settings->save();

    $report = BuildPasswordSecurityPostureReportAction::run(['admin']);

    expect($report->forceChangeEnabled)->toBeTrue()
        ->and($report->passwordExpiryEnabled)->toBeTrue()
        ->and($report->passwordHistoryEnabled)->toBeTrue()
        ->and($report->compromisedPasswordChecksEnabled)->toBeTrue()
        ->and($report->userColumnsInstalled)->toBeTrue()
        ->and($report->historyTableInstalled)->toBeTrue()
        ->and($report->forcedChangeUrls['admin'] ?? null)->toContain('/admin/password-policy/change-password')
        ->and((new PasswordPolicyHealthCheck)->securityPosture(['admin'])->forcedChangeUrls)
        ->toHaveKey('admin');
});
