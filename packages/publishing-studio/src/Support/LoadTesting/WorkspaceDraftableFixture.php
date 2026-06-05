<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Support\LoadTesting;

use Capell\PublishingStudio\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Minimal draftable model for the package load-test command.
 *
 * @property int $id
 * @property int $workspace_id
 * @property string $uuid
 * @property string $name
 */
class WorkspaceDraftableFixture extends Model
{
    use BelongsToWorkspace;
    use HasFactory;

    public $timestamps = true;

    protected $table = 'workspace_draftable_fixtures';

    protected $fillable = ['uuid', 'name', 'workspace_id', 'shadowed_by_workspace_id'];
}
