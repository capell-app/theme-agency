<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Models;

use Capell\KnowledgeBase\Database\Factories\KnowledgeBaseRelatedArticleFactory;
use Capell\KnowledgeBase\Enums\KnowledgeBaseRelatedArticleType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property int $article_id
 * @property int $related_article_id
 * @property KnowledgeBaseRelatedArticleType $relation_type
 * @property int $sort_order
 *
 * @method static KnowledgeBaseRelatedArticleFactory factory($count = null, $state = [])
 */
final class KnowledgeBaseRelatedArticle extends Model
{
    /** @use HasFactory<KnowledgeBaseRelatedArticleFactory> */
    use HasFactory;

    protected static string $factory = KnowledgeBaseRelatedArticleFactory::class;

    protected $table = 'knowledge_base_related_articles';

    /** @var list<string> */
    protected $fillable = [
        'article_id',
        'related_article_id',
        'relation_type',
        'sort_order',
    ];

    /**
     * @return BelongsTo<KnowledgeBaseArticle, $this>
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(KnowledgeBaseArticle::class, 'article_id');
    }

    /**
     * @return BelongsTo<KnowledgeBaseArticle, $this>
     */
    public function relatedArticle(): BelongsTo
    {
        return $this->belongsTo(KnowledgeBaseArticle::class, 'related_article_id');
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'relation_type' => KnowledgeBaseRelatedArticleType::class,
            'sort_order' => 'int',
        ];
    }
}
