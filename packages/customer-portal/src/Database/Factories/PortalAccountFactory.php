<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Database\Factories;

use Capell\CustomerPortal\Enums\PortalAccountStatus;
use Capell\CustomerPortal\Models\PortalAccount;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/**
 * @extends Factory<PortalAccount>
 */
final class PortalAccountFactory extends Factory
{
    protected $model = PortalAccount::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $email = $this->faker->unique()->safeEmail();

        return [
            'site_id' => fn (): int => $this->createSiteId(),
            'owner_type' => null,
            'owner_id' => null,
            'email' => $email,
            'display_name' => $this->faker->name(),
            'profile' => [
                'company' => $this->faker->company(),
            ],
            'preferences' => [
                'email_updates' => true,
                'event_reminders' => false,
            ],
            'status' => PortalAccountStatus::Active,
            'last_seen_at' => null,
        ];
    }

    public function forSite(int $siteId): static
    {
        return $this->state(fn (): array => [
            'site_id' => $siteId,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (): array => [
            'status' => PortalAccountStatus::Suspended,
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (): array => [
            'status' => PortalAccountStatus::Archived,
        ]);
    }

    private function createSiteId(): int
    {
        return (int) DB::table('sites')->insertGetId([]);
    }
}
