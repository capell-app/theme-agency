<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Actions;

use Capell\AgentBridge\Models\CapellAgentBridgeToken;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static CapellAgentBridgeToken run(CapellAgentBridgeToken $token)
 */
final class RevokeAgentBridgeTokenAction
{
    use AsAction;

    public function handle(CapellAgentBridgeToken $token): CapellAgentBridgeToken
    {
        $token->forceFill([
            'is_enabled' => false,
            'revoked_at' => $token->revoked_at ?? now(),
        ])->save();

        return $token;
    }
}
