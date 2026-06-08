<?php

declare(strict_types=1);

namespace Capell\FrontendAuthoring\Actions;

use Capell\Core\Facades\CapellCore;
use Capell\FrontendAuthoring\Data\EditableRegionPayloadData;
use Capell\FrontendAuthoring\Enums\EditableRegionField;
use Capell\FrontendAuthoring\Enums\EditableRegionSaveStatus;
use Capell\PublishingStudio\Actions\CopyOnWriteAction;
use Capell\PublishingStudio\Actions\GenerateWorkspacePreviewUrlAction;
use Capell\PublishingStudio\Models\Workspace;
use Capell\PublishingStudio\WorkspaceContext;
use Capell\PublishingStudio\WorkspaceRegistry;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsObject;
use RuntimeException;

/**
 * @method static array{cleared: int, urls: list<string>, status: string, redirect_url: string|null} run(EditableRegionPayloadData $payload, string $value, AuthenticatableContract $user)
 */
class UpdateEditableRegionAction
{
    use AsObject;

    private const string PUBLISHING_STUDIO_PACKAGE = 'capell-app/publishing-studio';

    /**
     * @return array{cleared: int, urls: list<string>, status: string, redirect_url: string|null}
     */
    public function handle(EditableRegionPayloadData $payload, string $value, AuthenticatableContract $user): array
    {
        $payload = ValidateEditableRegionPayloadAction::run($payload, $user);

        /** @var class-string<Model> $modelClass */
        $modelClass = $payload->model;
        abort_unless(is_subclass_of($modelClass, Model::class), 403);

        $record = $modelClass::query()->findOrFail($payload->recordKey);
        throw_unless($record instanceof Model, RuntimeException::class, 'Editable region record must resolve to an Eloquent model.');

        $urls = CollectAffectedCachedUrlsAction::run($record);

        if ($this->shouldRequireApproval($record)) {
            return $this->saveForApproval($record, $payload, $value, $urls);
        }

        $this->applyValue($record, $payload, $this->valueForStorage($payload, $value));
        $record->save();

        $cleared = ClearAffectedCachedUrlsAction::run($record, $urls, $payload->currentUrl);

        return [
            'cleared' => $cleared,
            'urls' => $urls,
            'status' => EditableRegionSaveStatus::Published->value,
            'redirect_url' => null,
        ];
    }

    /**
     * @param  list<string>  $urls
     * @return array{cleared: int, urls: list<string>, status: string, redirect_url: string|null}
     */
    private function saveForApproval(Model $record, EditableRegionPayloadData $payload, string $value, array $urls): array
    {
        abort_unless(CapellCore::isPackageInstalled(self::PUBLISHING_STUDIO_PACKAGE), 409);

        $workspaceClass = Workspace::class;
        $workspaceContextClass = WorkspaceContext::class;
        $workspaceRegistryClass = WorkspaceRegistry::class;
        $previewUrlActionClass = GenerateWorkspacePreviewUrlAction::class;
        $copyOnWriteActionClass = CopyOnWriteAction::class;

        abort_unless(
            class_exists($workspaceClass)
            && class_exists($workspaceContextClass)
            && class_exists($workspaceRegistryClass)
            && class_exists($previewUrlActionClass)
            && $workspaceRegistryClass::isRegistered($record::class),
            409,
        );

        $workspace = $workspaceClass::query()->create([
            'name' => config('capell-frontend-authoring.workflow.workspace_name', 'Inline editor changes'),
            'slug' => 'inline-editor-' . now()->format('YmdHis') . '-' . strtolower(Str::random(6)),
        ]);

        $workspaceContextClass::runWith($workspace, function () use ($copyOnWriteActionClass, $record, $payload, $value, $workspace): void {
            $this->applyValue($record, $payload, $this->valueForStorage($payload, $value));

            if ((int) ($record->getAttribute('workspace_id') ?? 0) === 0 && class_exists($copyOnWriteActionClass)) {
                (new $copyOnWriteActionClass)->cloneForEdit($record, $workspace);

                return;
            }

            $record->save();
        });

        $user = Auth::user();

        if ($user instanceof User) {
            $workspace->submitForApproval($user, 'Submitted from frontend inline editor.');
        }

        $path = parse_url($payload->currentUrl, PHP_URL_PATH);
        $freshWorkspace = $workspace->fresh();

        throw_unless($freshWorkspace instanceof Workspace, RuntimeException::class, 'Inline editing workspace must exist before generating a preview URL.');

        $previewUrl = (new $previewUrlActionClass)->handle($freshWorkspace, is_string($path) ? $path : '/');

        return [
            'cleared' => 0,
            'urls' => $urls,
            'status' => EditableRegionSaveStatus::PendingApproval->value,
            'redirect_url' => $previewUrl,
        ];
    }

    private function shouldRequireApproval(Model $record): bool
    {
        if (config('capell-frontend-authoring.workflow.require_approval') !== true) {
            return false;
        }

        if (! Auth::user() instanceof AuthenticatableContract) {
            return false;
        }

        if (! $record::query() instanceof Builder) {
            return false;
        }

        return array_key_exists('workspace_id', $record->getAttributes())
            || in_array('workspace_id', $record->getFillable(), true);
    }

    private function applyValue(Model $record, EditableRegionPayloadData $payload, string $value): void
    {
        $field = $payload->fieldKind();

        if ($field->isDirectAttribute()) {
            $record->setAttribute($payload->field, $value);

            return;
        }

        if ($field->isMetaAttribute()) {
            $meta = (array) $record->getAttribute('meta');
            Arr::set($meta, substr($payload->field, 5), $value);
            $record->setAttribute('meta', $meta);

            return;
        }

        abort(403);
    }

    private function valueForStorage(EditableRegionPayloadData $payload, string $value): string
    {
        if ($payload->fieldKind() !== EditableRegionField::Content) {
            return $value;
        }

        // Frontend Authoring is an admin-only signed editor surface. Keep content
        // HTML exactly as submitted; public output policy stays with Capell's
        // normal rendering/theme layer.
        return $value;
    }
}
