<?php

declare(strict_types=1);

namespace Capell\Comments\Actions;

use Capell\Comments\Data\CommentEmailTemplateData;
use Capell\EmailStudio\Data\EmailTemplateDefinitionData;
use Capell\EmailStudio\Data\EmailTemplateVariableData;
use Capell\EmailStudio\Support\EmailTemplateRegistry;
use Lorisleiva\Actions\Concerns\AsAction;

class RegisterCommentEmailTemplatesAction
{
    use AsAction;

    public function handle(): void
    {
        $registryClass = EmailTemplateRegistry::class;

        if (
            ! class_exists($registryClass)
            || ! class_exists(EmailTemplateDefinitionData::class)
            || ! class_exists(EmailTemplateVariableData::class)
        ) {
            return;
        }

        if (app()->bound($registryClass)) {
            $this->registerWithRegistry(resolve($registryClass));

            return;
        }

        app()->afterResolving($registryClass, function (object $registry): void {
            $this->registerWithRegistry($registry);
        });
    }

    private static function sampleValue(string $variable): string
    {
        return match ($variable) {
            'comment_excerpt' => 'This is a short excerpt from the original comment.',
            'commentable_title' => 'A published page',
            'commentable_url' => 'https://example.com/page#comments',
            'moderation_url' => 'https://example.com/admin/comments',
            'reply_excerpt' => 'This is a short excerpt from the reply.',
            'site_name' => 'Example Site',
            'verification_url' => 'https://example.com/comments/verify/example-token',
            default => 'Example value',
        };
    }

    private function registerWithRegistry(object $registry): void
    {
        if (! method_exists($registry, 'registerDefinition')) {
            return;
        }

        foreach ($this->templates() as $template) {
            $registry->registerDefinition(new EmailTemplateDefinitionData(
                key: $template->key,
                packageName: 'capell-app/comments',
                name: $template->name,
                description: $template->description,
                variables: array_map(
                    static fn (string $variable): EmailTemplateVariableData => new EmailTemplateVariableData(
                        name: $variable,
                        sampleValue: self::sampleValue($variable),
                    ),
                    $template->variables,
                ),
                subject: $template->subject,
                previewText: $template->previewText,
                text: $template->text,
                htmlView: $template->htmlView,
                sampleData: collect($template->variables)
                    ->mapWithKeys(static fn (string $variable): array => [$variable => self::sampleValue($variable)])
                    ->all(),
            ));
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
            new CommentEmailTemplateData(
                key: 'comments.verify-email',
                name: 'Comment email verification',
                variables: ['verification_url', 'site_name'],
                description: 'Sent when a commenter needs to verify their email before moderation.',
                subject: 'Verify your email for {{ site_name }}',
                previewText: 'Confirm your email address to finish submitting your comment.',
                htmlView: 'capell-comments::emails.verify-email',
                text: 'Verify your email for {{ site_name }}: {{ verification_url }}',
            ),
            new CommentEmailTemplateData(
                key: 'comments.pending-moderation',
                name: 'Comment pending moderation',
                variables: ['comment_excerpt', 'commentable_title', 'moderation_url'],
                description: 'Sent to moderators when a new comment is waiting for review.',
                subject: 'New comment waiting for moderation',
                previewText: '{{ comment_excerpt }}',
                htmlView: 'capell-comments::emails.pending-moderation',
                text: "A comment on {{ commentable_title }} is waiting for moderation.\n\n{{ comment_excerpt }}\n\nReview: {{ moderation_url }}",
            ),
            new CommentEmailTemplateData(
                key: 'comments.approved',
                name: 'Comment approved',
                variables: ['comment_excerpt', 'commentable_url'],
                description: 'Sent when a commenter has a comment approved.',
                subject: 'Your comment was approved',
                previewText: '{{ comment_excerpt }}',
                htmlView: 'capell-comments::emails.approved',
                text: "Your comment was approved:\n\n{{ comment_excerpt }}\n\nView it: {{ commentable_url }}",
            ),
            new CommentEmailTemplateData(
                key: 'comments.rejected',
                name: 'Comment rejected',
                variables: ['comment_excerpt', 'site_name'],
                description: 'Sent when a commenter has a comment rejected.',
                subject: 'Your comment on {{ site_name }} was not published',
                previewText: '{{ comment_excerpt }}',
                htmlView: 'capell-comments::emails.rejected',
                text: "Your comment on {{ site_name }} was not published.\n\n{{ comment_excerpt }}",
            ),
            new CommentEmailTemplateData(
                key: 'comments.reply-notification',
                name: 'Comment reply notification',
                variables: ['comment_excerpt', 'reply_excerpt', 'commentable_url'],
                description: 'Sent to a commenter when someone replies to their comment.',
                subject: 'Someone replied to your comment',
                previewText: '{{ reply_excerpt }}',
                htmlView: 'capell-comments::emails.reply-notification',
                text: "Someone replied to your comment.\n\nYour comment: {{ comment_excerpt }}\n\nReply: {{ reply_excerpt }}\n\nView: {{ commentable_url }}",
            ),
        ];
    }
}
