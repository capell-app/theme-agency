<?php

declare(strict_types=1);

use Capell\Comments\Enums\CommentStatus;
use Capell\Comments\Filament\Resources\CommentAuthors\CommentAuthorResource;
use Capell\Comments\Filament\Resources\Comments\CommentResource;
use Capell\Comments\Filament\Resources\Comments\Tables\CommentsTable;
use Capell\Comments\Filament\Settings\Contributors\CommentsDashboardSettingsContributor;
use Capell\Comments\Filament\Widgets\CommentStatsWidget;
use Capell\Comments\Filament\Widgets\LatestCommentsWidget;
use Capell\Comments\Models\Comment;
use Capell\Comments\Models\CommentAuthor;
use Capell\Comments\Models\CommentModerationEvent;
use Capell\Tests\Fixtures\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

it('builds the comments moderation resources with their table controls', function (): void {
    Gate::before(fn (): bool => true);

    $commentsTable = CommentResource::table(commentAdminTableForCoverage());
    $authorsTable = CommentAuthorResource::table(commentAdminTableForCoverage());

    expect(CommentResource::getModel())->toBe(Comment::class)
        ->and(CommentAuthorResource::getModel())->toBe(CommentAuthor::class)
        ->and(CommentResource::getPages())->toHaveKey('index')
        ->and(CommentAuthorResource::getPages())->toHaveKey('index')
        ->and(array_keys($commentsTable->getColumns()))->toContain('commentable', 'author.name', 'body', 'status')
        ->and(array_keys($commentsTable->getFilters()))->toContain('status')
        ->and(commentAdminActionNames($commentsTable->getActions()))->toContain('context', 'approve', 'reject', 'spam', 'archive')
        ->and(array_keys($authorsTable->getColumns()))->toContain('name', 'email', 'comments_count', 'email_verified_at', 'trusted_at', 'blocked_at')
        ->and(commentAdminActionNames($authorsTable->getActions()))->toContain('trust', 'block', 'unblock', 'verify', 'resend_verification');
});

it('denies comment moderation resources to users without global or site scope', function (): void {
    $this->actingAs(User::factory()->create());

    expect(CommentResource::canAccess())->toBeFalse()
        ->and(CommentAuthorResource::canAccess())->toBeFalse();
});

it('allows global admins to access comment moderation resources', function (): void {
    $superAdminRole = (string) config('capell.roles.super_admin', 'super_admin');
    Role::findOrCreate($superAdminRole, 'web');
    $user = User::factory()->create();
    $user->assignRole($superAdminRole);

    $this->actingAs($user);

    expect(CommentResource::canAccess())->toBeTrue()
        ->and(CommentAuthorResource::canAccess())->toBeTrue();
});

it('runs the approve moderation table action and records the moderation event', function (): void {
    Gate::before(fn (?User $user = null): bool => $user instanceof User);
    Notification::fake();

    $this->actingAs(User::factory()->create());

    $page = $this->createCommentsPage();
    $comment = Comment::factory()->pendingApproval()->create([
        'site_id' => $page->site_id,
        'commentable_type' => $page->getMorphClass(),
        'commentable_id' => $page->getKey(),
        'email_verified_at' => now(),
    ]);

    $action = collect(CommentsTable::configure(commentAdminTableForCoverage())->getActions())
        ->first(fn (Action|ActionGroup $action): bool => $action instanceof Action && $action->getName() === 'approve');

    expect($action)->toBeInstanceOf(Action::class);

    throw_unless($action instanceof Action, RuntimeException::class, 'Expected approve moderation action.');

    $closure = $action->getActionFunction();

    expect($closure)->not->toBeNull();

    throw_if(! $closure instanceof Closure, RuntimeException::class, 'Expected approve action closure.');

    $action->evaluate($closure, [
        'record' => $comment,
        'data' => ['note' => 'Looks good'],
    ]);

    expect($comment->refresh()->status)->toBe(CommentStatus::Approved)
        ->and(CommentModerationEvent::query()->where('comment_id', $comment->getKey())->where('action', 'status_changed')->where('note', 'Looks good')->exists())->toBeTrue();
});

it('author moderation actions enforce update authorization inside callbacks', function (): void {
    Gate::before(fn (?User $user = null, string $ability = ''): ?bool => $ability === 'update' ? false : ($user instanceof User ? true : null));
    Notification::fake();

    $this->actingAs(User::factory()->create());

    $site = $this->createCommentsSite();
    $author = CommentAuthor::factory()->unverified()->create(['site_id' => $site->getKey()]);
    $actions = collect(CommentAuthorResource::table(commentAdminTableForCoverage())->getActions());
    $trustAction = $actions->first(fn (Action|ActionGroup $action): bool => $action instanceof Action && $action->getName() === 'trust');
    $resendAction = $actions->first(fn (Action|ActionGroup $action): bool => $action instanceof Action && $action->getName() === 'resend_verification');

    expect($trustAction)->toBeInstanceOf(Action::class)
        ->and($resendAction)->toBeInstanceOf(Action::class);

    throw_if(! $trustAction instanceof Action || ! $resendAction instanceof Action, RuntimeException::class, 'Expected author moderation actions.');

    $trustClosure = $trustAction->getActionFunction();
    $resendClosure = $resendAction->getActionFunction();

    expect($trustClosure)->not->toBeNull()
        ->and($resendClosure)->not->toBeNull();

    throw_if(! $trustClosure instanceof Closure || ! $resendClosure instanceof Closure, RuntimeException::class, 'Expected author moderation action closures.');

    expect(fn (): mixed => $trustAction->evaluate($trustClosure, ['record' => $author]))
        ->toThrow(AuthorizationException::class)
        ->and(fn (): mixed => $resendAction->evaluate($resendClosure, ['record' => $author]))
        ->toThrow(AuthorizationException::class);

    expect($author->refresh()->trusted_at)->toBeNull();
});

