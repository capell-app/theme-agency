<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Models;

use Capell\EmailStudio\Database\Factories\EmailTemplateVariantFactory;
use Capell\EmailStudio\Enums\EmailVariantStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property int|null $site_id
 * @property string $site_scope_key
 * @property int $email_template_id
 * @property int|null $email_profile_id
 * @property int|null $email_template_theme_id
 * @property string|null $locale
 * @property EmailVariantStatus $status
 * @property int $version
 * @property string $subject
 * @property string|null $preview_text
 * @property array<int, array{email: string, name?: string|null}>|null $cc
 * @property array<int, array{email: string, name?: string|null}>|null $bcc
 * @property string $html_body
 * @property string|null $text_body
 * @property CarbonImmutable|null $approved_at
 * @property int|null $approved_by
 * @property EmailTemplate|null $template
 * @property EmailTemplateTheme|null $theme
 */
class EmailTemplateVariant extends Model
{
    /** @use HasFactory<EmailTemplateVariantFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'site_id',
        'site_scope_key',
        'email_template_id',
        'email_profile_id',
        'email_template_theme_id',
        'locale',
        'status',
        'version',
        'subject',
        'preview_text',
        'cc',
        'bcc',
        'html_body',
        'text_body',
        'approved_at',
        'approved_by',
    ];

    protected static string $factory = EmailTemplateVariantFactory::class;

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-email-studio.tables.template_variants');

        return is_string($tableName) ? $tableName : 'email_template_variants';
    }

    /**
     * @return BelongsTo<EmailTemplate, $this>
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }

    /**
     * @return BelongsTo<EmailProfile, $this>
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(EmailProfile::class, 'email_profile_id');
    }

    /**
     * @return BelongsTo<EmailTemplateTheme, $this>
     */
    public function theme(): BelongsTo
    {
        return $this->belongsTo(EmailTemplateTheme::class, 'email_template_theme_id');
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
            'status' => EmailVariantStatus::class,
            'cc' => 'array',
            'bcc' => 'array',
            'approved_at' => 'immutable_datetime',
        ];
    }
}
