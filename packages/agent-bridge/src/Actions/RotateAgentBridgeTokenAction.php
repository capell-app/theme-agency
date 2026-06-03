<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Actions;

use Capell\AgentBridge\Models\CapellAgentBridgeToken;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static array{token: CapellAgentBridgeToken, plainTextToken: string} run(CapellAgentBridgeToken $token)
 */
final class RotateAgentBridgeTokenAction
{
    use AsAction;

    /**
     * @return array{token: CapellAgentBridgeToken, plainTextToken: string}
     */
    public function handle(CapellAgentBridgeToken $token): array
    {
        $plainTextToken = CapellAgentBridgeToken::generatePlainTextToken();

        $token->forceFill([
            'token_hash' => CapellAgentBridgeToken::hashPlainTextToken($plainTextToken),
            'is_enabled' => true,
            'revoked_at' => null,
            'rotated_at' => now(),
        ])->save();

        return [
            'token' => $token,
            'plainTextToken' => $plainTextToken,
        ];
    }
}
