<?php

declare(strict_types=1);

namespace Capell\Bookings\Database\Factories;

use Capell\Bookings\Enums\BookingLocationTypeEnum;
use Capell\Bookings\Models\BookingLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingLocation>
 */
class BookingLocationFactory extends Factory
{
    protected $model = BookingLocation::class;

    public function definition(): array
    {
        return [
            'name' => 'Main office',
            'type' => BookingLocationTypeEnum::Physical->value,
            'line1' => '1 High Street',
            'city' => 'Leeds',
            'postal_code' => 'LS1 1AA',
            'country' => 'United Kingdom',
            'timezone' => 'Europe/London',
            'active' => true,
        ];
    }
}
