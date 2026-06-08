<?php

declare(strict_types=1);

namespace Capell\Newsletter\Filament\Resources\NewsletterSends;

use BackedEnum;
use Capell\Admin\Filament\Components\Forms\SiteSelect;
use Capell\Core\Facades\CapellCore;
use Capell\Newsletter\Enums\NewsletterSendStatus;
use Capell\Newsletter\Filament\Concerns\ScopesNewsletterResourcesToAssignedSites;
use Capell\Newsletter\Filament\Resources\NewsletterSends\Pages\CreateNewsletterSend;
use Capell\Newsletter\Filament\Resources\NewsletterSends\Pages\EditNewsletterSend;
use Capell\Newsletter\Filament\Resources\NewsletterSends\Pages\ListNewsletterSends;
use Capell\Newsletter\Models\NewsletterSend;
use Capell\Newsletter\Models\ProviderAudience;
use Capell\Newsletter\Models\Segment;
use Capell\Newsletter\Providers\NewsletterServiceProvider;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

class NewsletterSendResource extends Resource
{
    use ScopesNewsletterResourcesToAssignedSites;

    protected static ?string $slug = 'newsletter/newsletter-sends';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperAirplane;

    protected static ?string $recordTitleAttribute = 'name';

    #[Override]
    public static function form(Schema $configurator): Schema
    {
        return $configurator->components([
            SiteSelect::make('site_id')->required(),
            TextInput::make('name')->label(__('capell-newsletter::form.name'))->required()->maxLength(255),
            TextInput::make('subject')->label(__('capell-newsletter::form.subject'))->required()->maxLength(255),
            TextInput::make('preheader')->label(__('capell-newsletter::form.preheader'))->maxLength(255),
            Select::make('status')
                ->label(__('capell-newsletter::form.status'))
                ->options(self::sendStatusOptions())
                ->required(),
            DateTimePicker::make('scheduled_at')->label(__('capell-newsletter::form.scheduled_at')),
            Select::make('newsletter_segment_id')
                ->label(__('capell-newsletter::form.segment'))
                ->options(fn (): array => Segment::query()->pluck('name', 'id')->all())
                ->searchable(),
            Select::make('newsletter_provider_audience_id')
                ->label(__('capell-newsletter::form.provider_audience'))
                ->options(fn (): array => ProviderAudience::query()->pluck('name', 'id')->all())
                ->searchable(),
            TextInput::make('utm_source')->label(__('capell-newsletter::form.utm_source'))->maxLength(255),
            TextInput::make('utm_medium')->label(__('capell-newsletter::form.utm_medium'))->maxLength(255),
            TextInput::make('utm_campaign')->label(__('capell-newsletter::form.utm_campaign'))->maxLength(255),
            TextInput::make('utm_term')->label(__('capell-newsletter::form.utm_term'))->maxLength(255),
            TextInput::make('utm_content')->label(__('capell-newsletter::form.utm_content'))->maxLength(255),
            TextInput::make('utm_id')->label(__('capell-newsletter::form.utm_id'))->maxLength(255),
            KeyValue::make('metadata')->label(__('capell-newsletter::form.metadata')),
        ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label(__('capell-newsletter::form.name'))->searchable()->sortable(),
                TextColumn::make('subject')->label(__('capell-newsletter::form.subject'))->searchable(),
                TextColumn::make('status')->label(__('capell-newsletter::table.status'))->badge()->sortable(),
                TextColumn::make('scheduled_at')->label(__('capell-newsletter::table.scheduled_at'))->dateTime()->sortable(),
                TextColumn::make('sent_at')->label(__('capell-newsletter::table.sent_at'))->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('site_id')
                    ->label(__('capell-admin::form.site'))
                    ->searchable()
                    ->relationship(
                        name: 'site',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query): Builder => self::applyNewsletterSiteScope($query, 'id'),
                    ),
                SelectFilter::make('status')
                    ->label(__('capell-newsletter::form.status'))
                    ->options(self::sendStatusOptions()),
            ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return NewsletterSend::class;
    }

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return self::applyNewsletterSiteScope(parent::getEloquentQuery());
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return null;
    }

    #[Override]
    public static function getNavigationParentItem(): string
    {
        return __('capell-admin::navigation.marketing_studio');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-newsletter::navigation.newsletter_sends');
    }

    #[Override]
    public static function shouldRegisterNavigation(): bool
    {
        return CapellCore::isPackageInstalled(NewsletterServiceProvider::$packageName);
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListNewsletterSends::route('/'),
            'create' => CreateNewsletterSend::route('/create'),
            'edit' => EditNewsletterSend::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function sendStatusOptions(): array
    {
        return collect(NewsletterSendStatus::cases())
            ->mapWithKeys(static fn (NewsletterSendStatus $status): array => [$status->value => $status->getLabel()])
            ->all();
    }
}
