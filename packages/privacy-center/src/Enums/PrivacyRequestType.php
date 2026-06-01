<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Enums;

use Filament\Support\Contracts\HasLabel;

enum PrivacyRequestType: string implements HasLabel
{
    case Access = 'access';
    case Export = 'export';
    case Delete = 'delete';
    case Rectify = 'rectify';
    case Restrict = 'restrict';
    case Object = 'object';

    public function getLabel(): string
    {
        return __('capell-privacy-center::privacy.privacy_request_types.' . $this->value);
    }
}
