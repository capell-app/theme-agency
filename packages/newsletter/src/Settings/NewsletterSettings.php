<?php

declare(strict_types=1);

namespace Capell\Newsletter\Settings;

use Capell\Core\Contracts\SettingsContract;
use Capell\Core\Contracts\SettingsSchemaContract;
use Capell\Newsletter\Enums\ResubscribePolicy;
use Capell\Newsletter\Filament\Settings\NewsletterSettingsSchema;
use Spatie\LaravelSettings\Settings;

class NewsletterSettings extends Settings implements SettingsContract, SettingsSchemaContract
{
    public string $default_resubscribe_policy = ResubscribePolicy::RequireDoubleOptIn->value;

    /** @var array<int|string, string> */
    public array $site_resubscribe_policies = [];

    public static function group(): string
    {
        return 'newsletter';
    }

    public static function schema(): string
    {
        return NewsletterSettingsSchema::class;
    }
}
