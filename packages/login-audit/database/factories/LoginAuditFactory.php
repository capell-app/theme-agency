<?php

declare(strict_types=1);

namespace Capell\LoginAudit\Database\Factories;

use Capell\LoginAudit\Models\LoginAudit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<LoginAudit>
 */
class LoginAuditFactory extends Factory
{
    protected $model = LoginAudit::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        /** @var class-string<Model> $userModel */
        $userModel = config('auth.providers.users.model');
        $userFactory = [$userModel, 'factory'];

        return [
            'authenticatable_type' => (new $userModel)->getMorphClass(),
            'authenticatable_id' => is_callable($userFactory) ? $userFactory() : 1,
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'login_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'login_successful' => $this->faker->boolean(90),
            'logout_at' => $this->faker->optional(0.7)->dateTimeBetween('-1 month', 'now'),
            'cleared_by_user' => $this->faker->boolean(10),
            'location' => $this->faker->boolean() ? [
                'country' => $this->faker->country(),
                'city' => $this->faker->city(),
            ] : null,
        ];
    }
}
