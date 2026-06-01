<?php

declare(strict_types=1);

namespace Capell\UrlManager\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

class RedirectHit extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    protected $table = 'url_manager_redirect_hits';

    protected $fillable = [
        'redirect_rule_id',
        'request_url',
        'referer_url',
        'user_agent_hash',
        'ip_hash',
        'hit_at',
    ];

    /**
     * @return BelongsTo<RedirectRule, $this>
     */
    public function redirectRule(): BelongsTo
    {
        return $this->belongsTo(RedirectRule::class, 'redirect_rule_id');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'hit_at' => 'datetime',
        ];
    }
}
