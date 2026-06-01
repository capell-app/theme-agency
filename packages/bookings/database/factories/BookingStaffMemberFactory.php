<?php

declare(strict_types=1);

namespace Capell\Bookings\Database\Factories;

use Capell\Bookings\Models\BookingStaffMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingStaffMember>
 */
class BookingStaffMemberFactory extends Factory
{
    protected $model = BookingStaffMember::class;

    public function definition(): array
    {
        return [
            'display_name' => 'Avery Morgan',
            'title' => 'Consultant',
            'email' => 'avery@example.com',
            'timezone' => 'Europe/London',
            'active' => true,
        ];
    }
}
