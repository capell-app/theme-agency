<?php

declare(strict_types=1);

use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\PasswordPolicy\Health\PasswordPolicyHealthCheck;
use Capell\PasswordPolicy\Settings\PasswordPolicySettings;
use Illuminate\Support\Facades\Schema;

uses()->group('password-policy');

it('runs real diagnostics returning check results', function (): void {
    $results = PasswordPolicyHealthCheck::runDiagnostics();

    expect($results)->toHaveCount(3)
        ->and($results->every(static fn (mixed $result): bool => $result instanceof DoctorCheckResultData))->toBeTrue();
});

it('passes when enabled controls have their backing persistence installed', function (): void {
    $settings = PasswordPolicySettings::instance();
    $settings->force_change_enabled = true;
    $settings->password_expiry_enabled = true;
    $settings->password_history_enabled = true;
    $settings->save();

    expect(PasswordPolicyHealthCheck::passed())->toBeTrue();
});

it('passes when controls are disabled even if persistence is missing', function (): void {
    Schema::drop('password_policy_password_histories');

    $settings = PasswordPolicySettings::instance();
    $settings->force_change_enabled = false;
    $settings->password_expiry_enabled = false;
    $settings->password_history_enabled = false;
    $settings->save();

    expect(PasswordPolicyHealthCheck::passed())->toBeTrue();
});

it('fails the history table check when history blocking is enabled but the table is missing', function (): void {
    $settings = PasswordPolicySettings::instance();
    $settings->password_history_enabled = true;
    $settings->save();

    Schema::drop('password_policy_password_histories');

    $check = new PasswordPolicyHealthCheck;

    expect($check->passwordHistoryTableCheck()->passed)->toBeFalse()
        ->and(PasswordPolicyHealthCheck::passed())->toBeFalse();
});

it('fails the forced change column check when forced change is enabled but the column is missing', function (): void {
    $settings = PasswordPolicySettings::instance();
    $settings->force_change_enabled = true;
    $settings->save();

    Schema::table('users', function ($table): void {
        $table->dropColumn('must_change_password');
    });

    $check = new PasswordPolicyHealthCheck;

    expect($check->forcedChangeColumnCheck()->passed)->toBeFalse()
        ->and(PasswordPolicyHealthCheck::passed())->toBeFalse();
});

it('fails the expiry column check when expiry is enabled but the column is missing', function (): void {
    $settings = PasswordPolicySettings::instance();
    $settings->password_expiry_enabled = true;
    $settings->save();

    Schema::table('users', function ($table): void {
        $table->dropColumn('password_changed_at');
    });

    $check = new PasswordPolicyHealthCheck;

    expect($check->passwordExpiryColumnCheck()->passed)->toBeFalse()
        ->and(PasswordPolicyHealthCheck::passed())->toBeFalse();
});
