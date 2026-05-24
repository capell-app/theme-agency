<?php

declare(strict_types=1);

namespace Capell\Comments\Livewire;

use Capell\Comments\Actions\BuildPublicThreadAction;
use Capell\Comments\Actions\CreateCommentAction;
use Capell\Comments\Data\CreateCommentData;
use Capell\Comments\Data\PublicCommentData;
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

    public bool $submitted = false;

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
            'site_id' => $commentable->getAttribute('site_id'),
            'language_id' => $commentable->getAttribute('language_id'),
        ], JSON_THROW_ON_ERROR));
    }

    public function mount(?string $threadKey = null): void
    {
        $this->threadKey = is_string($threadKey) ? $threadKey : '';
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
            siteId: is_numeric($commentable->getAttribute('site_id')) ? (int) $commentable->getAttribute('site_id') : null,
            languageId: is_numeric($commentable->getAttribute('language_id')) ? (int) $commentable->getAttribute('language_id') : null,
            authorName: $this->authorName,
            authorEmail: $this->authorEmail,
            user: auth()->user() instanceof Model ? auth()->user() : null,
            parentPublicId: $this->parentPublicId,
            ipAddress: request()->ip(),
            userAgent: request()->userAgent(),
            url: request()->fullUrl(),
        ));

        $this->submitted = true;
        $this->reset('body', 'authorName', 'authorEmail', 'parentPublicId');
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

    public function render(): View
    {
        return view('capell-comments::livewire.thread');
    }

    private function refreshComments(): void
    {
        $commentable = $this->resolveCommentable();
        if (! $commentable instanceof Model) {
            $this->comments = [];

            return;
        }

        $this->comments = BuildPublicThreadAction::run(
            commentable: $commentable,
            rootLimit: (int) config('capell-comments.root_page_size', 20),
        );
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

        if (! is_string($type) || ! is_numeric($id)) {
            return null;
        }

        $class = Relation::getMorphedModel($type) ?? $type;
        if (! is_string($class) || ! class_exists($class) || ! is_a($class, Model::class, true)) {
            return null;
        }

        /** @var Model|null $model */
        $model = $class::query()->find((int) $id);

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
            (string) $this->authorEmail,
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
