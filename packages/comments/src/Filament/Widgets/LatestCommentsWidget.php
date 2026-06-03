<?php

declare(strict_types=1);

namespace Capell\Comments\Filament\Widgets;

use Capell\Admin\Contracts\CapellWidgetContract;
use Capell\Admin\Filament\Concerns\GatedByRoleAndSettings;
use Capell\Admin\Support\SiteScope;
use Capell\Comments\Filament\Resources\Comments\CommentResource;
use Capell\Comments\Models\Comment;
use Capell\Core\Contracts\Extensions\RegistersExtensionWidget;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Override;

class LatestCommentsWidget extends TableWidget implements CapellWidgetContract, RegistersExtensionWidget
{
    use GatedByRoleAndSettings;

    /** @var list<string> */
    protected static array $rolesConfigKeys = ['editor', 'admin', 'super_admin'];

    protected static string $settingsKey = 'latest_comments';

    protected static ?int $sort = 31;

    /** @var int|string|array<string, int|null> */
    protected int|string|array $columnSpan = 'full';

    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    #[Override]
    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => SiteScope::applyForCurrentActor(Comment::query()->with(['author', 'site']))->latest('submitted_at'))
            ->recordUrl(fn (Comment $record): string => CommentResource::getUrl(parameters: [
                'tableSearch' => (string) $record->getKey(),
            ]))
            ->columns([
                TextColumn::make('author.name')->label(__('capell-comments::table.author'))->limit(30),
                TextColumn::make('body')->label(__('capell-comments::table.comment'))->limit(80),
                TextColumn::make('status')->label(__('capell-comments::table.status'))->badge(),
                TextColumn::make('submitted_at')->label(__('capell-comments::table.submitted_at'))->dateTime(),
            ]);
    }
}
