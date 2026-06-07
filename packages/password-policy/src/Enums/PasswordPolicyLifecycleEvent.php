<?php

declare(strict_types=1);

namespace Capell\PasswordPolicy\Enums;

enum PasswordPolicyLifecycleEvent: string
{
    case PasswordChanged = 'password-policy.password-changed';

    case PasswordExpired = 'password-policy.password-expired';

    case UserMarkedForChange = 'password-policy.user-marked-for-change';
}
