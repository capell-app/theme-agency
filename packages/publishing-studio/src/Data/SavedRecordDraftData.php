<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Data;

use Capell\PublishingStudio\Models\Workspace;
use Illuminate\Database\Eloquent\Model;

final readonly class SavedRecordDraftData
{
    public function __construct(
        public Model $record,
        public Workspace $workspace,
    ) {}
}
