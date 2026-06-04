<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Enums;

enum SocialAuthStrategy: string
{
    case None = 'none';
    case ApiKey = 'api_key';
    case OAuth2 = 'oauth2';
}
