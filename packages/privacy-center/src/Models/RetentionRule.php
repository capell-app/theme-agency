<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Models;

use Capell\PrivacyCenter\Enums\RetentionAction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property int $id
 * @property int|null $site_id
 * @property string $data_domain
 * @property string|null $record_type
 * @property int $retention_days
 * @property RetentionAction $action
 * @property string|null $legal_basis
 * @property bool $is_active
 * @property array<array-key, mixed>|null $metadata
 */
class RetentionRule extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    /** @var array<string> */
    protected $guarded = [];

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-privacy-center.tables.retention_rules');

        return is_string($tableName) ? $tableName : 'privacy_retention_rules';
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'retention_days' => 'integer',
            'action' => RetentionAction::class,
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }
}
