<?php

declare(strict_types=1);

use Capell\Admin\Support\Extensions\ExtensionManagementSurfaceRegistry;
use Capell\Core\Support\Settings\SettingsSchemaRegistry;
use Capell\SeoSuite\Filament\Settings\SeoSettingsSchema;
use Capell\SeoSuite\Handlers\ClearCircuitBreakerHandler;
use Capell\SeoSuite\Providers\SeoSuiteServiceProvider;
use Capell\SeoSuite\Settings\SeoSuiteSettings;
use Capell\SeoSuite\Targets\FlatJsonTarget;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Schema;

it('autoloads seo tools provider dependencies from their PSR-4 paths', function (): void {
    expect(class_exists(FlatJsonTarget::class))->toBeTrue()
        ->and(class_exists(ClearCircuitBreakerHandler::class))->toBeTrue();
});

it('registers seo suite settings and extension settings surface', function (): void {
    $settingsRegistry = resolve(SettingsSchemaRegistry::class);

    expect($settingsRegistry->getSettingsClass('seo_suite'))->toBe(SeoSuiteSettings::class)
        ->and($settingsRegistry->getSchemas('seo_suite'))->toContain(SeoSettingsSchema::class);

    $surfaces = resolve(ExtensionManagementSurfaceRegistry::class)
        ->surfacesForPackage(SeoSuiteServiceProvider::$packageName);

    expect($surfaces[0]->settingsGroup ?? null)->toBe('seo_suite');
});

it('does not schedule PageSpeed audits unless the API client is configured', function (): void {
    $provider = new SeoSuiteServiceProvider(app());
    $method = new ReflectionMethod(SeoSuiteServiceProvider::class, 'pageSpeedAuditsAreConfigured');

    config([
        'capell-seo-suite.pagespeed.enabled' => true,
        'capell-seo-suite.pagespeed.api_key' => null,
    ]);

    expect($method->invoke($provider))->toBeFalse();

    config([
        'capell-seo-suite.pagespeed.enabled' => true,
        'capell-seo-suite.pagespeed.api_key' => 'test-key',
    ]);

    expect($method->invoke($provider))->toBeTrue();
});

it('registers scheduled PageSpeed audits with overlap and single-server guards', function (): void {
    if (! Schema::hasTable('settings')) {
        $this->markTestSkipped('Settings table is unavailable in this test harness.');
    }

    config([
        'capell-seo-suite.pagespeed.enabled' => true,
        'capell-seo-suite.pagespeed.api_key' => 'test-key',
    ]);

    $settings = resolve(SeoSuiteSettings::class);
    $settings->pagespeed_audit_enabled = true;
    $settings->pagespeed_weekly_digest_enabled = true;
    $settings->pagespeed_scheduled_limit = 7;

    app()->instance(SeoSuiteSettings::class, $settings);

    $schedule = new Schedule;
    app()->instance(Schedule::class, $schedule);

    $provider = new SeoSuiteServiceProvider(app());
    $method = new ReflectionMethod(SeoSuiteServiceProvider::class, 'registerPageSpeedSchedule');
    $method->invoke($provider);

    $event = collect($schedule->events())
        ->first(fn (mixed $scheduledEvent): bool => str_contains((string) $scheduledEvent->command, 'capell:seo-suite:pagespeed-audit'));

    expect($event instanceof Event)->toBeTrue();
    assert($event instanceof Event);

    expect($event->onOneServer)->toBeTrue()
        ->and($event->withoutOverlapping)->toBeTrue()
        ->and($event->command)->toContain('--limit=7');
});
