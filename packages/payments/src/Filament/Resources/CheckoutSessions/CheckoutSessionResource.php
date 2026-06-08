<?php

declare(strict_types=1);

namespace Capell\Payments\Filament\Resources\CheckoutSessions;

use BackedEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Payments\Actions\FormatPaymentMoneyAction;
use Capell\Payments\Filament\Resources\CheckoutSessions\Pages\ListCheckoutSessions;
use Capell\Payments\Models\CheckoutSession;
use Capell\Payments\Providers\PaymentsServiceProvider;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

final class CheckoutSessionResource extends Resource
{
    protected static ?string $slug = 'payments/checkout-sessions';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $recordTitleAttribute = 'provider_session_id';

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('provider_session_id')->label(__('capell-payments::generic.fields.provider_session_id'))->searchable()->copyable(),
            TextColumn::make('status')->label(__('capell-payments::generic.fields.status'))->badge()->sortable(),
            TextColumn::make('mode')->label(__('capell-payments::generic.fields.mode'))->badge()->sortable(),
            TextColumn::make('purpose')->label(__('capell-payments::generic.fields.purpose'))->badge()->sortable(),
            TextColumn::make('amount_total')
                ->label(__('capell-payments::generic.fields.amount_total'))
                ->formatStateUsing(fn (?int $state, CheckoutSession $record): ?string => FormatPaymentMoneyAction::run($state, $record->currency))
                ->sortable(),
            TextColumn::make('currency')->label(__('capell-payments::generic.fields.currency'))->searchable(),
            TextColumn::make('provider_customer_id')->label(__('capell-payments::generic.fields.provider_customer_id'))->searchable()->toggleable(),
            TextColumn::make('completed_at')->label(__('capell-payments::generic.fields.completed_at'))->dateTime()->sortable(),
            TextColumn::make('created_at')->label(__('capell-payments::generic.fields.created_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return CheckoutSession::class;
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('capell-admin::navigation.group_monitoring');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-payments::generic.resources.checkout_sessions');
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
            'index' => ListCheckoutSessions::route('/'),
        ];
    }
}
