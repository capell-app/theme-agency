<?php

declare(strict_types=1);

namespace Capell\Comments\Actions;

use Capell\Comments\Data\CommentEmailTemplateData;
use Lorisleiva\Actions\Concerns\AsAction;

class RegisterCommentEmailTemplatesAction
{
    use AsAction;

    public function handle(): void
    {
        $registryClass = 'Capell\\EmailStudio\\Support\\EmailTemplateRegistry';

        if (! class_exists($registryClass) || ! app()->bound($registryClass)) {
            return;
        }

        $registry = app($registryClass);

        foreach ($this->templates() as $template) {
            $registry->register(
                key: $template->key,
                name: $template->name,
                variables: $template->variables,
                description: $template->description,
                packageName: 'capell-app/comments',
            );
        }

        if (method_exists($registry, 'persist')) {
            $registry->persist();
        }
    }

    /**
     * @return list<CommentEmailTemplateData>
     */
    private function templates(): array
    {
        return [
            new CommentEmailTemplateData('comments.verify-email', 'Comment email verification', ['verification_url', 'site_name']),
            new CommentEmailTemplateData('comments.pending-moderation', 'Comment pending moderation', ['comment_excerpt', 'commentable_title', 'moderation_url']),
            new CommentEmailTemplateData('comments.approved', 'Comment approved', ['comment_excerpt', 'commentable_url']),
            new CommentEmailTemplateData('comments.rejected', 'Comment rejected', ['comment_excerpt', 'site_name']),
            new CommentEmailTemplateData('comments.reply-notification', 'Comment reply notification', ['comment_excerpt', 'reply_excerpt', 'commentable_url']),
        ];
    }
}
