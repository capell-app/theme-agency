<?php

declare(strict_types=1);

namespace Capell\AiCreator\Enums;

enum AiCreatorSessionStatus: string
{
    case Draft = 'draft';
    case Previewed = 'previewed';
    case Applied = 'applied';
}
