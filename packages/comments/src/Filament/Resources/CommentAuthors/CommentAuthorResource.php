<?php

declare(strict_types=1);

namespace Capell\Comments\Filament\Resources\CommentAuthors;

use BackedEnum;
use Capell\Admin\Support\SiteScope;
use Capell\Comments\Actions\RequestCommentEmailVerificationAction;
use Capell\Comments\Actions\UpdateCommentAuthorModerationAction;
use Capell\Comments\Filament\Resources\CommentAuthors\Pages\ListCommentAuthors;
use Capell\Comments\Models\CommentAuthor;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Override;

class CommentAuthorResource extends Resource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?int $navigationSort = 36;

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => SiteScope::applyForCurrentActor($query->with('site')->withCount('comments')))
            ->columns([
                TextColumn::make('name')->label(__('capell-comments::table.author')),
                TextColumn::make('email')->label(__('capell-comments::table.email')),
                TextColumn::make('comments_count')->label(__('capell-comments::table.comments'))->numeric(),
                IconColumn::make('email_verified_at')->label(__('capell-comments::table.verified'))->boolean()->state(fn (CommentAuthor $record): bool => $record->isEmailVerified()),
                IconColumn::make('trusted_at')->label(__('capell-comments::table.trusted'))->boolean()->state(fn (CommentAuthor $record): bool => $record->isTrusted()),
                IconColumn::make('blocked_at')->label(__('capell-comments::table.blocked'))->boolean()->state(fn (CommentAuthor $record): bool => $record->isBlocked()),
            ])
            ->recordActions([
                self::authorAction('trust', __('capell-comments::table.action_trust'), 'heroicon-o-star', 'success'),
                self::authorAction('block', __('capell-comments::table.action_block'), 'heroicon-o-no-symbol', 'danger'),
                self::authorAction('unblock', __('capell-comments::table.action_unblock'), 'heroicon-o-arrow-path', 'gray'),
                self::authorAction('verify', __('capell-comments::table.action_verify'), 'heroicon-o-check-badge', 'success'),
                Action::make('resend_verification')
                    ->label(__('capell-comments::table.action_resend_verification'))
                    ->icon('heroicon-o-envelope')
                    ->visible(fn (CommentAuthor $record): bool => Gate::allows('update', $record)
                        && ! $record->isEmailVerified()
                        && is_string($record->email)
                        && $record->email !== '')
                    ->requiresConfirmation()
                    ->action(function (CommentAuthor $record): void {
                        Gate::authorize('update', $record);

                        RequestCommentEmailVerificationAction::run($record);
                        self::notify();
                    }),
            ]);
    }

    #[Override]
    public static function getModel(): string
    {
        return CommentAuthor::class;
    }

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return SiteScope::applyForCurrentActor(parent::getEloquentQuery()->with('site'));
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return (string) __('capell-comments::navigation.comment_authors');
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return (string) __('capell-admin::navigation.group_reports');
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListCommentAuthors::route('/'),
        ];
    }

    private static function authorAction(string $name, string $label, string $icon, string $color): Action
    {
        return Action::make($name)
            ->label($label)
            ->icon($icon)
            ->color($color)
            ->requiresConfirmation()
            ->visible(fn (CommentAuthor $record): bool => match ($name) {
                'trust' => Gate::allows('update', $record) && ! $record->isTrusted(),
                'block' => Gate::allows('update', $record) && ! $record->isBlocked(),
                'unblock' => Gate::allows('update', $record) && $record->isBlocked(),
                'verify' => Gate::allows('update', $record) && ! $record->isEmailVerified(),
                default => false,
            })
            ->action(function (CommentAuthor $record) use ($name): void {
                Gate::authorize('update', $record);

                /** @var UpdateCommentAuthorModerationAction $action */
                $action = resolve(UpdateCommentAuthorModerationAction::class);
                $action->{$name}($record);
                self::notify();
            });
    }

    private static function notify(): void
    {
        Notification::make('comment-author-updated')
            ->title(__('capell-comments::messages.author_updated'))
            ->icon('heroicon-o-check-circle')
            ->iconColor('success')
            ->send();
    }
}
