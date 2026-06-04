<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Enums;

enum SocialFeedItemType: string
{
    case Image = 'image';
    case Video = 'video';
    case Text = 'text';
    case Link = 'link';
}
