<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Database\Factories;

use Capell\CustomerPortal\Enums\SupportRequestPriority;
use Capell\CustomerPortal\Enums\SupportRequestStatus;
use Capell\CustomerPortal\Models\PortalAccount;
use Capell\CustomerPortal\Models\PortalSupportRequest;
use Illuminate\Database\Eloquent\Factories\Factory;
use RuntimeException;

/**
 * @extends Factory<PortalSupportRequest>
 */
final class PortalSupportRequestFactory extends Factory
{
    protected $model = PortalSupportRequest::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'portal_account_id' => fn (): int => $this->createPortalAccount()->getKey(),
            'site_id' => fn (array $attributes): int => $this->resolvePortalAccount($attributes)->site_id,
            'status' => SupportRequestStatus::Open,
            'priority' => SupportRequestPriority::Normal,
            'subject' => $this->faker->sentence(4),
            'message' => $this->faker->paragraph(),
            'requester_email' => fn (array $attributes): ?string => $this->resolvePortalAccount($attributes)->email,
            'source' => 'customer-portal',
            'external_reference' => null,
            'context' => [
                'path' => '/portal/support',
            ],
            'submitted_at' => now(),
            'resolved_at' => null,
            'closed_at' => null,
        ];
    }

    public function forPortalAccount(PortalAccount $portalAccount): static
    {
        return $this->state(fn (): array => [
            'site_id' => $portalAccount->site_id,
            'portal_account_id' => $portalAccount->getKey(),
            'requester_email' => $portalAccount->email,
        ]);
    }

    public function resolved(): static
    {
        return $this->state(fn (): array => [
            'status' => SupportRequestStatus::Resolved,
            'resolved_at' => now(),
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (): array => [
            'status' => SupportRequestStatus::Closed,
            'resolved_at' => now(),
            'closed_at' => now(),
        ]);
    }

    private function createPortalAccount(): PortalAccount
    {
        return PortalAccount::factory()->create();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function resolvePortalAccount(array $attributes): PortalAccount
    {
        $portalAccountId = $attributes['portal_account_id'] ?? null;

        if (! is_int($portalAccountId)) {
            throw new RuntimeException('Portal support request factories require an integer portal_account_id.');
        }

        return PortalAccount::query()->findOrFail($portalAccountId);
    }
}
