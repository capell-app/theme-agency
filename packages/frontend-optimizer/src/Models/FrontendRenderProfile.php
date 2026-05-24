<?php

declare(strict_types=1);

namespace Capell\FrontendOptimizer\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $hash
 * @property string $scope
 * @property string|null $label
 * @property array<array-key, mixed> $signature
 * @property array<array-key, mixed>|null $manifest
 * @property string|null $critical_css_path
 * @property string $status
 */
class FrontendRenderProfile extends Model
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    protected $fillable = [
        'critical_css_path',
        'generated_at',
        'hash',
        'label',
        'manifest',
        'scope',
        'signature',
        'status',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'manifest' => 'array',
        'signature' => 'array',
    ];

    /**
     * @return HasMany<FrontendOptimizationRun, $this>
     */
    public function runs(): HasMany
    {
        return $this->hasMany(FrontendOptimizationRun::class, 'render_profile_id');
    }
}
