<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Override;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string|null $user_type
 * @property int|null $user_id
 * @property array<string, mixed> $form_state
 * @property string $prompt
 * @property Authenticatable|null $user
 */
final class CapellAgentBridgeSavedPrompt extends Model
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'form_state',
        'prompt',
    ];

    protected $table = 'capell_agent-bridge_saved_prompts';

    /** @return MorphTo<Model, $this> */
    public function user(): MorphTo
    {
        return $this->morphTo();
    }

    public function belongsToUser(Authenticatable $user): bool
    {
        return $this->user_type === $user->getMorphClass()
            && (string) $this->user_id === (string) $user->getAuthIdentifier();
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    protected function scopeForUser(Builder $query, Authenticatable $user): Builder
    {
        return $query
            ->where('user_type', $user->getMorphClass())
            ->where('user_id', $user->getAuthIdentifier());
    }

    /** @return array<string, string> */
    #[Override]
    protected function casts(): array
    {
        return [
            'form_state' => 'array',
        ];
    }
}
