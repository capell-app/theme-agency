<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Models;

use Capell\EmailStudio\Database\Factories\EmailTemplateFactory;
use Capell\EmailStudio\Enums\EmailTemplateStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property list<string>|null $variables
 */
class EmailTemplate extends Model
{
    /** @use HasFactory<EmailTemplateFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'site_id',
        'site_scope_key',
        'key',
        'name',
        'status',
        'description',
        'variables',
    ];

    protected static string $factory = EmailTemplateFactory::class;

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-email-studio.tables.templates');

        return is_string($tableName) ? $tableName : 'email_templates';
    }

    /**
     * @return HasMany<EmailTemplateVariant, $this>
     */
    public function variants(): HasMany
    {
        return $this->hasMany(EmailTemplateVariant::class);
    }

    /**
     * @return HasMany<EmailMessage, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(EmailMessage::class);
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'status' => EmailTemplateStatus::class,
            'variables' => 'array',
        ];
    }
}
