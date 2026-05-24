<?php

declare(strict_types=1);

namespace Capell\Comments\Models;

use Capell\Comments\Enums\CommentTokenType;
use Capell\Core\Models\Site;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

class CommentToken extends Model
{
    protected $table = 'comment_tokens';

    /** @var list<string> */
    protected $fillable = [
        'site_id',
        'comment_author_id',
        'comment_id',
        'type',
        'token_hash',
        'expires_at',
        'consumed_at',
    ];

    public function isUsable(): bool
    {
        if ($this->consumed_at !== null) {
            return false;
        }

        return $this->expires_at === null || $this->expires_at->isFuture();
    }

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * @return BelongsTo<CommentAuthor, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(CommentAuthor::class, 'comment_author_id');
    }

    /**
     * @return BelongsTo<Comment, $this>
     */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'type' => CommentTokenType::class,
            'expires_at' => 'immutable_datetime',
            'consumed_at' => 'immutable_datetime',
        ];
    }
}
