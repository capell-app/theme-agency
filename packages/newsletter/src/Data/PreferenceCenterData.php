<?php

declare(strict_types=1);

namespace Capell\Newsletter\Data;

use Capell\Newsletter\Enums\SubscriberStatus;
use Spatie\LaravelData\Data;

class PreferenceCenterData extends Data
{
    /**
     * @param  list<PreferenceCenterSegmentData>  $segments
     */
    public function __construct(
        public int $subscriberId,
        public int $siteId,
        public string $email,
        public SubscriberStatus $status,
        public bool $canReceiveNewsletter,
        public array $segments,
    ) {}
}
