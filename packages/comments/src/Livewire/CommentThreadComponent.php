<?php

declare(strict_types=1);

namespace Capell\Comments\Livewire;

use Capell\Comments\Actions\CreateCommentAction;
use Capell\Comments\Actions\ResolvePublicCommentableThreadAction;
use Capell\Comments\Data\CommentableTypeData;
use Capell\Comments\Data\CreateCommentData;
use Capell\Comments\Data\PublicCommentData;
use Capell\Comments\Support\CommentableRegistry;
use Capell\Comments\Support\CommentSettingsResolver;
use Capell\Core\Contracts\Extensions\RegistersExtensionFrontendComponent;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Throwable;

class CommentThreadComponent extends Component implements RegistersExtensionFrontendComponent
{
    public string $threadKey = '';

    public string $body = '';

    public ?string $authorName = null;

    public ?string $authorEmail = null;

    public ?string $parentPublicId = null;

    public string $commentWebsite = '';

    public int $formRenderedAt = 0;

    public bool $submitted = false;

    public int $replyPageSize = 5;

    /**
     * @var array<string, int>
     */
    public array $replyLimits = [];

    /**
     * @var list<PublicCommentData>
     */
    public array $comments = [];

    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    public static function threadKeyFor(Model $commentable): string
    {
        return Crypt::encryptString(json_encode([
            'type' => $commentable->getMorphClass(),
            'id' => $commentable->getKey(),
            'site_id' => self::optionalAttribute($commentable, 'site_id'),
            'language_id' => self::optionalAttribute($commentable, 'language_id'),
        ], JSON_THROW_ON_ERROR));
    }

    public function mount(?string $threadKey = null): void
    {
        $this->threadKey = is_string($threadKey) ? $threadKey : '';
        $this->resetBotTrap();
        $this->refreshComments();
    }

    public function submit(): void
    {
        $commentable = $this->resolveCommentable();
        if (! $commentable instanceof Model) {
            return;
        }

        $this->assertNotRateLimited($commentable);

        CreateCommentAction::run(new CreateCommentData(
            commentable: $commentable,
            body: $this->body,
            siteId: is_numeric(self::optionalAttribute($commentable, 'site_id')) ? (int) self::optionalAttribute($commentable, 'site_id') : null,
            languageId: is_numeric(self::optionalAttribute($commentable, 'language_id')) ? (int) self::optionalAttribute($commentable, 'language_id') : null,
            authorName: $this->authorName,
            authorEmail: $this->authorEmail,
            user: auth()->user() instanceof Model ? auth()->user() : null,
            parentPublicId: $this->parentPublicId,
            ipAddress: request()->ip(),
            userAgent: request()->userAgent(),
            url: request()->fullUrl(),
            honeypot: $this->commentWebsite,
            formRenderedAt: $this->formRenderedAt,
        ));

        $this->submitted = true;
        $this->reset('body', 'authorName', 'authorEmail', 'parentPublicId', 'commentWebsite');
        $this->resetBotTrap();
        $this->refreshComments();
    }

    public function replyTo(string $publicId): void
    {
        $this->parentPublicId = $publicId;
    }

    public function cancelReply(): void
    {
        $this->parentPublicId = null;
    }

    public function loadMoreReplies(string $publicId): void
    {
        if ($publicId === '') {
            return;
        }

        $pageSize = max(1, $this->replyPageSize);
        $currentLimit = max(0, $this->replyLimits[$publicId] ?? $pageSize);
        $this->replyLimits[$publicId] = $currentLimit + $pageSize;
        $this->refreshComments();
    }

    public function render(): View
    {
        return view('capell-comments::livewire.thread');
    }

    private static function optionalAttribute(Model $model, string $key): mixed
    {
        return array_key_exists($key, $model->getAttributes())
            ? $model->getAttribute($key)
            : null;
    }

    private function resetBotTrap(): void
    {
        $this->formRenderedAt = now()->getTimestamp();
    }

    private function refreshComments(): void
    {
        $commentable = $this->resolveCommentable();
        if (! $commentable instanceof Model) {
            $this->comments = [];

            return;
        }

        $this->replyPageSize = $this->resolveReplyPageSize($commentable);

        $thread = ResolvePublicCommentableThreadAction::run(
            commentable: $commentable,
            rootLimit: $this->resolveRootPageSize($commentable),
            replyLimit: $this->replyPageSize,
            replyLimitsByPublicId: $this->replyLimits,
        );
        $this->comments = $thread->comments ?? [];
    }

    private function resolveRootPageSize(Model $commentable): int
    {
        $commentableType = resolve(CommentableRegistry::class)->forModel($commentable);

        if (! $commentableType instanceof CommentableTypeData) {
            return max(1, (int) config('capell-comments.root_page_size', 20));
        }

        return resolve(CommentSettingsResolver::class)->rootPageSize(
            siteId: $commentableType->siteId($commentable),
            commentableType: $commentableType->key,
        );
    }

    private function resolveReplyPageSize(Model $commentable): int
    {
        $commentableType = resolve(CommentableRegistry::class)->forModel($commentable);

        if (! $commentableType instanceof CommentableTypeData) {
            return max(1, (int) config('capell-comments.reply_page_size', 5));
        }

        return max(1, resolve(CommentSettingsResolver::class)->replyPageSize(
            siteId: $commentableType->siteId($commentable),
            commentableType: $commentableType->key,
        ));
    }

    private function resolveCommentable(): ?Model
    {
        if ($this->threadKey === '') {
            return null;
        }

        try {
            $payload = json_decode(Crypt::decryptString($this->threadKey), true, flags: JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return null;
        }

        $type = $payload['type'] ?? null;
        $id = $payload['id'] ?? null;
        $siteId = $payload['site_id'] ?? null;
        $languageId = $payload['language_id'] ?? null;

        if (! is_string($type) || ! is_numeric($id)) {
            return null;
        }

        $class = Relation::getMorphedModel($type) ?? $type;
        if (! is_string($class) || ! class_exists($class) || ! is_a($class, Model::class, true)) {
            return null;
        }

        /** @var Model|null $model */
        $model = $class::query()->find((int) $id);

        if (! $model instanceof Model) {
            return null;
        }

        if (is_numeric($siteId) && (int) self::optionalAttribute($model, 'site_id') !== (int) $siteId) {
            return null;
        }

        if (is_numeric($languageId) && (int) self::optionalAttribute($model, 'language_id') !== (int) $languageId) {
            return null;
        }

        return $model;
    }

    /**
     * @throws ValidationException
     */
    private function assertNotRateLimited(Model $commentable): void
    {
        $key = 'capell-comments:submit:' . hash('sha256', implode('|', [
            (string) $commentable->getMorphClass(),
            (string) $commentable->getKey(),
            (string) request()->ip(),
        ]));

        $maxAttempts = (int) config('capell-comments.throttle.max_attempts', 6);
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            throw ValidationException::withMessages([
                'body' => __('capell-comments::messages.too_many_comments'),
            ]);
        }

        RateLimiter::hit($key, (int) config('capell-comments.throttle.decay_seconds', 60));
    }
}
