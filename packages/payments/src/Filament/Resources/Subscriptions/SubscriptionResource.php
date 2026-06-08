<?php

declare(strict_types=1);

namespace Capell\Payments\Filament\Resources\Subscriptions;

use BackedEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Payments\Filament\Resources\Subscriptions\Pages\ListSubscriptions;
use Capell\Payments\Models\Subscription;
use Capell\Payments\Providers\PaymentsServiceProvider;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

final class SubscriptionResource extends Resource
{
    protected static ?string $slug = 'payments/subscriptions';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPathRoundedSquare;

    protected static ?string $recordTitleAttribute = 'provider_subscription_id';

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('provider_subscription_id')->label(__('capell-payments::generic.fields.provider_subscription_id'))->searchable()->copyable(),
            TextColumn::make('status')->label(__('capell-payments::generic.fields.status'))->badge()->sortable(),
            TextColumn::make('provider_customer_id')->label(__('capell-payments::generic.fields.provider_customer_id'))->searchable()->toggleable(),
            TextColumn::make('provider_session_id')->label(__('capell-payments::generic.fields.provider_session_id'))->searchable()->toggleable(),
            TextColumn::make('current_period_ends_at')->label(__('capell-payments::generic.fields.current_period_ends_at'))->dateTime()->sortable(),
            TextColumn::make('trial_ends_at')->label(__('capell-payments::generic.fields.trial_ends_at'))->dateTime()->sortable()->toggleable(),
            TextColumn::make('canceled_at')->label(__('capell-payments::generic.fields.canceled_at'))->dateTime()->sortable()->toggleable(),
            TextColumn::make('created_at')->label(__('capell-payments::generic.fields.created_at'))->dateTime()->sortable(),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return Subscription::class;
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('capell-admin::navigation.group_monitoring');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-payments::generic.resources.subscriptions');
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
            'index' => ListSubscriptions::route('/'),
        ];
    }
}
