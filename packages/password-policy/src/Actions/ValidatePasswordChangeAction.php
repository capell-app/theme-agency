<?php

declare(strict_types=1);

namespace Capell\PasswordPolicy\Actions;

use Capell\PasswordPolicy\Data\PasswordChangeData;
use Capell\PasswordPolicy\Rules\PasswordPolicyRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsObject;

class ValidatePasswordChangeAction
{
    use AsObject;

    public function handle(?Model $user, PasswordChangeData $input, bool $checkCompromisedPasswords): void
    {
        if (
            $user instanceof Model
            && $input->requireCurrentPassword
            && ! Hash::check((string) $input->currentPassword, (string) $user->getAttribute('password'))
        ) {
            throw ValidationException::withMessages([
                'current_password' => __('capell-password-policy::validation.current_password'),
            ]);
        }

        Validator::make([
            'password' => $input->password,
            'password_confirmation' => $input->passwordConfirmation,
        ], [
            'password' => [
                'required',
                'confirmed',
                PasswordPolicyRule::forUser($user)->withCompromisedPasswordCheck($checkCompromisedPasswords),
            ],
        ], [
            'password.required' => __('capell-password-policy::validation.password_required'),
            'password.confirmed' => __('capell-password-policy::validation.password_confirmed'),
        ])->validate();
    }
}
