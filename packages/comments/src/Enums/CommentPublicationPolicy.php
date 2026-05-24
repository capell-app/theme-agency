<?php

declare(strict_types=1);

namespace Capell\Comments\Enums;

use Filament\Support\Contracts\HasLabel;

enum CommentPublicationPolicy: string implements HasLabel
{
    case RequireApproval = 'require_approval';
    case AutoPublish = 'auto_publish';
    case Disabled = 'disabled';

    public function getLabel(): string
    {
        return (string) __('capell-comments::generic.publication_policy.' . $this->value);
    }
}
