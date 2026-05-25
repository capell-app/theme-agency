<?php

declare(strict_types=1);

namespace Capell\Comments\Models;

use Capell\Core\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

class CommentModerationEvent extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    protected $table = 'comment_moderation_events';

    /** @var list<string> */
    protected $fillable = [
        'site_id',
        'comment_id',
        'comment_author_id',
        'moderator_id',
        'action',
        'previous_status',
        'new_status',
        'note',
        'occurred_at',
    ];

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * @return BelongsTo<Comment, $this>
     */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    /**
     * @return BelongsTo<CommentAuthor, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(CommentAuthor::class, 'comment_author_id');
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'occurred_at' => 'immutable_datetime',
        ];
    }
}
