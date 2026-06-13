<?php

declare(strict_types=1);

namespace Capell\Bookings\Settings;

use Capell\Bookings\Filament\Settings\BookingsSettingsSchema;
use Capell\Core\Contracts\SettingsContract;
use Capell\Core\Contracts\SettingsSchemaContract;
use Override;
use Spatie\LaravelSettings\Settings;

class BookingsSettings extends Settings implements SettingsContract, SettingsSchemaContract
{
    public string $travel_provider;

    public int $travel_cache_bucket_minutes;

    public int $travel_observation_retention_days;

    public int $lunch_duration_minutes;

    public string $lunch_window_starts_at;

    public string $lunch_window_ends_at;

    public int $hold_expiry_minutes;

    public int $fuel_rate_pence_per_mile;

    /** @var list<int> */
    public array $reminder_offsets_days;

    /** @var list<int> */
    public array $review_offsets_days;

    public int $message_log_retention_days;

    public int $default_group_capacity;

    public bool $ai_suggestions_enabled;

    public bool $facebook_events_enabled;

    public bool $sms_enabled;

    public bool $whatsapp_enabled;

    public static function group(): string
    {
        return 'bookings';
    }

    public static function schema(): string
    {
        return BookingsSettingsSchema::class;
    }

    public static function instance(): self
    {
        return resolve(self::class);
    }

    #[Override]
    public function refresh(): self
    {
        parent::refresh();

        return $this;
    }
}
