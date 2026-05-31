<?php

declare(strict_types=1);

namespace Capell\Contacts\Models;

use Capell\Contacts\Enums\LeadStatus;
use Capell\Core\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Override;

class Lead extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'site_id',
        'contact_id',
        'organisation_id',
        'source_type',
        'source_id',
        'title',
        'status',
        'value_amount',
        'currency',
        'context',
        'captured_at',
        'qualified_at',
        'closed_at',
    ];

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-contacts.tables.leads');

        return is_string($tableName) ? $tableName : 'contact_leads';
    }

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * @return BelongsTo<Contact, $this>
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * @return BelongsTo<Organisation, $this>
     */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return HasMany<ContactActivity, $this>
     */
    public function activities(): HasMany
    {
        return $this->hasMany(ContactActivity::class);
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'status' => LeadStatus::class,
            'value_amount' => 'decimal:2',
            'context' => 'encrypted:array',
            'captured_at' => 'immutable_datetime',
            'qualified_at' => 'immutable_datetime',
            'closed_at' => 'immutable_datetime',
        ];
    }
}
