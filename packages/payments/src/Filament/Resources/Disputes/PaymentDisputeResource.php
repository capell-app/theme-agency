<?php

declare(strict_types=1);

namespace Capell\Payments\Filament\Resources\Disputes;

use BackedEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Payments\Actions\FormatPaymentMoneyAction;
use Capell\Payments\Filament\Resources\Disputes\Pages\ListPaymentDisputes;
use Capell\Payments\Models\PaymentDispute;
use Capell\Payments\Providers\PaymentsServiceProvider;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

final class PaymentDisputeResource extends Resource
{
    protected static ?string $slug = 'payments/disputes/payment-disputes';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static ?string $recordTitleAttribute = 'provider_dispute_id';

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('provider_dispute_id')->label(__('capell-payments::generic.fields.provider_dispute_id'))->searchable()->copyable(),
            TextColumn::make('status')->label(__('capell-payments::generic.fields.status'))->badge()->sortable(),
            TextColumn::make('amount')
                ->label(__('capell-payments::generic.fields.amount'))
                ->formatStateUsing(fn (?int $state, PaymentDispute $record): ?string => FormatPaymentMoneyAction::run($state, $record->currency))
                ->sortable(),
            TextColumn::make('currency')->label(__('capell-payments::generic.fields.currency'))->searchable(),
            TextColumn::make('reason')->label(__('capell-payments::generic.fields.reason'))->searchable()->toggleable(),
            TextColumn::make('provider_payment_intent_id')->label(__('capell-payments::generic.fields.provider_payment_intent_id'))->searchable()->toggleable(),
            TextColumn::make('provider_charge_id')->label(__('capell-payments::generic.fields.provider_charge_id'))->searchable()->toggleable(),
            TextColumn::make('evidence_due_at')->label(__('capell-payments::generic.fields.evidence_due_at'))->dateTime()->sortable(),
            TextColumn::make('created_at')->label(__('capell-payments::generic.fields.created_at'))->dateTime()->sortable(),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return PaymentDispute::class;
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('capell-admin::navigation.group_monitoring');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-payments::generic.resources.disputes');
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
            'index' => ListPaymentDisputes::route('/'),
        ];
    }
}
