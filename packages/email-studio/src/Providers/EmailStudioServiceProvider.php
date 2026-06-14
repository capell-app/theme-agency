<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Providers;

use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Capell\Core\Support\Settings\SettingsGroupMetadata;
use Capell\EmailStudio\Actions\ApplyMailTrackerSettingsAction;
use Capell\EmailStudio\Actions\CreateDefaultEmailTemplateThemeAction;
use Capell\EmailStudio\Actions\RegisterAuthEmailTemplatesAction;
use Capell\EmailStudio\Console\Commands\PruneEmailBodiesCommand;
use Capell\EmailStudio\Console\Commands\PurgeTrackedEmailsCommand;
use Capell\EmailStudio\Enums\EmailProviderType;
use Capell\EmailStudio\Filament\Settings\EmailStudioSettingsSchema;
use Capell\EmailStudio\Models\EmailEvent;
use Capell\EmailStudio\Models\EmailMessage;
use Capell\EmailStudio\Models\EmailProfile;
use Capell\EmailStudio\Models\EmailRecipient;
use Capell\EmailStudio\Models\EmailReply;
use Capell\EmailStudio\Models\EmailSuppression;
use Capell\EmailStudio\Models\EmailTemplate;
use Capell\EmailStudio\Models\EmailTemplateRegistration;
use Capell\EmailStudio\Models\EmailTemplateTheme;
use Capell\EmailStudio\Models\EmailTemplateVariant;
use Capell\EmailStudio\Models\EmailTrackingToken;
use Capell\EmailStudio\Models\SentEmail;
use Capell\EmailStudio\Models\SentEmailUrlClicked;
use Capell\EmailStudio\Settings\EmailStudioSettings;
use Capell\EmailStudio\Settings\EmailStudioSettingsMigrationProvider;
use Capell\EmailStudio\Support\EmailProviderRegistry;
use Capell\EmailStudio\Support\EmailTemplateRegistry;
use Capell\EmailStudio\Support\Providers\FakeEmailProviderAdapter;
use Capell\EmailStudio\Support\Providers\PostmarkEmailProviderAdapter;
use Capell\EmailStudio\Support\Providers\SmtpEmailProviderAdapter;
use Filament\Support\Icons\Heroicon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Override;
use Spatie\LaravelPackageTools\Package;

class EmailStudioServiceProvider extends AbstractPackageServiceProvider
{
    public static string $name = 'capell-email-studio';

    public static string $packageName = 'capell-app/email-studio';

    /**
     * @return list<string>
     */
    public static function getSettingMigrations(): array
    {
        return [
            '2026_06_05_000001_create_email_studio_settings',
            '2026_06_13_000001_add_email_template_authoring_settings',
        ];
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasConfigFile('capell-email-studio')
            ->hasTranslations()
            ->hasViews(self::$name)
            ->hasRoute('web')
            ->hasMigrations([
                '2026_05_10_190847_01_create_email_profiles_table',
                '2026_05_10_190847_02_create_email_templates_table',
                '2026_05_10_190847_03_create_email_template_variants_table',
                '2026_05_10_190847_04_create_email_messages_table',
                '2026_05_10_190847_05_create_email_recipients_table',
                '2026_05_10_190847_06_create_email_events_table',
                '2026_05_10_190847_07_create_email_replies_table',
                '2026_05_10_190847_08_create_email_suppressions_table',
                '2026_05_10_190847_09_create_email_template_registrations_table',
                '2026_05_10_190847_10_create_email_tracking_tokens_table',
                '2026_05_21_000001_add_site_foreign_keys_to_email_studio_tables',
                '2026_06_13_000001_add_email_template_authoring_tables',
            ])
            ->hasCommand(PruneEmailBodiesCommand::class)
            ->hasCommand(PurgeTrackedEmailsCommand::class);
    }

    public function registeringPackage(): void
    {
        ApplyMailTrackerSettingsAction::run();
        $this->app->register(AdminServiceProvider::class);
        $this->app->register(FrontendServiceProvider::class);
    }

    public function packageRegistered(): void
    {
        $this->registerSettingsMigrations();

        $this->app->singleton(EmailTemplateRegistry::class);
        $this->app->singleton(EmailProviderRegistry::class, static fn (): EmailProviderRegistry => (new EmailProviderRegistry)
            ->register(EmailProviderType::Fake, new FakeEmailProviderAdapter)
            ->register(EmailProviderType::Smtp, new SmtpEmailProviderAdapter)
            ->register(EmailProviderType::Postmark, new PostmarkEmailProviderAdapter));

        $this->app->booted(function (): void {
            $this->registerRateLimiters();

            if (! $this->isPackageInstalled()) {
                return;
            }

            $this
                ->registerModels()
                ->registerSettings()
                ->registerDefaultTemplateDefinitions()
                ->registerDefaultTheme()
                ->registerProtectedTables();
        });
    }

