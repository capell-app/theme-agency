<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Tests\Fixtures;

use Capell\Newsletter\Data\ConsentEvidenceData;
use Capell\Newsletter\Data\SubscriberData;
use Capell\Newsletter\Enums\ConsentEventType;

final class SubscribeUserAutomationActionProbe
{
    public ?SubscriberData $subscriberData = null;

    public ?ConsentEvidenceData $evidenceData = null;

    public ?ConsentEventType $eventType = null;

    /** @var array<int, int|string> */
    public array $tagIds = [];

    public bool $replaceTags = false;
}
