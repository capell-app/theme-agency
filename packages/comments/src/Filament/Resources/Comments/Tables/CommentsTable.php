<?php

declare(strict_types=1);

namespace Capell\Comments\Filament\Resources\Comments\Tables;

use Capell\Admin\Support\SiteScope;
use Capell\Comments\Actions\TransitionCommentStatusAction;
use Capell\Comments\Enums\CommentStatus;
use Capell\Comments\Models\Comment;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

class CommentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => SiteScope::applyForCurrentActor($query->with(['author', 'site', 'parent', 'commentable', 'moderationEvents'])))
            ->defaultSort('submitted_at', 'desc')
            ->emptyStateHeading(__('capell-comments::table.comments_empty'))
            ->columns([
                TextColumn::make('id')
                    ->label(__('capell-comments::table.id'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('commentable')
                    ->label(__('capell-comments::table.commentable'))
                    ->state(fn (Comment $record): string => self::commentableLabel($record))
                    ->limit(40),
                TextColumn::make('author.name')->label(__('capell-comments::table.author'))->limit(30),
                TextColumn::make('body')->label(__('capell-comments::table.comment'))->limit(90),
                TextColumn::make('status')->label(__('capell-comments::table.status'))->badge(),
                TextColumn::make('site.name')->label(__('capell-comments::table.site'))->toggleable(),
                TextColumn::make('submitted_at')->label(__('capell-comments::table.submitted_at'))->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('capell-comments::table.status'))
                    ->options(collect(CommentStatus::cases())
                        ->mapWithKeys(fn (CommentStatus $status): array => [$status->value => $status->getLabel()])
                        ->all()),
            ])
            ->recordActions([
                Action::make('context')
                    ->label(__('capell-comments::table.action_context'))
                    ->icon('heroicon-o-eye')
                    ->modalSubmitAction(false)
                    ->modalContent(fn (Comment $record): View => view('capell-comments::filament.comment-context', [
                        'comment' => $record,
                        'commentableLabel' => self::commentableLabel($record),
                        'commentableUrl' => self::commentableUrl($record),
                    ])),
                self::statusAction('approve', CommentStatus::Approved, 'heroicon-o-check-circle', 'success'),
                self::statusAction('reject', CommentStatus::Rejected, 'heroicon-o-x-circle', 'gray'),
                self::statusAction('spam', CommentStatus::Spam, 'heroicon-o-no-symbol', 'danger'),
                self::statusAction('archive', CommentStatus::Archived, 'heroicon-o-archive-box', 'warning'),
            ]);
    }

    private static function statusAction(string $name, CommentStatus $status, string $icon, string $color): Action
    {
        return Action::make($name)
            ->label(__('capell-comments::table.action_' . $name))
            ->icon($icon)
            ->color($color)
            ->requiresConfirmation()
            ->visible(fn (Comment $record): bool => $record->status !== $status && self::canTransition($record, $status))
            ->form([
                Textarea::make('note')
                    ->label(__('capell-comments::table.moderation_note'))
                    ->rows(3),
            ])
            ->action(function (Comment $record, array $data) use ($status): void {
                Gate::authorize('update', $record);

                TransitionCommentStatusAction::run(
                    comment: $record,
                    status: $status,
                    moderatorId: auth()->id(),
                    note: is_string($data['note'] ?? null) ? $data['note'] : null,
                );

                Notification::make('comment-status-updated')
                    ->title(__('capell-comments::messages.status_updated'))
                    ->icon('heroicon-o-check-circle')
                    ->iconColor('success')
                    ->send();
            });
    }

    private static function canTransition(Comment $record, CommentStatus $status): bool
    {
        if (! Gate::allows('update', $record)) {
            return false;
        }

        return $status !== CommentStatus::Approved || $record->email_verified_at !== null;
    }

    private static function commentableLabel(Comment $record): string
    {
        $commentable = $record->commentable;
        if ($commentable === null) {
            return __('capell-comments::generic.comment');
        }

        foreach (['name', 'title'] as $attribute) {
            $value = $commentable->getAttribute($attribute);
            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return class_basename($commentable);
    }

    private static function commentableUrl(Comment $record): ?string
    {
        $commentable = $record->commentable;
        if ($commentable === null || ! method_exists($commentable, 'getUrl')) {
            return null;
        }

        $url = $commentable->getUrl();

        return is_string($url) && $url !== '' ? $url : null;
    }
}
