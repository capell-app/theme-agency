<?php

declare(strict_types=1);

namespace Capell\Payments\Filament\Resources\WebhookEvents;

use BackedEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Payments\Filament\Resources\WebhookEvents\Pages\ListPaymentWebhookEvents;
use Capell\Payments\Models\PaymentWebhookEvent;
use Capell\Payments\Providers\PaymentsServiceProvider;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

final class PaymentWebhookEventResource extends Resource
{
    protected static ?string $slug = 'payments/webhook-events/payment-webhook-events';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBolt;

    protected static ?string $recordTitleAttribute = 'provider_event_id';

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('provider_event_id')->label(__('capell-payments::generic.fields.provider_event_id'))->searchable()->copyable(),
            TextColumn::make('event_type')->label(__('capell-payments::generic.fields.event_type'))->searchable()->sortable(),
            TextColumn::make('status')->label(__('capell-payments::generic.fields.status'))->badge()->sortable(),
            TextColumn::make('livemode')->label(__('capell-payments::generic.fields.livemode'))->badge()->sortable(),
            TextColumn::make('received_at')->label(__('capell-payments::generic.fields.received_at'))->dateTime()->sortable(),
            TextColumn::make('processed_at')->label(__('capell-payments::generic.fields.processed_at'))->dateTime()->sortable()->toggleable(),
            TextColumn::make('failed_at')->label(__('capell-payments::generic.fields.failed_at'))->dateTime()->sortable()->toggleable(),
            TextColumn::make('error')->label(__('capell-payments::generic.fields.error'))->limit(80)->toggleable(isToggledHiddenByDefault: true),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return PaymentWebhookEvent::class;
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('capell-admin::navigation.group_monitoring');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-payments::generic.resources.webhook_events');
    }

    #[Override]
    public static function shouldRegisterNavigation(): bool
    {
        return CapellCore::isPackageInstalled(PaymentsServiceProvider::$packageName);
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListPaymentWebhookEvents::route('/'),
        ];
    }
}
