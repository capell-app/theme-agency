# Fortify Integration

Password Policy does not register Laravel Fortify actions for the host app. Host
applications own registration and password-reset workflows, so they should opt
into the package rule where they already validate passwords.

Use `Capell\PasswordPolicy\Rules\PasswordPolicyRule` in Fortify's
`CreateNewUser` and `ResetUserPassword` actions:

```php
use Capell\PasswordPolicy\Rules\PasswordPolicyRule;
use Illuminate\Support\Facades\Validator;

Validator::make($input, [
    'password' => [
        'required',
        'confirmed',
        PasswordPolicyRule::forUser(),
    ],
])->validate();
```

For password resets where the user is already known, pass the user so reuse
history is checked against the current hash and stored history rows:

```php
use Capell\PasswordPolicy\Rules\PasswordPolicyRule;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

/** @var Authenticatable&Model $user */
Validator::make($input, [
    'password' => [
        'required',
        'confirmed',
        PasswordPolicyRule::forUser($user),
    ],
])->validate();
```

The rule reads the package settings by default, including minimum length,
mixed-case, number, symbol, compromised-password, and history controls. Use
`withCompromisedPasswordCheck(false)` only when a host flow deliberately needs
to bypass the HIBP check while still enforcing the rest of the policy.
