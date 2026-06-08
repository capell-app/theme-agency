<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Models;

use Capell\KnowledgeBase\Database\Factories\KnowledgeBaseArticleFactory;
use Capell\KnowledgeBase\Enums\KnowledgeBaseArticleStatus;
use Capell\KnowledgeBase\Support\KnowledgeBasePublicPath;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property int $id
 * @property int $collection_id
 * @property int|null $current_version_id
 * @property string $title
 * @property string $slug
 * @property string|null $summary
 * @property KnowledgeBaseArticleStatus $status
 * @property int $search_weight
 * @property bool $is_ai_readable
 * @property CarbonImmutable|null $published_at
 * @property CarbonImmutable|null $updated_at
 * @property-read KnowledgeBaseCollection $collection
 * @property-read KnowledgeBaseArticleVersion|null $currentVersion
 *
 * @method static KnowledgeBaseArticleFactory factory($count = null, $state = [])
 */
final class KnowledgeBaseArticle extends Model
{
    /** @use HasFactory<KnowledgeBaseArticleFactory> */
    use HasFactory;

    protected static string $factory = KnowledgeBaseArticleFactory::class;

    protected $table = 'knowledge_base_articles';

    /** @var list<string> */
    protected $fillable = [
        'collection_id',
        'current_version_id',
        'title',
        'slug',
        'summary',
        'status',
        'search_weight',
        'is_ai_readable',
        'published_at',
    ];

    /**
     * @return BelongsTo<KnowledgeBaseCollection, $this>
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(KnowledgeBaseCollection::class, 'collection_id');
    }

    /**
     * @return BelongsTo<KnowledgeBaseArticleVersion, $this>
     */
    public function currentVersion(): BelongsTo
    {
        return $this->belongsTo(KnowledgeBaseArticleVersion::class, 'current_version_id');
    }

    /**
     * @return HasMany<KnowledgeBaseArticleVersion, $this>
     */
    public function versions(): HasMany
    {
        return $this->hasMany(KnowledgeBaseArticleVersion::class, 'article_id');
    }

    /**
     * @return HasMany<KnowledgeBaseArticleFeedback, $this>
     */
    public function feedback(): HasMany
    {
        return $this->hasMany(KnowledgeBaseArticleFeedback::class, 'article_id');
    }

    /**
     * @return HasMany<KnowledgeBaseRelatedArticle, $this>
     */
    public function relatedArticleLinks(): HasMany
    {
        return $this->hasMany(KnowledgeBaseRelatedArticle::class, 'article_id');
    }

    /**
     * @return BelongsToMany<KnowledgeBaseArticle, $this>
     */
    public function relatedArticles(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'knowledge_base_related_articles',
            'article_id',
            'related_article_id',
        )->withPivot(['relation_type', 'sort_order'])->withTimestamps();
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        $this->loadMissing(['collection', 'currentVersion']);

        $collection = $this->collection;
        $currentVersion = $this->currentVersion;

        if (! $collection instanceof KnowledgeBaseCollection || ! $collection->is_public) {
            return [];
        }

        if (! $currentVersion instanceof KnowledgeBaseArticleVersion || ! $this->status->isPubliclyVisible()) {
            return [];
        }

        return [
            'title' => $currentVersion->title,
            'url' => KnowledgeBasePublicPath::forArticle($this),
            'excerpt' => $currentVersion->summary ?? '',
            'body' => strip_tags($currentVersion->body),
            'type' => 'knowledge-base',
            'status' => 'published',
            'is_public' => true,
            'updated_at' => ($currentVersion->published_at ?? $this->published_at ?? $this->updated_at)?->toIso8601String(),
            'meta' => [
                'collection' => $collection->title,
                'version' => $currentVersion->version,
                'weight' => $this->search_weight,
            ],
        ];
    }

    /**
     * @param  Builder<KnowledgeBaseArticle>  $query
     * @return Builder<KnowledgeBaseArticle>
     */
    protected function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', KnowledgeBaseArticleStatus::Published)
            ->whereNotNull('published_at');
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'status' => KnowledgeBaseArticleStatus::class,
            'search_weight' => 'int',
            'is_ai_readable' => 'bool',
            'published_at' => 'immutable_datetime',
        ];
    }
}
