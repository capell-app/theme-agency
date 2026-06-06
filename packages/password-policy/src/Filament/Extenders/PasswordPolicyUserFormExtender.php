<?php

declare(strict_types=1);

namespace Capell\PasswordPolicy\Filament\Extenders;

use Capell\Admin\Contracts\Extenders\UserFormExtender;
use Capell\Core\Support\Database\RuntimeSchemaState;
use Capell\PasswordPolicy\Actions\RecordPasswordHistoryAction;
use Capell\PasswordPolicy\Actions\ValidatePasswordChangeAction;
use Capell\PasswordPolicy\Data\PasswordChangeData;
use Capell\PasswordPolicy\Support\PasswordPolicySettingsResolver;
use Illuminate\Database\Eloquent\Model;

class PasswordPolicyUserFormExtender implements UserFormExtender
{
    /**
     * @var array<string, string>
     */
    private array $pendingPasswordHistoryHashes = [];

    public function mutateDataBeforeCreate(array $data): array
    {
        if ($this->hasNoPassword($data)) {
            return $data;
        }

        $settings = resolve(PasswordPolicySettingsResolver::class)->settings();

        ValidatePasswordChangeAction::run(
            null,
            $this->passwordChangeData($data),
            $settings->compromisedPasswordChecksEnabled,
        );

        return $data;
    }

    public function afterCreate(Model $record): void
    {
        $this->persistPasswordPolicyAttributes($record);
    }

    public function mutateDataBeforeSave(Model $record, array $data): array
    {
        if ($this->hasNoPassword($data)) {
            return $data;
        }

        $settings = resolve(PasswordPolicySettingsResolver::class)->settings();

        ValidatePasswordChangeAction::run(
            $record,
            $this->passwordChangeData($data),
            $settings->compromisedPasswordChecksEnabled,
        );

        $this->pendingPasswordHistoryHashes[$this->recordHistoryKey($record)] = (string) $record->getAttribute('password');

        return $data;
    }

    public function afterSave(Model $record): void
    {
        if (! $record->wasChanged('password')) {
            return;
        }

        $recordHistoryKey = $this->recordHistoryKey($record);
        $passwordHash = $this->pendingPasswordHistoryHashes[$recordHistoryKey] ?? null;
        unset($this->pendingPasswordHistoryHashes[$recordHistoryKey]);

        if (is_string($passwordHash) && $passwordHash !== '') {
            RecordPasswordHistoryAction::run($record, $passwordHash);
        }

        $this->persistPasswordPolicyAttributes($record);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function hasNoPassword(array $data): bool
    {
        return ! array_key_exists('password', $data) || blank($data['password']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function passwordChangeData(array $data): PasswordChangeData
    {
        $password = (string) $data['password'];

        return new PasswordChangeData(
            password: $password,
            passwordConfirmation: $password,
            requireCurrentPassword: false,
        );
    }

    private function persistPasswordPolicyAttributes(Model $record): void
    {
        $values = [];
        $schema = resolve(RuntimeSchemaState::class);

        if ($schema->hasColumn($record->getTable(), 'password_changed_at')) {
            $values['password_changed_at'] = now();
        }

        if ($schema->hasColumn($record->getTable(), 'must_change_password')) {
            $values['must_change_password'] = false;
        }

        if ($values === []) {
            return;
        }

        $record->forceFill($values)->save();
    }

    private function recordHistoryKey(Model $record): string
    {
        return $record->getTable() . ':' . $record->getKey();
    }
}
