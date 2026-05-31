<?php

declare(strict_types=1);

namespace Capell\Bookings\Models;

use Capell\Bookings\Database\Factories\BookingStaffMemberFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;

class BookingStaffMember extends Model
{
    /** @use HasFactory<BookingStaffMemberFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'booking_staff_members';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'active',
        'display_name',
        'email',
        'meta',
        'phone',
        'profile_url',
        'settings',
        'timezone',
        'title',
    ];

    protected static string $factory = BookingStaffMemberFactory::class;

    /**
     * @return HasMany<BookingAvailabilityWindow, $this>
     */
    public function availabilityWindows(): HasMany
    {
        return $this->hasMany(BookingAvailabilityWindow::class, 'staff_member_id');
    }

    /**
     * @return HasMany<AppointmentRequest, $this>
     */
    public function appointmentRequests(): HasMany
    {
        return $this->hasMany(AppointmentRequest::class, 'staff_member_id');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    protected function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'meta' => 'json',
            'settings' => 'json',
        ];
    }
}
