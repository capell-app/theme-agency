<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Data;

use Capell\AgentBridge\Contracts\CapellAgentBridgeCapabilityAction;
use Capell\AgentBridge\Enums\CapabilityRiskEnum;
use Capell\AgentBridge\Enums\CapabilityServerEnum;
use Spatie\LaravelData\Data;

final class CapabilityData extends Data
{
    /**
     * @param  class-string<CapellAgentBridgeCapabilityAction>  $actionClass
     * @param  class-string<Data>|null  $inputDataClass
     * @param  class-string<Data>|null  $outputDataClass
     * @param  array<string, mixed>|null  $inputSchema
     * @param  array<string, mixed>|null  $outputSchema
     */
    public function __construct(
        public readonly string $key,
        public readonly string $name,
        public readonly string $description,
        public readonly string $scope,
        public readonly CapabilityServerEnum $server,
        public readonly CapabilityRiskEnum $risk,
        public readonly string $actionClass,
        public readonly ?string $requiredPackage = null,
        public readonly ?string $policyAbility = null,
        public readonly ?string $inputDataClass = null,
        public readonly ?string $outputDataClass = null,
        public readonly ?array $inputSchema = null,
        public readonly ?array $outputSchema = null,
        public readonly bool $supportsPreview = true,
        public readonly bool $requiresConfirmation = true,
        public readonly ?string $auditEvent = null,
        public readonly bool $public = false,
    ) {}

    public function needsConfirmation(): bool
    {
        return $this->requiresConfirmation || $this->risk->requiresConfirmation();
    }

    /**
     * @return array{
     *     key: string,
     *     name: string,
     *     description: string,
     *     scope: string,
     *     server: string,
     *     risk: string,
     *     requiredPackage: string|null,
     *     policyAbility: string|null,
     *     inputDataClass: string|null,
     *     outputDataClass: string|null,
     *     inputSchema: array<string, mixed>|null,
     *     outputSchema: array<string, mixed>|null,
     *     supportsPreview: bool,
     *     requiresConfirmation: bool,
     *     auditEvent: string|null,
     *     public: bool
     * }
     */
    public function toPayload(): array
    {
        return [
            'key' => $this->key,
            'name' => $this->name,
            'description' => $this->description,
            'scope' => $this->scope,
            'server' => $this->server->value,
            'risk' => $this->risk->value,
            'requiredPackage' => $this->requiredPackage,
            'policyAbility' => $this->policyAbility,
            'inputDataClass' => $this->inputDataClass,
            'outputDataClass' => $this->outputDataClass,
            'inputSchema' => $this->inputSchema,
            'outputSchema' => $this->outputSchema,
            'supportsPreview' => $this->supportsPreview,
            'requiresConfirmation' => $this->requiresConfirmation,
            'auditEvent' => $this->auditEvent,
            'public' => $this->public,
        ];
    }

    /**
     * Anonymous-safe projection for the tokenless public capability surface.
     *
     * Deliberately omits scope, policy ability, package name, class-strings,
     * audit event, and input/output schemas: the public-output-safety rule
     * forbids exposing any of that to anonymous callers. Use {@see toPayload()}
     * for the authenticated admin surface.
     *
     * @return array{key: string, name: string, description: string, risk: string}
     */
    public function publicPayload(): array
    {
        return [
            'key' => $this->key,
            'name' => $this->name,
            'description' => $this->description,
            'risk' => $this->risk->value,
        ];
    }
}
