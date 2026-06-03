<?php

declare(strict_types=1);

return [
    'conversion_cookie' => 'capell_campaign_visit',
    'utm_keys' => [
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
    ],
    'tables' => [
        'groups' => 'campaign_groups',
        'landing_pages' => 'campaign_landing_pages',
        'cta_widgets' => 'campaign_cta_widgets',
        'conversion_goals' => 'campaign_conversion_goals',
        'conversions' => 'campaign_conversions',
    ],
    'layout_presets' => [
        'enabled' => true,
    ],
];
