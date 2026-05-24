<?php

declare(strict_types=1);

namespace Capell\Comments\Filament\Pages;

use BackedEnum;
use Capell\Admin\Support\SiteScope;
use Capell\Comments\Enums\CommentStatus;
use Capell\Comments\Filament\Resources\Comments\Tables\CommentsTable;
use Capell\Comments\Models\Comment;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Override;

class CommentModerationInbox extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxStack;

    protected string $view = 'capell-comments::filament.moderation-inbox';

    #[Override]
    public static function getNavigationLabel(): string
    {
        return (string) __('capell-comments::navigation.moderation_inbox');
    }

    #[Override]
    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof Authenticatable && Gate::forUser($user)->allows('viewAny', Comment::class);
    }

    public function table(Table $table): Table
    {
        return CommentsTable::configure($table)
            ->query(fn (): Builder => SiteScope::applyForCurrentActor(
                Comment::query()
                    ->with(['author', 'site'])
                    ->whereIn('status', [
                        CommentStatus::PendingApproval,
                        CommentStatus::PendingEmailVerification,
                        CommentStatus::Spam,
                    ]),
            ));
    }
}
