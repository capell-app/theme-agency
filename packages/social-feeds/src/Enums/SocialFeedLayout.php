<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Enums;

enum SocialFeedLayout: string
{
    case List = 'list';
    case Slideshow = 'slideshow';
    case Carousel = 'carousel';
    case Paginated = 'paginated';

    public static function fromState(mixed $value): self
    {
        if (is_string($value)) {
            return self::tryFrom($value) ?? self::Carousel;
        }

        return self::Carousel;
    }
}
