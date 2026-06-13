<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->addIfMissing('bookings.travel_provider', 'osrm');
        $this->addIfMissing('bookings.travel_cache_bucket_minutes', 30);
        $this->addIfMissing('bookings.travel_observation_retention_days', 365);
        $this->addIfMissing('bookings.lunch_duration_minutes', 45);
        $this->addIfMissing('bookings.lunch_window_starts_at', '12:00');
        $this->addIfMissing('bookings.lunch_window_ends_at', '14:00');
        $this->addIfMissing('bookings.hold_expiry_minutes', 30);
        $this->addIfMissing('bookings.fuel_rate_pence_per_mile', 500);
        $this->addIfMissing('bookings.reminder_offsets_days', [7, 1]);
        $this->addIfMissing('bookings.review_offsets_days', [1, 2, 10]);
        $this->addIfMissing('bookings.message_log_retention_days', 730);
        $this->addIfMissing('bookings.default_group_capacity', 8);
        $this->addIfMissing('bookings.ai_suggestions_enabled', false);
        $this->addIfMissing('bookings.facebook_events_enabled', false);
        $this->addIfMissing('bookings.sms_enabled', false);
        $this->addIfMissing('bookings.whatsapp_enabled', false);
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('bookings.travel_provider');
        $this->migrator->deleteIfExists('bookings.travel_cache_bucket_minutes');
        $this->migrator->deleteIfExists('bookings.travel_observation_retention_days');
        $this->migrator->deleteIfExists('bookings.lunch_duration_minutes');
        $this->migrator->deleteIfExists('bookings.lunch_window_starts_at');
        $this->migrator->deleteIfExists('bookings.lunch_window_ends_at');
        $this->migrator->deleteIfExists('bookings.hold_expiry_minutes');
        $this->migrator->deleteIfExists('bookings.fuel_rate_pence_per_mile');
        $this->migrator->deleteIfExists('bookings.reminder_offsets_days');
        $this->migrator->deleteIfExists('bookings.review_offsets_days');
        $this->migrator->deleteIfExists('bookings.message_log_retention_days');
        $this->migrator->deleteIfExists('bookings.default_group_capacity');
        $this->migrator->deleteIfExists('bookings.ai_suggestions_enabled');
        $this->migrator->deleteIfExists('bookings.facebook_events_enabled');
        $this->migrator->deleteIfExists('bookings.sms_enabled');
        $this->migrator->deleteIfExists('bookings.whatsapp_enabled');
    }

    private function addIfMissing(string $key, mixed $value): void
    {
        if ($this->migrator->exists($key)) {
            return;
        }

        $this->migrator->add($key, $value);
    }
};
