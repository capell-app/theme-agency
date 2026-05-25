<?php

declare(strict_types=1);

namespace Capell\Comments\Actions;

use Capell\Comments\Data\CommentableTypeData;
use Capell\Comments\Data\CreateCommentData;
use Capell\Comments\Enums\CommentIdentityMode;
use Capell\Comments\Enums\CommentPublicationPolicy;
use Capell\Comments\Enums\CommentStatus;
use Capell\Comments\Enums\CommentVerificationFlow;
use Capell\Comments\Models\Comment;
use Capell\Comments\Models\CommentAuthor;
use Capell\Comments\Support\CommentableRegistry;
use Capell\Comments\Support\CommentBodySanitizer;
use Capell\Comments\Support\CommentSettingsResolver;
use Capell\Comments\Support\VisitorHasher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateCommentAction
{
    use AsAction;

    public function __construct(
        private readonly CommentableRegistry $commentableRegistry,
        private readonly CommentSettingsResolver $settings,
        private readonly CommentBodySanitizer $sanitizer,
        private readonly VisitorHasher $visitorHasher,
    ) {}

    public function handle(CreateCommentData $data): Comment
    {
        return DB::transaction(function () use ($data): Comment {
            $commentableType = $this->commentableRegistry->forModel($data->commentable);

            if (! $commentableType instanceof CommentableTypeData || ! $commentableType->isVisible($data->commentable)) {
                throw ValidationException::withMessages([
                    'commentable' => __('capell-comments::messages.commentable_unavailable'),
                ]);
            }

            $siteId = $data->siteId ?? $commentableType->siteId($data->commentable);
            if ($siteId === null || ! $this->settings->enabled($siteId, $commentableType->key)) {
                throw ValidationException::withMessages([
                    'commentable' => __('capell-comments::messages.comments_disabled'),
                ]);
            }

            if ($this->settings->publicationPolicy($siteId, $commentableType->key) === CommentPublicationPolicy::Disabled) {
                throw ValidationException::withMessages([
                    'commentable' => __('capell-comments::messages.comments_disabled'),
                ]);
            }

            $author = $this->resolveAuthor($data, $siteId, $commentableType->key);
            if ($author->isBlocked()) {
                throw ValidationException::withMessages([
                    'author' => __('capell-comments::messages.author_blocked'),
                ]);
            }

            $emailVerifiedForSubmission = $data->user instanceof Model && $author->isEmailVerified();

            $parent = $this->resolveParent($data, $siteId, $data->commentable);
            $depth = $parent instanceof Comment ? $parent->depth + 1 : 0;
            if ($depth > $this->settings->maxDepth($siteId, $commentableType->key)) {
                throw ValidationException::withMessages([
                    'parent' => __('capell-comments::messages.max_depth_reached'),
                ]);
            }

            $body = $this->sanitizer->sanitize($data->body);
            if ($body === '') {
                throw ValidationException::withMessages([
                    'body' => __('capell-comments::messages.body_required'),
                ]);
            }

            $comment = Comment::query()->create([
                'site_id' => $siteId,
                'language_id' => $data->languageId,
                'comment_author_id' => $author->getKey(),
                'commentable_type' => $data->commentable->getMorphClass(),
                'commentable_id' => $data->commentable->getKey(),
                'parent_id' => $parent?->getKey(),
                'root_id' => $parent instanceof Comment ? ($parent->root_id ?? $parent->getKey()) : null,
                'depth' => $depth,
                'status' => $this->initialStatus($author, $siteId, $commentableType->key, $emailVerifiedForSubmission),
                'body' => $body,
                'visitor_ip_hash' => $this->visitorHasher->hash($data->ipAddress, $siteId),
                'visitor_user_agent_hash' => $this->visitorHasher->hash($data->userAgent, $siteId),
                'link_count' => $this->sanitizer->linkCount($body),
                'submitted_at' => now()->toImmutable(),
                'email_verified_at' => $emailVerifiedForSubmission ? now()->toImmutable() : null,
            ]);

            CommentModerationEventAction::run(
                comment: $comment,
                action: 'created',
                previousStatus: null,
                newStatus: $comment->status,
            );

            if ($this->settings->requiresEmailVerification($siteId, $commentableType->key) && ! $emailVerifiedForSubmission && $author->email !== null) {
                RequestCommentEmailVerificationAction::run($author, $comment);
            }

            if ($comment->isPubliclyVisible()) {
                InvalidateCommentableCacheAction::run($data->commentable);
            }

            return $comment->fresh(['author', 'parent']) ?? $comment;
        });
    }

    private function resolveAuthor(CreateCommentData $data, int $siteId, string $commentableType): CommentAuthor
    {
        $mode = $this->settings->identityMode($siteId, $commentableType);
        $hasUser = $data->user instanceof Model;

        if ($mode === CommentIdentityMode::Authenticated && ! $hasUser) {
            throw ValidationException::withMessages([
                'author' => __('capell-comments::messages.authentication_required'),
            ]);
        }

        if ($hasUser) {
            return $this->resolveAuthenticatedAuthor($data->user, $siteId);
        }

        if ($mode === CommentIdentityMode::Authenticated) {
            throw ValidationException::withMessages([
                'author' => __('capell-comments::messages.authentication_required'),
            ]);
        }

        if (! is_string($data->authorName) || trim($data->authorName) === '') {
            throw ValidationException::withMessages(['authorName' => __('capell-comments::messages.name_required')]);
        }

        if (! is_string($data->authorEmail) || ! filter_var($data->authorEmail, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withMessages(['authorEmail' => __('capell-comments::messages.email_required')]);
        }

        /** @var CommentAuthor|null $author */
        $author = CommentAuthor::query()
            ->where('site_id', $siteId)
            ->where('email_hash', CommentAuthor::emailHash($data->authorEmail))
            ->lockForUpdate()
            ->first();

        if (! $author instanceof CommentAuthor) {
            /** @var CommentAuthor $author */
            $author = CommentAuthor::query()->create([
                'site_id' => $siteId,
                'name' => trim($data->authorName),
                'email' => trim($data->authorEmail),
            ]);
        }

        return $author;
    }

    private function resolveAuthenticatedAuthor(Model $user, int $siteId): CommentAuthor
    {
        $email = is_string($user->getAttribute('email')) ? $user->getAttribute('email') : null;
        $name = is_string($user->getAttribute('name')) ? $user->getAttribute('name') : ($email ?? __('capell-comments::generic.authenticated_author'));

        /** @var CommentAuthor|null $author */
        $author = CommentAuthor::query()
            ->where('site_id', $siteId)
            ->where('user_type', $user->getMorphClass())
            ->where('user_id', $user->getKey())
            ->lockForUpdate()
            ->first();

        if (! $author instanceof CommentAuthor && is_string($email)) {
            /** @var CommentAuthor|null $author */
            $author = CommentAuthor::query()
                ->where('site_id', $siteId)
                ->where('email_hash', CommentAuthor::emailHash($email))
                ->whereNull('user_type')
                ->whereNull('user_id')
                ->lockForUpdate()
                ->first();
        }

        if (! $author instanceof CommentAuthor) {
            /** @var CommentAuthor $author */
            $author = CommentAuthor::query()->create([
                'site_id' => $siteId,
                'user_type' => $user->getMorphClass(),
                'user_id' => $user->getKey(),
                'name' => $name,
                'email' => $email,
            ]);
        }

        $author->forceFill([
            'user_type' => $user->getMorphClass(),
            'user_id' => $user->getKey(),
            'name' => $name,
            'email' => $email,
            'email_verified_at' => $author->isEmailVerified() || $this->userHasVerifiedEmail($user)
                ? ($author->email_verified_at ?? now())
                : null,
        ])->save();

        return $author;
    }

    private function userHasVerifiedEmail(Model $user): bool
    {
        if (method_exists($user, 'hasVerifiedEmail')) {
            return (bool) $user->hasVerifiedEmail();
        }

        return $user->getAttribute('email_verified_at') !== null;
    }

    private function resolveParent(CreateCommentData $data, int $siteId, Model $commentable): ?Comment
    {
        if (! is_string($data->parentPublicId) || $data->parentPublicId === '') {
            return null;
        }

        /** @var Comment|null $parent */
        $parent = Comment::query()
            ->where('site_id', $siteId)
            ->where('public_id', $data->parentPublicId)
            ->where('commentable_type', $commentable->getMorphClass())
            ->where('commentable_id', $commentable->getKey())
            ->lockForUpdate()
            ->first();

        if (! $parent instanceof Comment || ! $parent->isPubliclyVisible()) {
            throw ValidationException::withMessages([
                'parent' => __('capell-comments::messages.parent_unavailable'),
            ]);
        }

        return $parent;
    }

    private function initialStatus(CommentAuthor $author, int $siteId, string $commentableType, bool $emailVerifiedForSubmission): CommentStatus
    {
        $policy = $this->settings->publicationPolicy($siteId, $commentableType);
        $requiresVerification = $this->settings->requiresEmailVerification($siteId, $commentableType);

        if ($policy === CommentPublicationPolicy::AutoPublish && $emailVerifiedForSubmission && $author->isTrusted()) {
            return CommentStatus::Approved;
        }

        if ($requiresVerification && ! $emailVerifiedForSubmission) {
            return $this->settings->verificationFlow($siteId, $commentableType) === CommentVerificationFlow::VerifyThenModerate
                ? CommentStatus::PendingEmailVerification
                : CommentStatus::PendingApproval;
        }

        return CommentStatus::PendingApproval;
    }
}
