<?php

declare(strict_types=1);

namespace Capell\AccessGate\Enums;

use Filament\Support\Contracts\HasLabel;

enum TokenPolicy: string implements HasLabel
{
    case SingleActiveBrowserToken = 'single_active_browser_token';
    case MultipleBrowserTokens = 'multiple_browser_tokens';

    public function getLabel(): string
    {
        return __(sprintf('capell-access-gate::filament.token_policy.%s', $this->value));
    }
}
