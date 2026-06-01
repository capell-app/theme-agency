<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Models;

use Capell\KnowledgeBase\Database\Factories\KnowledgeBaseCollectionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property int $id
 * @property int|null $parent_id
 * @property string $key
 * @property string $title
 * @property string $slug
 * @property string|null $description
 * @property int $sort_order
 * @property bool $is_public
 *
 * @method static KnowledgeBaseCollectionFactory factory($count = null, $state = [])
 */
final class KnowledgeBaseCollection extends Model
{
    /** @use HasFactory<KnowledgeBaseCollectionFactory> */
    use HasFactory;

    protected static string $factory = KnowledgeBaseCollectionFactory::class;

    protected $table = 'knowledge_base_collections';

    /** @var list<string> */
    protected $fillable = [
        'parent_id',
        'key',
        'title',
        'slug',
        'description',
        'sort_order',
        'is_public',
    ];

    /**
     * @return BelongsTo<KnowledgeBaseCollection, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<KnowledgeBaseCollection, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * @return HasMany<KnowledgeBaseArticle, $this>
     */
    public function articles(): HasMany
    {
        return $this->hasMany(KnowledgeBaseArticle::class, 'collection_id');
    }

    /**
     * @param  Builder<KnowledgeBaseCollection>  $query
     * @return Builder<KnowledgeBaseCollection>
     */
    protected function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'is_public' => 'bool',
            'sort_order' => 'int',
        ];
    }
}
