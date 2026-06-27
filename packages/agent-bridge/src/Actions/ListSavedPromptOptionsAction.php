<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Actions;

use Capell\AgentBridge\Models\CapellAgentBridgeSavedPrompt;
use Illuminate\Contracts\Auth\Authenticatable;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * Saved-prompt options (name keyed by stringified id) for the given user's
 * prompt-builder selector.
 *
 * @method static array<int|string, string> run(Authenticatable $user)
 */
class ListSavedPromptOptionsAction
{
    use AsObject;

    /**
     * @return array<int|string, string>
     */
    public function handle(Authenticatable $user): array
    {
        return CapellAgentBridgeSavedPrompt::query()
            ->forUser($user)
            ->orderBy('name')
            ->get()
            ->mapWithKeys(static fn (CapellAgentBridgeSavedPrompt $prompt): array => [(string) $prompt->id => $prompt->name])
            ->all();
    }
}
