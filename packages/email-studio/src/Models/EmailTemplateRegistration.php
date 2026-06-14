<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Models;

use Capell\EmailStudio\Database\Factories\EmailTemplateRegistrationFactory;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property int|null $site_id
 * @property string $site_scope_key
 * @property string $template_key
 * @property string $package_name
 * @property string $name
 * @property string|null $description
 * @property list<string> $variables
 * @property string $default_locale
 * @property bool $is_static_renderable
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
class EmailTemplateRegistration extends Model
{
    /** @use HasFactory<EmailTemplateRegistrationFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'site_id',
        'site_scope_key',
        'template_key',
        'package_name',
        'name',
        'description',
        'variables',
        'default_locale',
        'is_static_renderable',
    ];

    protected static string $factory = EmailTemplateRegistrationFactory::class;

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-email-studio.tables.template_registrations');

        return is_string($tableName) ? $tableName : 'email_template_registrations';
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'is_static_renderable' => 'boolean',
        ];
    }
}
