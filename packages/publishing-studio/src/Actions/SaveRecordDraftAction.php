<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Actions;

use Capell\PublishingStudio\Data\SavedRecordDraftData;
use Capell\PublishingStudio\Models\Workspace;
use Capell\PublishingStudio\WorkspaceRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as AuthenticatedUser;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

final class SaveRecordDraftAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(
        Model $record,
        array $data,
        AuthenticatedUser $user,
        ?Workspace $workspace = null,
        ?callable $saveRelationships = null,
    ): SavedRecordDraftData {
        $workspace ??= CreateRecordDraftWorkspaceAction::run($record, $user);

        $draft = DB::transaction(function () use ($record, $data, $workspace, $saveRelationships): Model {
            $draft = $this->resolveDraft($record, $workspace);
            $draft->fill($data);
            $draft->save();

            if ($saveRelationships !== null) {
                $saveRelationships($draft, $workspace);
            }

            if ((int) $record->getAttribute('workspace_id') === 0) {
                DB::table($record->getTable())
                    ->where($record->getKeyName(), $record->getKey())
                    ->where('workspace_id', 0)
                    ->update(['shadowed_by_workspace_id' => $workspace->id]);
            }

            return $draft;
        });

        $workspace->updated_by = $user->getKey();
        $workspace->save();

        return new SavedRecordDraftData($draft, $workspace);
    }

    private function resolveDraft(Model $record, Workspace $workspace): Model
    {
        if ((int) $record->getAttribute('workspace_id') === $workspace->id) {
            return $record;
        }

        $uuid = $record->getAttribute('uuid');

        if (is_string($uuid) && $uuid !== '') {
            $existing = $record::query()
                ->withoutGlobalScopes()
                ->where('workspace_id', $workspace->id)
                ->where('uuid', $uuid)
                ->first();

            if ($existing instanceof Model) {
                return $existing;
            }
        }

        $draft = WorkspaceRegistry::get($record::class)->cloneInto($record, $workspace);

        if (! $draft->exists) {
            $draft->save();
        }

        return $draft;
    }
}
