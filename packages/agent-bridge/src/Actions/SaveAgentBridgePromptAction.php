<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Actions;

use Capell\AgentBridge\Data\AgentBridgePromptData;
use Capell\AgentBridge\Models\CapellAgentBridgeSavedPrompt;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Lorisleiva\Actions\Concerns\AsAction;

final class SaveAgentBridgePromptAction
{
    use AsAction;

    public function handle(
        Authenticatable&Model $user,
        string $name,
        ?string $description,
        AgentBridgePromptData $data,
        ?CapellAgentBridgeSavedPrompt $savedPrompt = null,
    ): CapellAgentBridgeSavedPrompt {
        if ($savedPrompt instanceof CapellAgentBridgeSavedPrompt && ! $savedPrompt->belongsToUser($user)) {
            throw new AuthorizationException(__('capell-agent-bridge::admin.saved_prompt_forbidden'));
        }

        $prompt = BuildAgentBridgePromptAction::run($data);
        $record = $savedPrompt ?? new CapellAgentBridgeSavedPrompt;

        $record->forceFill([
            'name' => $name,
            'description' => $description,
            'form_state' => $data->toFormState(),
            'prompt' => $prompt,
        ]);

        $record->user()->associate($user);
        $record->save();

        return $record;
    }
}