it('author moderation actions update records when the actor can update the author', function (): void {
    Gate::before(fn (?User $user = null, string $ability = ''): ?bool => $ability === 'update' ? true : ($user instanceof User ? true : null));
    Notification::fake();

    $this->actingAs(User::factory()->create());

    $site = $this->createCommentsSite();
    $author = CommentAuthor::factory()->unverified()->create(['site_id' => $site->getKey()]);
    $action = collect(CommentAuthorResource::table(commentAdminTableForCoverage())->getActions())
        ->first(fn (Action|ActionGroup $tableAction): bool => $tableAction instanceof Action && $tableAction->getName() === 'trust');

    expect($action)->toBeInstanceOf(Action::class);

    throw_unless($action instanceof Action, RuntimeException::class, 'Expected trust author action.');

    $closure = $action->getActionFunction();

    expect($closure)->not->toBeNull();

    throw_if(! $closure instanceof Closure, RuntimeException::class, 'Expected trust author action closure.');

    $action->evaluate($closure, ['record' => $author]);

    expect($author->refresh()->trusted_at)->not->toBeNull();
});

it('resolves moderation context labels and public URLs from the related commentable', function (): void {
    $commentable = new CommentAdminUrlCommentable([
        'name' => 'Public article',
        'url' => 'https://example.test/public-article',
    ]);
    $comment = new Comment;
    $comment->setRelation('commentable', $commentable);

    $label = new ReflectionMethod(CommentsTable::class, 'commentableLabel');
    $url = new ReflectionMethod(CommentsTable::class, 'commentableUrl');

    expect($label->invoke(null, $comment))->toBe('Public article')
        ->and($url->invoke(null, $comment))->toBe('https://example.test/public-article');
});

it('builds comment moderation stats from real comment states', function (): void {
    $page = $this->createCommentsPage();
    $author = CommentAuthor::factory()->create(['site_id' => $page->site_id]);
    $commentDefaults = [
        'site_id' => $page->site_id,
        'comment_author_id' => $author->getKey(),
        'commentable_type' => $page->getMorphClass(),
        'commentable_id' => $page->getKey(),
    ];

    Comment::factory()->pendingApproval()->create($commentDefaults);
    Comment::factory()->create([...$commentDefaults, 'status' => CommentStatus::Approved]);
    Comment::factory()->spam()->create($commentDefaults);
    Comment::factory()->spam()->create($commentDefaults);

    $widget = new CommentStatsWidget;
    $stats = (new ReflectionMethod(CommentStatsWidget::class, 'getStats'))->invoke($widget);

    expect($stats)->toHaveCount(3)
        ->and((string) $stats[0]->getValue())->toBe('1')
        ->and((string) $stats[1]->getValue())->toBe('1')
        ->and((string) $stats[2]->getValue())->toBe('2')
        ->and(CommentStatsWidget::compatibleCapellApiVersion())->toBe('^4.0');
});

it('declares comment dashboard settings and latest comments table metadata', function (): void {
    Gate::before(fn (): bool => true);

    $entries = (new CommentsDashboardSettingsContributor)->settingsKeys();
    $table = (new LatestCommentsWidget)->table(commentAdminTableForCoverage());
    $site = $this->createCommentsSite();
    $author = CommentAuthor::factory()->create(['site_id' => $site->getKey(), 'name' => 'Taylor Editor']);
    $comment = Comment::factory()->create([
        'site_id' => $site->getKey(),
        'comment_author_id' => $author->getKey(),
        'body' => 'Useful editorial feedback',
    ]);

    expect($entries)->toBe([
        [
            'key' => 'comment_stats',
            'label' => __('capell-comments::widgets.comment_stats'),
            'group' => __('capell-comments::widgets.group'),
        ],
        [
            'key' => 'latest_comments',
            'label' => __('capell-comments::widgets.latest_comments'),
            'group' => __('capell-comments::widgets.group'),
        ],
    ])->and(array_keys($table->getColumns()))->toBe(['author.name', 'body', 'status', 'submitted_at'])
        ->and($table->getRecordUrl($comment))->toContain('tableSearch=' . $comment->getKey());
});

function commentAdminTableForCoverage(): Table
{
    $livewire = Mockery::mock(HasTable::class);
    $livewire->shouldIgnoreMissing();
    $livewire->shouldReceive('makeFilamentTranslatableContentDriver')->andReturn(null)->byDefault();
    $livewire->shouldReceive('getTableFilterState')->andReturn([])->byDefault();
    $livewire->shouldReceive('isTableLoaded')->andReturnTrue()->byDefault();
    $livewire->shouldReceive('getTableArguments')->andReturn([])->byDefault();

    return Table::make($livewire);
}

/**
 * @param  array<array-key, mixed>  $actions
 * @return array<int, string>
 */
function commentAdminActionNames(array $actions): array
{
    return collect($actions)
        ->flatten()
        ->filter(fn (mixed $action): bool => $action instanceof Action)
        ->map(fn (Action $action): string => $action->getName() ?? '')
        ->values()
        ->all();
}

final class CommentAdminUrlCommentable extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function getUrl(): string
    {
        return (string) $this->getAttribute('url');
    }
}
