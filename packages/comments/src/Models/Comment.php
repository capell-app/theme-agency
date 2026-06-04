<?php

declare(strict_types=1);

namespace Capell\Comments\Models;

use Capell\Comments\Database\Factories\CommentFactory;
use Capell\Comments\Enums\CommentStatus;
use Capell\Core\Models\Language;
use Capell\Core\Models\Site;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Override;

/**
 * @property string $public_id
 * @property int $site_id
 * @property string $commentable_type
 * @property int $commentable_id
 * @property int|null $parent_id
 * @property int|null $root_id
 * @property int $depth
 * @property CommentStatus $status
 * @property string $body
 * @property CarbonImmutable|null $submitted_at
 * @property CarbonImmutable|null $email_verified_at
 */
class Comment extends Model
{
    /** @use HasFactory<CommentFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'comments';

    /** @var list<string> */
    protected $fillable = [
        'public_id',
        'site_id',
        'language_id',
        'comment_author_id',
        'commentable_type',
        'commentable_id',
        'parent_id',
        'root_id',
        'depth',
        'status',
        'body',
        'visitor_ip_hash',
        'visitor_user_agent_hash',
        'link_count',
        'spam_reasons',
        'submitted_at',
        'email_verified_at',
        'approved_at',
        'rejected_at',
        'marked_spam_at',
        'archived_at',
        'moderated_by',
        'moderation_note',
    ];

    protected static string $factory = CommentFactory::class;

    public function isPubliclyVisible(): bool
    {
        return $this->status === CommentStatus::Approved;
    }

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * @return BelongsTo<Language, $this>
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * @return BelongsTo<CommentAuthor, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(CommentAuthor::class, 'comment_author_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<Comment, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return BelongsTo<Comment, $this>
     */
    public function root(): BelongsTo
    {
        return $this->belongsTo(self::class, 'root_id');
    }

    /**
     * @return HasMany<Comment, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * @return HasMany<CommentModerationEvent, $this>
     */
    public function moderationEvents(): HasMany
    {
        return $this->hasMany(CommentModerationEvent::class);
    }

    #[Override]
    protected static function booted(): void
    {
        static::creating(function (Comment $comment): void {
            if (! is_string($comment->public_id) || $comment->public_id === '') {
                $comment->public_id = (string) Str::uuid();
            }

            if (! $comment->submitted_at instanceof CarbonImmutable) {
                $comment->submitted_at = now()->toImmutable();
            }
        });
    }

    /**
     * @param  Builder<Comment>  $query
     * @return Builder<Comment>
     */
    protected function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where('status', CommentStatus::Approved);
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'status' => CommentStatus::class,
            'spam_reasons' => 'array',
            'submitted_at' => 'immutable_datetime',
            'email_verified_at' => 'immutable_datetime',
            'approved_at' => 'immutable_datetime',
            'rejected_at' => 'immutable_datetime',
            'marked_spam_at' => 'immutable_datetime',
            'archived_at' => 'immutable_datetime',
        ];
    }
}
