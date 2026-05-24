<?php

declare(strict_types=1);

namespace Capell\Comments\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CommentStatus: string implements HasColor, HasLabel
{
    case PendingEmailVerification = 'pending_email_verification';
    case PendingApproval = 'pending_approval';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Spam = 'spam';
    case Archived = 'archived';

    public function getLabel(): string
    {
        return (string) __('capell-comments::generic.comment_status.' . $this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PendingEmailVerification, self::PendingApproval => 'warning',
            self::Approved => 'success',
            self::Rejected, self::Archived => 'gray',
            self::Spam => 'danger',
        };
    }

    public function isPubliclyVisible(): bool
    {
        return $this === self::Approved;
    }
}
