<?php

declare(strict_types=1);

namespace Capell\Comments\Models;

use Capell\Comments\Database\Factories\CommentAuthorFactory;
use Capell\Core\Models\Site;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Override;

/**
 * @property int $site_id
 * @property string $name
 * @property string $email
 * @property string|null $email_hash
 * @property Carbon|null $email_verified_at
 * @property Carbon|null $trusted_at
 * @property Carbon|null $blocked_at
 */
class CommentAuthor extends Model
{
    /** @use HasFactory<CommentAuthorFactory> */
    use HasFactory;

    protected $table = 'comment_authors';

    /** @var list<string> */
    protected $fillable = [
        'site_id',
        'user_type',
        'user_id',
        'name',
        'email',
        'email_hash',
        'email_verified_at',
        'trusted_at',
        'blocked_at',
        'internal_notes',
    ];

    protected static string $factory = CommentAuthorFactory::class;

    public static function emailHash(?string $email): ?string
    {
        if (! is_string($email) || trim($email) === '') {
            return null;
        }

        $secret = config('capell-comments.email_hash_secret') ?: config('app.key');

        return hash_hmac('sha256', Str::lower(trim($email)), (string) $secret);
    }

    public function isEmailVerified(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function isTrusted(): bool
    {
        return $this->trusted_at !== null && $this->blocked_at === null;
    }

    public function isBlocked(): bool
    {
        return $this->blocked_at !== null;
    }

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function user(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return HasMany<Comment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * @return HasMany<CommentToken, $this>
     */
    public function tokens(): HasMany
    {
        return $this->hasMany(CommentToken::class);
    }

    #[Override]
    protected static function booted(): void
    {
        static::saving(function (CommentAuthor $author): void {
            $author->email_hash = self::emailHash($author->email);
        });
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'name' => 'encrypted',
            'email' => 'encrypted',
            'email_verified_at' => 'datetime',
            'trusted_at' => 'datetime',
            'blocked_at' => 'datetime',
        ];
    }
}
