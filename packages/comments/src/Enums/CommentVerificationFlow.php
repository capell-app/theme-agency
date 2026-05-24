<?php

declare(strict_types=1);

namespace Capell\Comments\Enums;

use Filament\Support\Contracts\HasLabel;

enum CommentVerificationFlow: string implements HasLabel
{
    case VerifyThenModerate = 'verify_then_moderate';
    case ModerateThenVerify = 'moderate_then_verify';

    public function getLabel(): string
    {
        return (string) __('capell-comments::generic.verification_flow.' . $this->value);
    }
}
