<?php

declare(strict_types=1);

namespace Capell\Bookings\Database\Factories;

use Capell\Bookings\Models\BookingService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingService>
 */
class BookingServiceFactory extends Factory
{
    protected $model = BookingService::class;

    public function definition(): array
    {
        return [
            'name' => 'Initial consultation',
            'description' => 'A first appointment to understand needs and next steps.',
            'duration_minutes' => 45,
            'buffer_before_minutes' => 0,
            'buffer_after_minutes' => 15,
            'lead_time_minutes' => 120,
            'active' => true,
            'confirmation_required' => true,
        ];
    }
}
