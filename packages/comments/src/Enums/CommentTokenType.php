<?php

declare(strict_types=1);

namespace Capell\Comments\Enums;

enum CommentTokenType: string
{
    case VerifyEmail = 'verify_email';
    case NotificationPreference = 'notification_preference';
}
