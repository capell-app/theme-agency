<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Models;

use Capell\EmailStudio\Database\Factories\EmailTemplateThemeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property int|null $site_id
 * @property string $site_scope_key
 * @property string $key
 * @property string $name
 * @property bool $is_default
 * @property string|null $logo_path
 * @property string|null $logo_url
 * @property array<string, string>|null $colors
 * @property string|null $screenshot_path
 */
class EmailTemplateTheme extends Model
{
    /** @use HasFactory<EmailTemplateThemeFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'site_id',
        'site_scope_key',
        'key',
        'name',
        'is_default',
        'logo_path',
        'logo_url',
        'screenshot_path',
        'colors',
    ];

    protected static string $factory = EmailTemplateThemeFactory::class;

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-email-studio.tables.template_themes');

        return is_string($tableName) ? $tableName : 'email_template_themes';
    }

    /**
     * @return HasMany<EmailTemplateVariant, $this>
     */
    public function variants(): HasMany
    {
        return $this->hasMany(EmailTemplateVariant::class);
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'colors' => 'array',
        ];
    }
}
