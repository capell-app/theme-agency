<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Actions;

use Capell\PublishingStudio\Enums\WorkspaceKindEnum;
use Capell\PublishingStudio\Enums\WorkspaceStatusEnum;
use Capell\PublishingStudio\Models\Workspace;
use Capell\PublishingStudio\WorkspaceRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as AuthenticatedUser;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

final class CreateRecordDraftWorkspaceAction
{
    use AsAction;

    public function handle(Model $record, AuthenticatedUser $user): Workspace
    {
        WorkspaceRegistry::get($record::class);

        $title = (string) ($record->getAttribute('name') ?? $record->getKey());
        $name = sprintf('Draft: %s · %s', $title, now()->format('Y-m-d H:i'));

        $workspace = new Workspace([
            'name' => $name,
            'slug' => Str::slug($name . ' ' . Str::random(6)),
            'status' => WorkspaceStatusEnum::Open->value,
            'kind' => WorkspaceKindEnum::SinglePageDraft->value,
        ]);

        $workspace->created_by = $user->getKey();
        $workspace->updated_by = $user->getKey();
        $workspace->save();

        return $workspace;
    }
}