    public function packageBooted(): void
    {
        if (! $this->isPackageInstalled()) {
            return;
        }

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule): void {
            $schedule->command('capell-email-studio:prune-bodies')
                ->daily()
                ->withoutOverlapping()
                ->onOneServer();

            $schedule->command('capell-email-studio:purge-tracked-emails')
                ->daily()
                ->withoutOverlapping()
                ->onOneServer();
        });

        if ($this->app->runningInConsole()) {
            /** @var EmailStudioSettingsMigrationProvider $provider */
            $provider = $this->app->make(EmailStudioSettingsMigrationProvider::class);

            $this->publishes([
                $provider->path() . '/2026_06_05_000001_create_email_studio_settings.php' => database_path('settings/2026_06_05_000001_create_email_studio_settings.php'),
                $provider->path() . '/2026_06_13_000001_add_email_template_authoring_settings.php' => database_path('settings/2026_06_13_000001_add_email_template_authoring_settings.php'),
            ], 'capell-email-studio-settings');
        }
    }

    #[Override]
    protected function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(self::$packageName);
    }

    private function registerModels(): self
    {
        $this->surface()->models([
            EmailProfile::class,
            EmailTemplate::class,
            EmailTemplateTheme::class,
            EmailTemplateVariant::class,
            EmailMessage::class,
            EmailRecipient::class,
            EmailEvent::class,
            EmailReply::class,
            EmailSuppression::class,
            EmailTemplateRegistration::class,
            EmailTrackingToken::class,
            SentEmail::class,
            SentEmailUrlClicked::class,
        ]);

        return $this;
    }

    private function registerSettings(): self
    {
        $this->surface()->settingsClass(EmailStudioSettings::group(), EmailStudioSettings::class);
        $this->surface()->settingsMetadata(new SettingsGroupMetadata(
            group: EmailStudioSettings::group(),
            label: 'capell-email-studio::settings.title',
            icon: Heroicon::OutlinedEnvelope,
            navigationGroup: 'capell-admin::navigation.group_system',
            navigationSort: 94,
            packageName: self::$packageName,
        ));
        $this->surface()->settingsSchema(EmailStudioSettings::group(), EmailStudioSettingsSchema::class);

        return $this;
    }

    private function registerSettingsMigrations(): self
    {
        $this->app->singleton(EmailStudioSettingsMigrationProvider::class);

        return $this;
    }

    private function registerProtectedTables(): self
    {
        $tables = config('capell-email-studio.tables', []);

        if (! is_array($tables)) {
            return $this;
        }

        foreach ($tables as $tableName) {
            if (! is_string($tableName)) {
                continue;
            }

            if ($tableName === '') {
                continue;
            }

            CapellCore::registerProtectedTable(static fn (): string => $tableName);
        }

        CapellCore::registerProtectedTable(static fn (): string => 'sent_emails');
        CapellCore::registerProtectedTable(static fn (): string => 'sent_emails_url_clicked');
        CapellCore::registerProtectedTable(static fn (): string => 'email_template_themes');

        return $this;
    }

    private function registerDefaultTemplateDefinitions(): self
    {
        RegisterAuthEmailTemplatesAction::run();

        if (
            Schema::hasTable((new EmailTemplateRegistration)->getTable())
            && ! EmailTemplateRegistration::query()->where('template_key', 'auth.verify-email')->exists()
        ) {
            resolve(EmailTemplateRegistry::class)->persist();
        }

        return $this;
    }

    private function registerDefaultTheme(): self
    {
        if (
            Schema::hasTable((new EmailTemplateTheme)->getTable())
            && ! EmailTemplateTheme::query()
                ->where('site_scope_key', 'global')
                ->where('key', 'default')
                ->exists()
        ) {
            CreateDefaultEmailTemplateThemeAction::run();
        }

        return $this;
    }

    private function registerRateLimiters(): self
    {
        RateLimiter::for('capell-email-studio-provider-events', static fn (Request $request): Limit => Limit::perMinute(120)
            ->by(hash('sha256', (string) $request->route('token') . '|' . (string) $request->ip())));

        return $this;
    }
}
