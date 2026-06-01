<?php

declare(strict_types=1);

namespace Capell\Payments\Filament\Resources\Customers;

use BackedEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Payments\Filament\Resources\Customers\Pages\ListPaymentCustomers;
use Capell\Payments\Models\PaymentCustomer;
use Capell\Payments\Providers\PaymentsServiceProvider;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

final class PaymentCustomerResource extends Resource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $recordTitleAttribute = 'provider_customer_id';

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('provider_customer_id')->label(__('capell-payments::generic.fields.provider_customer_id'))->searchable()->copyable(),
            TextColumn::make('provider')->label(__('capell-payments::generic.fields.provider'))->badge()->sortable(),
            TextColumn::make('email')->label(__('capell-payments::generic.fields.email'))->searchable(),
            TextColumn::make('name')->label(__('capell-payments::generic.fields.name'))->searchable(),
            TextColumn::make('site_id')->label(__('capell-payments::generic.fields.site_id'))->numeric()->sortable()->toggleable(),
            TextColumn::make('billable_type')->label(__('capell-payments::generic.fields.billable_type'))->searchable()->toggleable(),
            TextColumn::make('billable_id')->label(__('capell-payments::generic.fields.billable_id'))->searchable()->toggleable(),
            TextColumn::make('created_at')->label(__('capell-payments::generic.fields.created_at'))->dateTime()->sortable(),
        ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return PaymentCustomer::class;
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('capell-admin::navigation.group_monitoring');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-payments::generic.resources.customers');
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
            'index' => ListPaymentCustomers::route('/'),
        ];
    }
}
