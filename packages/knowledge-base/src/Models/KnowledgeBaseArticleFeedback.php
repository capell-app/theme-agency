<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Models;

use Capell\KnowledgeBase\Database\Factories\KnowledgeBaseArticleFeedbackFactory;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property int $article_id
 * @property int|null $article_version_id
 * @property bool $helpful
 * @property string|null $comment
 * @property string|null $visitor_hash
 * @property string|null $user_agent_hash
 * @property CarbonImmutable $submitted_at
 *
 * @method static KnowledgeBaseArticleFeedbackFactory factory($count = null, $state = [])
 */
final class KnowledgeBaseArticleFeedback extends Model
{
    /** @use HasFactory<KnowledgeBaseArticleFeedbackFactory> */
    use HasFactory;

    protected static string $factory = KnowledgeBaseArticleFeedbackFactory::class;

    protected $table = 'knowledge_base_article_feedback';

    /** @var list<string> */
    protected $fillable = [
        'article_id',
        'article_version_id',
        'helpful',
        'comment',
        'visitor_hash',
        'user_agent_hash',
        'submitted_at',
    ];

    /**
     * @return BelongsTo<KnowledgeBaseArticle, $this>
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(KnowledgeBaseArticle::class, 'article_id');
    }

    /**
     * @return BelongsTo<KnowledgeBaseArticleVersion, $this>
     */
    public function articleVersion(): BelongsTo
    {
        return $this->belongsTo(KnowledgeBaseArticleVersion::class, 'article_version_id');
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'helpful' => 'bool',
            'submitted_at' => 'immutable_datetime',
        ];
    }
}
