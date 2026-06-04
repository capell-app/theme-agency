<?php

declare(strict_types=1);

namespace Capell\Comments\Models;

use Capell\Comments\Enums\CommentReactionType;
use Capell\Core\Models\Site;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Override;

/**
 * @property int $site_id
 * @property int $comment_id
 * @property string|null $visitor_ip_hash
 * @property string|null $visitor_user_agent_hash
 * @property CommentReactionType $type
 */
class CommentReaction extends Model
{
    protected $table = 'comment_reactions';

    /** @var list<string> */
    protected $fillable = [
        'site_id',
        'comment_id',
        'user_type',
        'user_id',
        'visitor_ip_hash',
        'visitor_user_agent_hash',
        'type',
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
     * @return MorphTo<Model, $this>
     */
    public function user(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'type' => CommentReactionType::class,
        ];
    }
}
