<?php

declare(strict_types=1);

namespace Capell\LiveChat\Data;

use Capell\LiveChat\Models\LiveChatKnowledgeDocument;
use Spatie\LaravelData\Data;

final class LiveChatKnowledgeSearchResultData extends Data
{
    public function __construct(
        public readonly LiveChatKnowledgeDocument $document,
        public readonly int $score,
    ) {}
}
