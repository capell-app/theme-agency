<?php

declare(strict_types=1);

namespace Capell\PasswordPolicy\Rules;

use Capell\Core\Support\Database\RuntimeSchemaState;
use Capell\PasswordPolicy\Data\ResolvedPasswordPolicySettingsData;
use Capell\PasswordPolicy\Support\PasswordPolicySettingsResolver;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

final class PasswordPolicyRule implements ValidationRule
{
    public function __construct(
        private readonly ?Model $user = null,
        private readonly ?ResolvedPasswordPolicySettingsData $settings = null,
        private readonly ?bool $checkCompromisedPasswords = null,
    ) {}

    public static function forUser(?Model $user = null): self
    {
        return new self(user: $user);
    }

    public function withCompromisedPasswordCheck(bool $checkCompromisedPasswords = true): self
    {
        return new self(
            user: $this->user,
            settings: $this->settings,
            checkCompromisedPasswords: $checkCompromisedPasswords,
        );
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail(__('capell-password-policy::validation.password_required'));

            return;
        }

        $settings = $this->resolvedSettings();
        $validator = Validator::make(
            [$attribute => $value],
            [$attribute => [$this->passwordRule($settings)]],
            $this->messages($attribute, $settings),
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $fail($message);
            }

            return;
        }

        if ($this->passwordHasBeenUsed($value, $settings)) {
            $fail(__('capell-password-policy::validation.password_reused'));
        }
    }

    private function resolvedSettings(): ResolvedPasswordPolicySettingsData
    {
        return $this->settings ?? resolve(PasswordPolicySettingsResolver::class)->settings();
    }

    private function passwordRule(ResolvedPasswordPolicySettingsData $settings): Password
    {
        $passwordRule = Password::min($settings->minimumPasswordLength);

        if ($settings->requireMixedCase) {
            $passwordRule->mixedCase();
        }

        if ($settings->requireNumbers) {
            $passwordRule->numbers();
        }

        if ($settings->requireSymbols) {
            $passwordRule->symbols();
        }

        if ($this->checkCompromisedPasswords ?? $settings->compromisedPasswordChecksEnabled) {
            $passwordRule->uncompromised();
        }

        return $passwordRule;
    }

    /**
     * @return array<string, string>
     */
    private function messages(string $attribute, ResolvedPasswordPolicySettingsData $settings): array
    {
        return [
            $attribute . '.min.string' => __('capell-password-policy::validation.password_min', [
                'min' => $settings->minimumPasswordLength,
            ]),
            $attribute . '.password.mixed' => __('capell-password-policy::validation.password_mixed'),
            $attribute . '.password.numbers' => __('capell-password-policy::validation.password_numbers'),
            $attribute . '.password.symbols' => __('capell-password-policy::validation.password_symbols'),
            $attribute . '.password.uncompromised' => __('capell-password-policy::validation.password_uncompromised'),
        ];
    }

    private function passwordHasBeenUsed(string $password, ResolvedPasswordPolicySettingsData $settings): bool
    {
        if (! $this->user instanceof Model || ! $settings->passwordHistoryEnabled) {
            return false;
        }

        $passwordHashes = collect([(string) $this->user->getAttribute('password')]);

        if (resolve(RuntimeSchemaState::class)->hasTable('password_policy_password_histories')) {
            $historyHashes = DB::table('password_policy_password_histories')
                ->where('user_id', $this->user->getKey())
                ->latest('id')
                ->limit(max(1, $settings->passwordHistoryCount))
                ->pluck('password');

            $passwordHashes = $passwordHashes->merge($historyHashes);
        }

        foreach ($passwordHashes as $passwordHash) {
            if (Hash::check($password, $passwordHash)) {
                return true;
            }
        }

        return false;
    }
}
