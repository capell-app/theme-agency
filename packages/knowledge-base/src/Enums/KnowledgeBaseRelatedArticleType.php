<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Enums;

use Filament\Support\Contracts\HasLabel;

enum KnowledgeBaseRelatedArticleType: string implements HasLabel
{
    case Related = 'related';
    case Prerequisite = 'prerequisite';
    case NextStep = 'next_step';

    public function getLabel(): string
    {
        return (string) __('capell-knowledge-base::generic.related_article_type.' . $this->value);
    }
}
