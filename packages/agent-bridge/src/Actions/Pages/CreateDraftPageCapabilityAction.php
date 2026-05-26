<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Actions\Pages;

use Capell\AgentBridge\Contracts\CapellAgentBridgeCapabilityAction;
use Capell\AgentBridge\Data\CapabilityInvocationData;
use Capell\AgentBridge\Data\CapabilityResultData;
use Capell\Core\Models\Page;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

final class CreateDraftPageCapabilityAction implements CapellAgentBridgeCapabilityAction
{
    public function preview(CapabilityInvocationData $invocation): CapabilityResultData
    {
        $payload = $this->validatedPayload($invocation->payload);
        $this->authorizeSite($invocation->user, (int) $payload['site_id']);

        return new CapabilityResultData(
            ok: true,
            message: 'A new unpublished page record will be created.',
            data: [
                'page' => $payload,
            ],
        );
    }

    public function execute(CapabilityInvocationData $invocation): CapabilityResultData
    {
        $payload = $this->validatedPayload($invocation->payload);
        $this->authorizeSite($invocation->user, (int) $payload['site_id']);

        $pageClass = $this->pageClass();

        $page = $pageClass::query()->create([
            'name' => $payload['name'],
            'site_id' => $payload['site_id'],
            'blueprint_id' => $payload['blueprint_id'],
            'layout_id' => $payload['layout_id'],
            'parent_id' => $payload['parent_id'] ?? null,
            'meta' => $payload['meta'] ?? null,
            'admin' => $payload['admin'] ?? null,
            'visible_from' => $payload['visible_from'] ?? null,
            'visible_until' => $payload['visible_until'] ?? null,
        ]);

        return new CapabilityResultData(
            ok: true,
            message: 'Draft page has been created.',
            data: [
                'page_id' => (int) $page->getKey(),
                'name' => $page->getAttribute('name'),
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function validatedPayload(array $payload): array
    {
        validator($payload, [
            'name' => ['required', 'string', 'max:255'],
            'site_id' => ['required', 'integer'],
            'blueprint_id' => ['required', 'integer'],
            'layout_id' => ['required', 'integer'],
            'parent_id' => ['nullable', 'integer'],
            'meta' => ['nullable', 'array'],
            'admin' => ['nullable', 'array'],
            'visible_from' => ['nullable', 'date'],
            'visible_until' => ['nullable', 'date'],
        ])->validate();

        return $payload;
    }

    private function authorizeSite(?Authenticatable $user, int $siteId): void
    {
        if (! $user instanceof Authenticatable) {
            throw ValidationException::withMessages([
                'site_id' => __('capell-agent-bridge::admin.capability_site_requires_user'),
            ]);
        }

        if ($this->isGlobalAdmin($user)) {
            return;
        }

        $assignedSiteIds = $user->getAssignedSiteIds();
        if (! is_iterable($assignedSiteIds)) {
            throw ValidationException::withMessages([
                'site_id' => __('capell-agent-bridge::admin.capability_site_forbidden'),
            ]);
        }

        foreach ($assignedSiteIds as $assignedSiteId) {
            if ((int) $assignedSiteId === $siteId) {
                return;
            }
        }

        throw ValidationException::withMessages([
            'site_id' => __('capell-agent-bridge::admin.capability_site_forbidden'),
        ]);
    }

    private function isGlobalAdmin(Authenticatable $user): bool
    {
        if (is_callable([$user, 'isGlobalAdmin']) && $user->isGlobalAdmin() === true) {
            return true;
        }

        return is_callable([$user, 'hasRole'])
            && $user->hasRole(config('capell.roles.super_admin', 'super_admin')) === true;
    }

    /** @return class-string<Model> */
    private function pageClass(): string
    {
        return Page::class;
    }
}
