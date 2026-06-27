<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Actions;

use Capell\AgentBridge\Models\CapellAgentBridgeSavedPrompt;
use Illuminate\Contracts\Auth\Authenticatable;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * Loads a saved prompt by id, scoped to the owning user so one user cannot
 * load another's prompt. Returns null when not found or not owned.
 *
 * @method static ?CapellAgentBridgeSavedPrompt run(Authenticatable $user, int|string $savedPromptId)
 */
class FindSavedPromptForUserAction
{
    use AsObject;

    public function handle(Authenticatable $user, int|string $savedPromptId): ?CapellAgentBridgeSavedPrompt
    {
        return CapellAgentBridgeSavedPrompt::query()
            ->forUser($user)
            ->find($savedPromptId);
    }
}
