<?php

declare(strict_types=1);

namespace Capell\AccessGate\Listeners;

use Capell\AccessGate\Contracts\AdminNotificationRecipientResolver;
use Capell\AccessGate\Filament\Resources\Registrations\RegistrationResource;
use Capell\AccessGate\Models\Registration;
use Filament\Notifications\Actions\Action as FilamentNotificationAction;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Collection;
use Throwable;

final class NotifyAdminsOfAccessRequest
{
    public function __construct(private readonly Container $container) {}

    public function handle(Registration $registration): void
    {
        if (! class_exists(FilamentNotification::class)) {
            return;
        }

        $recipients = $this->resolveRecipients();

        if ($recipients->isEmpty()) {
            return;
        }

        $areaKey = $registration->area?->key ?? (string) $registration->access_area_id;

        $notification = FilamentNotification::make()
            ->title(__('capell-access-gate::notifications.new_request.title'))
            ->body(__('capell-access-gate::notifications.new_request.body', [
                'email' => $registration->email,
                'area' => $areaKey,
            ]))
            ->icon('heroicon-o-envelope')
            ->warning();

        if (class_exists(RegistrationResource::class)) {
            $notification->actions([
                FilamentNotificationAction::make('review')
                    ->label(__('capell-access-gate::notifications.new_request.review'))
                    ->url(RegistrationResource::getUrl('index'))
                    ->markAsRead(),
            ]);
        }

        $notification->sendToDatabase($recipients);
    }

    /**
     * @return Collection<int, mixed>
     */
    private function resolveRecipients(): Collection
    {
        /** @var array<int, class-string<AdminNotificationRecipientResolver>> $resolvers */
        $resolvers = (array) config('access-gate.notifications.new_request_recipients', []);

        return collect($resolvers)
            ->flatMap(function (string $resolverClass): Collection {
                if (! class_exists($resolverClass) || ! is_subclass_of($resolverClass, AdminNotificationRecipientResolver::class)) {
                    return collect();
                }

                try {
                    return $this->container->make($resolverClass)->resolve();
                } catch (Throwable $exception) {
                    report($exception);

                    return collect();
                }
            })
            ->filter()
            ->unique(fn (object $recipient): string => $recipient::class . ':' . (method_exists($recipient, 'getKey') ? (string) $recipient->getKey() : spl_object_hash($recipient)))
            ->values();
    }
}
