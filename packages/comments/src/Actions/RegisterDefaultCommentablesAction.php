<?php

declare(strict_types=1);

namespace Capell\Comments\Actions;

use Capell\Blog\Models\Article;
use Capell\Comments\Data\CommentableTypeData;
use Capell\Comments\Support\CommentableRegistry;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Models\Page;
use Illuminate\Database\Eloquent\Model;
use Lorisleiva\Actions\Concerns\AsAction;

class RegisterDefaultCommentablesAction
{
    use AsAction;

    public function __construct(private readonly CommentableRegistry $registry) {}

    public function handle(): void
    {
        $this->registry->register(new CommentableTypeData(
            key: 'page',
            modelClass: Page::class,
            labelResolver: static fn (Model $model): string => (string) ($model->getAttribute('name') ?? __('capell-comments::generic.page')),
            urlResolver: static fn (Model $model): ?string => method_exists($model, 'getUrl') ? (string) $model->getUrl() : null,
            siteResolver: static fn (Model $model): ?int => is_numeric($model->getAttribute('site_id')) ? (int) $model->getAttribute('site_id') : null,
            visibilityResolver: static fn (Model $model): bool => self::isPubliclyVisible($model),
        ));

        $articleClass = Article::class;
        if (! class_exists($articleClass) || ! CapellCore::isPackageInstalled('capell-app/blog')) {
            return;
        }

        $this->registry->register(new CommentableTypeData(
            key: 'article',
            modelClass: $articleClass,
            labelResolver: static fn (Model $model): string => (string) ($model->getAttribute('name') ?? __('capell-comments::generic.article')),
            urlResolver: static fn (Model $model): ?string => method_exists($model, 'getUrl') ? (string) $model->getUrl() : null,
            siteResolver: static fn (Model $model): ?int => is_numeric($model->getAttribute('site_id')) ? (int) $model->getAttribute('site_id') : null,
            visibilityResolver: static fn (Model $model): bool => self::isPubliclyVisible($model),
        ));
    }

    private static function isPubliclyVisible(Model $model): bool
    {
        if (method_exists($model, 'isPublished')) {
            return (bool) $model->isPublished();
        }

        if (method_exists($model, 'isPending') && $model->isPending()) {
            return false;
        }

        return ! (method_exists($model, 'isExpired') && $model->isExpired());
    }
}
