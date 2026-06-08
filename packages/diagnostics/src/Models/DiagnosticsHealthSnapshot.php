<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property int $id
 * @property string $overall_status
 * @property int $health_score
 * @property string|null $worst_severity
 * @property int $declared_count
 * @property int $implemented_count
 * @property int $stub_count
 * @property int $broken_count
 * @property int $executed_count
 * @property int $passed_count
 * @property int $failed_count
 * @property array<int, array<string, mixed>>|null $checks
 * @property CarbonImmutable $recorded_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
final class DiagnosticsHealthSnapshot extends Model
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'overall_status',
        'health_score',
        'worst_severity',
        'declared_count',
        'implemented_count',
        'stub_count',
        'broken_count',
        'executed_count',
        'passed_count',
        'failed_count',
        'checks',
        'recorded_at',
    ];

    #[Override]
    protected function casts(): array
    {
        return [
            'health_score' => 'integer',
            'declared_count' => 'integer',
            'implemented_count' => 'integer',
            'stub_count' => 'integer',
            'broken_count' => 'integer',
            'executed_count' => 'integer',
            'passed_count' => 'integer',
            'failed_count' => 'integer',
            'checks' => 'array',
            'recorded_at' => 'immutable_datetime',
        ];
    }
}
