<?php

declare(strict_types=1);

namespace Capell\Comments\Filament\Resources\Comments;

use BackedEnum;
use Capell\Admin\Support\SiteScope;
use Capell\Comments\Filament\Resources\Comments\Pages\ListComments;
use Capell\Comments\Filament\Resources\Comments\Tables\CommentsTable;
use Capell\Comments\Models\Comment;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Override;

class CommentResource extends Resource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::ChatBubbleLeftRight;

    protected static ?int $navigationSort = 35;

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return CommentsTable::configure($table);
    }

    #[Override]
    public static function getModel(): string
    {
        return Comment::class;
    }

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return SiteScope::applyForCurrentActor(parent::getEloquentQuery()->with(['author', 'site']));
    }

    #[Override]
    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof Authenticatable && Gate::forUser($user)->allows('viewAny', Comment::class);
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return (string) __('capell-comments::navigation.comments');
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
            'index' => ListComments::route('/'),
        ];
    }
}
