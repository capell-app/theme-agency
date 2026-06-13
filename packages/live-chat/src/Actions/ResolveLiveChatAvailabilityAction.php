<?php

declare(strict_types=1);

namespace Capell\LiveChat\Actions;

use Capell\LiveChat\Data\LiveChatAvailabilityData;
use Capell\LiveChat\Models\LiveChatAvailabilityException;
use Capell\LiveChat\Models\LiveChatAvailabilityWindow;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

final class ResolveLiveChatAvailabilityAction
{
    use AsAction;

    public function handle(int $siteId, ?CarbonImmutable $at = null, ?string $timezone = null): LiveChatAvailabilityData
    {
        $resolvedTimezone = $timezone
            ?? $this->configString('capell-live-chat.business_hours.timezone', $this->configString('capell-live-chat.default_timezone', 'UTC'));
        $checkedAt = ($at ?? CarbonImmutable::now($resolvedTimezone))->setTimezone($resolvedTimezone);
        $offlineMessage = $this->configString(
            'capell-live-chat.widget.offline_message',
            __('capell-live-chat::generic.widget.offline_message'),
        );

        $exception = LiveChatAvailabilityException::query()
            ->where('site_id', $siteId)
            ->whereDate('date', $checkedAt->toDateString())
            ->first();

        if ($exception instanceof LiveChatAvailabilityException) {
            return new LiveChatAvailabilityData(
                available: $this->isExceptionAvailable($exception, $checkedAt),
                timezone: $exception->timezone,
                message: is_string($exception->message) && trim($exception->message) !== '' ? $exception->message : $offlineMessage,
                checkedAt: $checkedAt,
            );
        }

        $window = LiveChatAvailabilityWindow::query()
            ->where('site_id', $siteId)
            ->where('day_of_week', $checkedAt->dayOfWeekIso)
            ->where('is_active', true)
            ->get()
            ->first(fn (LiveChatAvailabilityWindow $candidate): bool => $this->isWithinWindow($candidate->opens_at, $candidate->closes_at, $checkedAt));

        if ($window instanceof LiveChatAvailabilityWindow) {
            return new LiveChatAvailabilityData(
                available: true,
                timezone: $window->timezone,
                message: __('capell-live-chat::generic.availability.available'),
                checkedAt: $checkedAt,
            );
        }

        if ($this->withinConfiguredHours($checkedAt)) {
            return new LiveChatAvailabilityData(
                available: true,
                timezone: $resolvedTimezone,
                message: __('capell-live-chat::generic.availability.available'),
                checkedAt: $checkedAt,
            );
        }

        return new LiveChatAvailabilityData(
            available: false,
            timezone: $resolvedTimezone,
            message: $offlineMessage,
            checkedAt: $checkedAt,
        );
    }

    private function isExceptionAvailable(LiveChatAvailabilityException $exception, CarbonImmutable $checkedAt): bool
    {
        if (! $exception->is_available) {
            return false;
        }

        if (! is_string($exception->opens_at) || ! is_string($exception->closes_at)) {
            return true;
        }

        return $this->isWithinWindow($exception->opens_at, $exception->closes_at, $checkedAt);
    }

    private function isWithinWindow(?string $opensAt, ?string $closesAt, CarbonImmutable $checkedAt): bool
    {
        if (! is_string($opensAt) || ! is_string($closesAt)) {
            return false;
        }

        $time = $checkedAt->format('H:i');

        return $time >= substr($opensAt, 0, 5) && $time < substr($closesAt, 0, 5);
    }

    private function withinConfiguredHours(CarbonImmutable $checkedAt): bool
    {
        $windows = config('capell-live-chat.business_hours.weekly', []);

        if (! is_array($windows)) {
            return false;
        }

        foreach ($windows as $window) {
            if (! is_array($window)) {
                continue;
            }

            $day = $window['day'] ?? null;

            if (! is_int($day) && ! (is_string($day) && ctype_digit($day))) {
                continue;
            }

            if ((int) $day !== $checkedAt->dayOfWeekIso) {
                continue;
            }

            $opensAt = $window['opens_at'] ?? null;
            $closesAt = $window['closes_at'] ?? null;

            if (! is_string($opensAt) || ! is_string($closesAt)) {
                continue;
            }

            if ($this->isWithinWindow($opensAt, $closesAt, $checkedAt)) {
                return true;
            }
        }

        return false;
    }

    private function configString(string $key, string $fallback): string
    {
        $value = config($key);

        return is_string($value) && $value !== '' ? $value : $fallback;
    }
}
