<?php

declare(strict_types=1);

namespace Capell\Payments\Filament\Resources\PaymentIntents;

use BackedEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Payments\Actions\FormatPaymentMoneyAction;
use Capell\Payments\Actions\IssuePaymentRefundAction;
use Capell\Payments\Enums\PaymentIntentStatus;
use Capell\Payments\Filament\Resources\PaymentIntents\Pages\ListPaymentIntents;
use Capell\Payments\Models\PaymentIntent;
use Capell\Payments\Providers\PaymentsServiceProvider;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

final class PaymentIntentResource extends Resource
{
    protected static ?string $slug = 'payments/payment-intents';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $recordTitleAttribute = 'provider_payment_intent_id';

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('provider_payment_intent_id')->label(__('capell-payments::generic.fields.provider_payment_intent_id'))->searchable()->copyable(),
            TextColumn::make('status')->label(__('capell-payments::generic.fields.status'))->badge()->sortable(),
            TextColumn::make('amount')
                ->label(__('capell-payments::generic.fields.amount'))
                ->formatStateUsing(fn (?int $state, PaymentIntent $record): ?string => FormatPaymentMoneyAction::run($state, $record->currency))
                ->sortable(),
            TextColumn::make('currency')->label(__('capell-payments::generic.fields.currency'))->searchable(),
            TextColumn::make('provider_customer_id')->label(__('capell-payments::generic.fields.provider_customer_id'))->searchable()->toggleable(),
            TextColumn::make('provider_session_id')->label(__('capell-payments::generic.fields.provider_session_id'))->searchable()->toggleable(),
            TextColumn::make('created_at')->label(__('capell-payments::generic.fields.created_at'))->dateTime()->sortable(),
        ])->recordActions([
            self::issueRefundAction(),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return PaymentIntent::class;
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('capell-admin::navigation.group_monitoring');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-payments::generic.resources.payment_intents');
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
            'index' => ListPaymentIntents::route('/'),
        ];
    }

    private static function issueRefundAction(): Action
    {
        return Action::make('issue_refund')
            ->label(__('capell-payments::generic.actions.issue_refund'))
            ->icon('heroicon-o-arrow-uturn-left')
            ->color('warning')
            ->visible(fn (PaymentIntent $record): bool => $record->status === PaymentIntentStatus::Succeeded
                && is_string($record->provider_payment_intent_id)
                && $record->provider_payment_intent_id !== ''
                && $record->amount > 0)
            ->requiresConfirmation()
            ->schema([
                TextInput::make('amount')
                    ->label(__('capell-payments::generic.fields.amount'))
                    ->helperText(__('capell-payments::generic.help.refund_amount_minor_units'))
                    ->numeric()
                    ->minValue(1),
                Select::make('reason')
                    ->label(__('capell-payments::generic.fields.reason'))
                    ->options([
                        'duplicate' => __('capell-payments::generic.refund_reasons.duplicate'),
                        'fraudulent' => __('capell-payments::generic.refund_reasons.fraudulent'),
                        'requested_by_customer' => __('capell-payments::generic.refund_reasons.requested_by_customer'),
                    ]),
            ])
            ->action(function (PaymentIntent $record, array $data): void {
                $amount = is_numeric($data['amount'] ?? null) ? (int) $data['amount'] : null;
                $reason = is_string($data['reason'] ?? null) && $data['reason'] !== '' ? $data['reason'] : null;

                IssuePaymentRefundAction::run($record, $amount, $reason);

                Notification::make('capell_payments_refund_issued')
                    ->title(__('capell-payments::generic.notifications.refund_issued'))
                    ->success()
                    ->send();
            });
    }
}
