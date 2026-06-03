<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Policies;

final class CampaignCtaWidgetPolicy extends AbstractCampaignStudioResourcePolicy
{
    protected static function subject(): string
    {
        return 'CampaignCtaWidget';
    }
}
