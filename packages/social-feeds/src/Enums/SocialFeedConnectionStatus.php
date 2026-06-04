<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Enums;

enum SocialFeedConnectionStatus: string
{
    case Connected = 'connected';
    case Disconnected = 'disconnected';
    case Error = 'error';
    case Pending = 'pending';
}
