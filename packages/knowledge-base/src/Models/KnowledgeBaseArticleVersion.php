<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Models;

use Capell\KnowledgeBase\Database\Factories\KnowledgeBaseArticleVersionFactory;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Override;

/**
 * @property int $id
 * @property int $article_id
 * @property string $version
 * @property string $title
 * @property string|null $summary
 * @property string $body
 * @property string|null $author_type
 * @property int|null $author_id
 * @property CarbonImmutable|null $published_at
 *
 * @method static KnowledgeBaseArticleVersionFactory factory($count = null, $state = [])
 */
final class KnowledgeBaseArticleVersion extends Model
{
    /** @use HasFactory<KnowledgeBaseArticleVersionFactory> */
    use HasFactory;

    protected static string $factory = KnowledgeBaseArticleVersionFactory::class;

    protected $table = 'knowledge_base_article_versions';

    /** @var list<string> */
    protected $fillable = [
        'article_id',
        'version',
        'title',
        'summary',
        'body',
        'author_type',
        'author_id',
        'published_at',
    ];

    /**
     * @return BelongsTo<KnowledgeBaseArticle, $this>
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(KnowledgeBaseArticle::class, 'article_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function author(): MorphTo
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
            'published_at' => 'immutable_datetime',
        ];
    }
}
