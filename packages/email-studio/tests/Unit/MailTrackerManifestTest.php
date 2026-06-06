<?php

declare(strict_types=1);

use Capell\Core\Contracts\Extensions\RegistersExtensionAdminResource;
use Capell\Core\Contracts\Extensions\RegistersExtensionSetting;
use Capell\Core\Contracts\Extensions\RunsScheduledExtensionJob;
use Capell\EmailStudio\Console\Commands\PurgeTrackedEmailsCommand;
use Capell\EmailStudio\Filament\Resources\SentEmails\SentEmailResource;
use Capell\EmailStudio\Manifest\EmailStudioSettingsContribution;
use Capell\EmailStudio\Manifest\SentEmailModelContribution;
use Capell\EmailStudio\Manifest\SentEmailResourceContribution;
use Capell\EmailStudio\Manifest\TrackedEmailPurgeScheduleContribution;
use Capell\EmailStudio\Models\SentEmail;
use Capell\EmailStudio\Settings\EmailStudioSettings;
use Pest\Expectation;

it('declares mail tracker settings admin resource model and purge schedule contributions', function (): void {
    $manifest = capell_json_file_array(dirname(__DIR__, 2) . '/capell.json');
    $screenshots = data_get($manifest, 'marketplace.screenshots', []);

    throw_unless(is_array($screenshots), RuntimeException::class, 'Email Studio screenshots must be an array.');

    $screenshotPaths = array_map(
        static function (mixed $screenshot): string {
            throw_unless(is_array($screenshot), RuntimeException::class, 'Email Studio screenshot entries must be arrays.');

            $path = $screenshot['path'] ?? null;

            throw_unless(is_string($path), RuntimeException::class, 'Email Studio screenshot paths must be strings.');

            return dirname(__DIR__, 2) . '/' . $path;
        },
        $screenshots,
    );

    $composer = capell_json_file_array(dirname(__DIR__, 2) . '/composer.json');

    expect(data_get($manifest, 'dependencies.requires'))->not->toContain('jdavidbakr/mail-tracker')
        ->and(data_get($composer, 'require.jdavidbakr/mail-tracker'))->toBeString()
        ->and(data_get($manifest, 'commands.mailTrackerPurge'))->toBe(PurgeTrackedEmailsCommand::class)
        ->and(data_get($manifest, 'settings'))->toContain(EmailStudioSettings::class)
        ->and(data_get($manifest, 'contributes'))->toContain([
            'type' => 'admin-resource',
            'class' => SentEmailResourceContribution::class,
            'resourceClass' => SentEmailResource::class,
        ])
        ->and(data_get($manifest, 'contributes'))->toContain([
            'type' => 'model',
            'class' => SentEmailModelContribution::class,
            'modelClass' => SentEmail::class,
        ])
        ->and(data_get($manifest, 'contributes'))->toContain([
            'type' => 'setting',
            'class' => EmailStudioSettingsContribution::class,
            'settingsClass' => EmailStudioSettings::class,
            'settingsGroup' => EmailStudioSettings::group(),
        ])
        ->and(data_get($manifest, 'contributes'))->toContain([
            'type' => 'scheduled-job',
            'class' => TrackedEmailPurgeScheduleContribution::class,
            'command' => 'capell-email-studio:purge-tracked-emails',
            'frequency' => 'daily',
        ])
        ->and($screenshotPaths)->each(fn (Expectation $path): Expectation => $path->toBeFile())
        ->and(class_implements(SentEmailResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and(class_implements(EmailStudioSettingsContribution::class))->toContain(RegistersExtensionSetting::class)
        ->and(class_implements(TrackedEmailPurgeScheduleContribution::class))->toContain(RunsScheduledExtensionJob::class);
});
