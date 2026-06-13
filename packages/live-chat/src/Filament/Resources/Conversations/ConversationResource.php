<?php

declare(strict_types=1);

namespace Capell\LiveChat\Filament\Resources\Conversations;

use BackedEnum;
use Capell\LiveChat\Actions\CloseLiveChatConversationAction;
use Capell\LiveChat\Actions\GenerateLiveChatSummaryAction;
use Capell\LiveChat\Actions\RequestLiveChatHandoffAction;
use Capell\LiveChat\Actions\SuggestLiveChatHumanReplyAction;
use Capell\LiveChat\Actions\SyncLiveChatConversationContactAction;
use Capell\LiveChat\Enums\ConversationStatus;
use Capell\LiveChat\Enums\LiveChatIntent;
use Capell\LiveChat\Enums\LiveChatPriority;
use Capell\LiveChat\Filament\Resources\Conversations\Pages\EditConversation;
use Capell\LiveChat\Filament\Resources\Conversations\Pages\ListConversations;
use Capell\LiveChat\Models\LiveChatConversation;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Override;

final class ConversationResource extends Resource
{
    protected static ?string $slug = 'live-chat/conversations';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $recordTitleAttribute = 'uuid';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('capell-live-chat::generic.resources.conversation'))
                ->schema([
                    TextInput::make('uuid')->label(__('capell-live-chat::generic.fields.uuid'))->disabled(),
                    Select::make('status')->label(__('capell-live-chat::generic.fields.status'))->options(self::statusOptions())->required(),
                    Select::make('intent')->label(__('capell-live-chat::generic.fields.intent'))->options(self::intentOptions()),
                    Select::make('priority')->label(__('capell-live-chat::generic.fields.priority'))->options(self::priorityOptions())->required(),
                    TextInput::make('assignment_queue')->label(__('capell-live-chat::generic.fields.route_to'))->maxLength(255),
                    TextInput::make('visitor_name')->label(__('capell-live-chat::generic.fields.name'))->disabled(),
                    TextInput::make('visitor_email')->label(__('capell-live-chat::generic.fields.email'))->disabled(),
                    TextInput::make('visitor_phone')->label(__('capell-live-chat::generic.fields.phone'))->disabled(),
                    TextInput::make('visitor_company')->label(__('capell-live-chat::generic.fields.company'))->disabled(),
                    Textarea::make('metadata')->label(__('capell-live-chat::generic.fields.metadata'))->disabled()->formatStateUsing(
                        static fn (mixed $state): string => json_encode($state, JSON_PRETTY_PRINT) ?: '',
                    )->columnSpanFull(),
                ])
                ->columns(2),
            Section::make(__('capell-live-chat::generic.resources.ai_runs'))
                ->schema([
                    Textarea::make('metadata.ai.summary')
                        ->label(__('capell-live-chat::generic.fields.summary'))
                        ->disabled()
                        ->dehydrated(false)
                        ->columnSpanFull(),
                    TextInput::make('metadata.ai.sentiment')
                        ->label(__('capell-live-chat::generic.fields.sentiment'))
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('metadata.ai.risk')
                        ->label(__('capell-live-chat::generic.fields.risk'))
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('metadata.ai.lead_qualification')
                        ->label(__('capell-live-chat::generic.fields.lead_qualification'))
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('metadata.ai.knowledge_gap_reason')
                        ->label(__('capell-live-chat::generic.fields.knowledge_gap_reason'))
                        ->disabled()
                        ->dehydrated(false),
                    Textarea::make('metadata.ai.source_documents')
                        ->label(__('capell-live-chat::generic.fields.source_documents'))
                        ->disabled()
                        ->dehydrated(false)
                        ->formatStateUsing(static function (mixed $state): string {
                            if (is_array($state)) {
                                return json_encode($state, JSON_PRETTY_PRINT) ?: '';
                            }

                            return is_string($state) ? $state : '';
                        })
                        ->columnSpanFull(),
                    Textarea::make('metadata.ai.suggested_reply')
                        ->label(__('capell-live-chat::generic.fields.suggested_reply'))
                        ->disabled()
                        ->dehydrated(false)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('uuid')->label(__('capell-live-chat::generic.fields.uuid'))->limit(10)->copyable(),
            TextColumn::make('visitor_name')->label(__('capell-live-chat::generic.fields.name'))->limit(24),
            TextColumn::make('visitor_email')->label(__('capell-live-chat::generic.fields.email'))->limit(28)->toggleable(),
            TextColumn::make('status')->label(__('capell-live-chat::generic.fields.status'))->badge()->sortable(),
            TextColumn::make('intent')->label(__('capell-live-chat::generic.fields.intent'))->badge()->sortable(),
            TextColumn::make('priority')->label(__('capell-live-chat::generic.fields.priority'))->badge()->sortable(),
            TextColumn::make('assignment_queue')->label(__('capell-live-chat::generic.fields.route_to'))->toggleable(),
            TextColumn::make('last_message_at')->label(__('capell-live-chat::generic.fields.last_message_at'))->dateTime()->sortable(),
            TextColumn::make('created_at')->label(__('capell-live-chat::generic.fields.created_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
        ])->defaultSort('last_message_at', 'desc')->recordActions([
            self::generateSummaryAction(),
            self::suggestReplyAction(),
            self::syncContactAction(),
            self::requestHandoffAction(),
            self::closeAction(),
        ]);
    }

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->latest('last_message_at');
    }

    #[Override]
    public static function getModel(): string
    {
        return LiveChatConversation::class;
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return __('capell-live-chat::generic.navigation_group');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-live-chat::generic.resources.conversations');
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListConversations::route('/'),
            'edit' => EditConversation::route('/{record}/edit'),
        ];
    }

    public static function requestHandoffAction(): Action
    {
        return Action::make('request_handoff')
            ->label(__('capell-live-chat::generic.actions.request_handoff'))
            ->icon('heroicon-o-hand-raised')
            ->visible(static fn (LiveChatConversation $record): bool => $record->status !== ConversationStatus::Closed)
            ->action(function (LiveChatConversation $record): void {
                RequestLiveChatHandoffAction::run($record);

                Notification::make('live-chat-handoff-requested')
                    ->title(__('capell-live-chat::generic.messages.handoff_requested'))
                    ->success()
                    ->send();
            });
    }

    public static function generateSummaryAction(): Action
    {
        return Action::make('generate_summary')
            ->label(__('capell-live-chat::generic.actions.generate_summary'))
            ->icon('heroicon-o-sparkles')
            ->action(function (LiveChatConversation $record): void {
                GenerateLiveChatSummaryAction::run($record);

                Notification::make('live-chat-summary-generated')
                    ->title(__('capell-live-chat::generic.messages.summary_generated'))
                    ->success()
                    ->send();
            });
    }

    public static function suggestReplyAction(): Action
    {
        return Action::make('suggest_reply')
            ->label(__('capell-live-chat::generic.actions.suggest_reply'))
            ->icon('heroicon-o-chat-bubble-left-ellipsis')
            ->action(function (LiveChatConversation $record): void {
                SuggestLiveChatHumanReplyAction::run($record);

                Notification::make('live-chat-reply-suggested')
                    ->title(__('capell-live-chat::generic.messages.reply_suggested'))
                    ->success()
                    ->send();
            });
    }

    public static function syncContactAction(): Action
    {
        return Action::make('sync_contact')
            ->label(__('capell-live-chat::generic.actions.sync_contact'))
            ->icon('heroicon-o-user-plus')
            ->action(function (LiveChatConversation $record): void {
                SyncLiveChatConversationContactAction::run($record);

                Notification::make('live-chat-contact-synced')
                    ->title(__('capell-live-chat::generic.messages.contact_synced'))
                    ->success()
                    ->send();
            });
    }

    public static function closeAction(): Action
    {
        return Action::make('close')
            ->label(__('capell-live-chat::generic.actions.close'))
            ->icon('heroicon-o-check-circle')
            ->visible(static fn (LiveChatConversation $record): bool => $record->status !== ConversationStatus::Closed)
            ->requiresConfirmation()
            ->action(function (LiveChatConversation $record): void {
                CloseLiveChatConversationAction::run($record);

                Notification::make('live-chat-closed')
                    ->title(__('capell-live-chat::generic.messages.conversation_closed'))
                    ->success()
                    ->send();
            });
    }

    /**
     * @return array<string, string>
     */
    private static function statusOptions(): array
    {
        return collect(ConversationStatus::cases())
            ->mapWithKeys(static fn (ConversationStatus $status): array => [$status->value => $status->getLabel()])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private static function intentOptions(): array
    {
        return collect(LiveChatIntent::cases())
            ->mapWithKeys(static fn (LiveChatIntent $intent): array => [$intent->value => $intent->getLabel()])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private static function priorityOptions(): array
    {
        return collect(LiveChatPriority::cases())
            ->mapWithKeys(static fn (LiveChatPriority $priority): array => [$priority->value => $priority->getLabel()])
            ->all();
    }
}
