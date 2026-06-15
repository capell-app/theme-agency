<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property int $id
 * @property int|null $site_id
 * @property string $name
 * @property string|null $email
 * @property string|null $phone
 * @property array<int, string>|null $roles
 * @property array<string, mixed>|null $availability
 * @property bool $active
 * @property-read Collection<int, EquestrianHorseCareTask> $careTasks
 */
final class EquestrianStaffMember extends Model
{
    protected $table = 'equestrian_staff_members';

    protected $guarded = [];

    /**
     * @return HasMany<EquestrianHorseCareTask, $this>
     */
    public function careTasks(): HasMany
    {
        return $this->hasMany(EquestrianHorseCareTask::class, 'assigned_staff_member_id');
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles ?? [], true);
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'availability' => 'json',
            'roles' => 'json',
        ];
    }
}
