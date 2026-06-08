<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Filament\Resources\SentEmails;

use BackedEnum;
use Capell\Core\Facades\CapellCore;
use Capell\EmailStudio\Filament\Resources\SentEmails\Pages\ListSentEmails;
use Capell\EmailStudio\Filament\Resources\SentEmails\Pages\ViewSentEmail;
use Capell\EmailStudio\Models\SentEmail;
use Capell\EmailStudio\Providers\EmailStudioServiceProvider;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Override;

final class SentEmailResource extends Resource
{
    protected static ?string $slug = 'email-studio/sent-emails';

    protected static ?string $model = SentEmail::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Envelope;

    protected static ?string $recordTitleAttribute = 'subject';

    protected static ?int $navigationSort = 45;

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withCount('clickRows'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('subject')
                    ->label(__('capell-email-studio::mail_tracker.fields.subject'))
                    ->searchable()
                    ->limit(60),
                TextColumn::make('sender_email')
                    ->label(__('capell-email-studio::mail_tracker.fields.sender'))
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('recipient_email')
                    ->label(__('capell-email-studio::mail_tracker.fields.recipient'))
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('opens')
                    ->label(__('capell-email-studio::mail_tracker.fields.opens'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('clicks')
                    ->label(__('capell-email-studio::mail_tracker.fields.clicks'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('click_rows_count')
                    ->label(__('capell-email-studio::mail_tracker.fields.tracked_urls'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('content')
                    ->label(__('capell-email-studio::mail_tracker.fields.content_stored'))
                    ->boolean()
                    ->state(fn (SentEmail $record): bool => $record->content !== null && $record->content !== ''),
                TextColumn::make('opened_at')
                    ->label(__('capell-email-studio::mail_tracker.fields.opened_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('clicked_at')
                    ->label(__('capell-email-studio::mail_tracker.fields.clicked_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label(__('capell-email-studio::mail_tracker.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Filter::make('created_at')
                    ->label(__('capell-email-studio::mail_tracker.filters.created_at'))
                    ->schema([
                        DatePicker::make('from')
                            ->label(__('capell-email-studio::mail_tracker.filters.from')),
                        DatePicker::make('until')
                            ->label(__('capell-email-studio::mail_tracker.filters.until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $from = $data['from'] ?? null;
                        $until = $data['until'] ?? null;

                        return $query
                            ->when(is_string($from) && $from !== '', fn (Builder $query): Builder => $query->whereDate('created_at', '>=', $from))
                            ->when(is_string($until) && $until !== '', fn (Builder $query): Builder => $query->whereDate('created_at', '<=', $until));
                    }),
                TernaryFilter::make('opened')
                    ->label(__('capell-email-studio::mail_tracker.filters.opened'))
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereNotNull('opened_at'),
                        false: fn (Builder $query): Builder => $query->whereNull('opened_at'),
                        blank: fn (Builder $query): Builder => $query,
                    ),
                TernaryFilter::make('clicked')
                    ->label(__('capell-email-studio::mail_tracker.filters.clicked'))
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereNotNull('clicked_at'),
                        false: fn (Builder $query): Builder => $query->whereNull('clicked_at'),
                        blank: fn (Builder $query): Builder => $query,
                    ),
                TernaryFilter::make('content')
                    ->label(__('capell-email-studio::mail_tracker.filters.content_stored'))
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereNotNull('content')->where('content', '!=', ''),
                        false: fn (Builder $query): Builder => $query->where(function (Builder $query): void {
                            $query->whereNull('content')->orWhere('content', '');
                        }),
                        blank: fn (Builder $query): Builder => $query,
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->emptyStateHeading(__('capell-email-studio::mail_tracker.empty.heading'))
            ->emptyStateDescription(__('capell-email-studio::mail_tracker.empty.description'))
            ->emptyStateIcon('heroicon-o-envelope');
    }

    #[Override]
    public static function canCreate(): bool
    {
        return false;
    }

    #[Override]
    public static function canEdit(Model $record): bool
    {
        return false;
    }

    #[Override]
    public static function canDelete(Model $record): bool
    {
        return false;
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return (string) __('capell-email-studio::mail_tracker.navigation.sent_emails');
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return (string) __('capell-email-studio::generic.email_studio');
    }

    #[Override]
    public static function getPluralModelLabel(): string
    {
        return (string) __('capell-email-studio::mail_tracker.resources.sent_emails');
    }

    #[Override]
    public static function getModelLabel(): string
    {
        return (string) __('capell-email-studio::mail_tracker.resources.sent_email');
    }

    #[Override]
    public static function shouldRegisterNavigation(): bool
    {
        return CapellCore::isPackageInstalled(EmailStudioServiceProvider::$packageName);
    }

    /**
     * @return array<string, mixed>
     */
    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListSentEmails::route('/'),
            'view' => ViewSentEmail::route('/{record}'),
        ];
    }
}
