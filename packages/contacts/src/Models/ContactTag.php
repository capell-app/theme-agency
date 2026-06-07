<?php

declare(strict_types=1);

namespace Capell\Contacts\Models;

use Capell\Core\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Override;

/**
 * @property int|null $site_id
 * @property string $name
 * @property string $slug
 */
final class ContactTag extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'site_id',
        'name',
        'slug',
    ];

    public static function slugFor(string $tag): string
    {
        return Str::of($tag)
            ->trim()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '-')
            ->trim('-')
            ->toString();
    }

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-contacts.tables.contact_tags');

        return is_string($tableName) ? $tableName : 'contact_tags';
    }

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * @return BelongsToMany<Contact, $this>
     */
    public function contacts(): BelongsToMany
    {
        $pivotTable = config('capell-contacts.tables.contact_tag_memberships');

        return $this->belongsToMany(
            Contact::class,
            is_string($pivotTable) ? $pivotTable : 'contact_tag_memberships',
            'contact_tag_id',
            'contact_id',
        )->withTimestamps();
    }

    #[Override]
    protected static function booted(): void
    {
        self::saving(function (ContactTag $tag): void {
            $tag->name = trim($tag->name);
            $tag->slug = self::slugFor($tag->slug !== '' ? $tag->slug : $tag->name);
        });
    }
}
