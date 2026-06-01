<?php

declare(strict_types=1);

namespace Capell\Contacts\Models;

use Capell\Contacts\Enums\ContactActivityType;
use Capell\Core\Models\Site;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Override;

/**
 * @property ContactActivityType|null $type
 * @property string|null $summary
 * @property array<string, mixed>|null $payload
 * @property CarbonImmutable|null $occurred_at
 */
class ContactActivity extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'site_id',
        'contact_id',
        'organisation_id',
        'lead_id',
        'subject_type',
        'subject_id',
        'type',
        'summary',
        'payload',
        'occurred_at',
    ];

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-contacts.tables.activities');

        return is_string($tableName) ? $tableName : 'contact_activities';
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
     * @return BelongsTo<Lead, $this>
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'type' => ContactActivityType::class,
            'summary' => 'encrypted',
            'payload' => 'encrypted:array',
            'occurred_at' => 'immutable_datetime',
        ];
    }
}
