<?php

declare(strict_types=1);

namespace Capell\AiCreator\Enums;

enum AiCreatorRecommendationLevel: string
{
    case Required = 'required';
    case Recommended = 'recommended';
    case Optional = 'optional';
}
