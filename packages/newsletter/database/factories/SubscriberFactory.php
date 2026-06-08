<?php

declare(strict_types=1);

namespace Capell\Newsletter\Database\Factories;

use Capell\Core\Models\Site;
use Capell\Newsletter\Enums\SubscriberStatus;
use Capell\Newsletter\Models\Subscriber;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscriber>
 */
class SubscriberFactory extends Factory
{
    protected $model = Subscriber::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $email = $this->faker->unique()->safeEmail();

        return [
            'site_id' => Site::factory(),
            'email_hash' => Subscriber::emailHash($email),
            'email' => $email,
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'profile' => [],
            'status' => SubscriberStatus::Subscribed,
            'source_form_id' => null,
            'source_form_handle' => null,
            'pending_at' => null,
            'subscribed_at' => now(),
            'confirmed_at' => now(),
            'unsubscribed_at' => null,
            'suppressed_at' => null,
            'bounced_at' => null,
            'complained_at' => null,
        ];
    }
}
