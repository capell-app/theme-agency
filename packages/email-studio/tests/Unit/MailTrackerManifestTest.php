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
    $manifest = json_decode(
        (string) file_get_contents(dirname(__DIR__, 2) . '/capell.json'),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );
    $screenshotPaths = array_map(
        static fn (array $screenshot): string => dirname(__DIR__, 2) . '/' . $screenshot['path'],
        $manifest['marketplace']['screenshots'],
    );

    expect($manifest['dependencies']['requires'])->toContain('jdavidbakr/mail-tracker')
        ->and($manifest['commands']['mailTrackerPurge'])->toBe(PurgeTrackedEmailsCommand::class)
        ->and($manifest['settings'])->toContain(EmailStudioSettings::class)
        ->and($manifest['contributes'])->toContain([
            'type' => 'admin-resource',
            'class' => SentEmailResourceContribution::class,
            'resourceClass' => SentEmailResource::class,
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'model',
            'class' => SentEmailModelContribution::class,
            'modelClass' => SentEmail::class,
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'setting',
            'class' => EmailStudioSettingsContribution::class,
            'settingsClass' => EmailStudioSettings::class,
            'settingsGroup' => EmailStudioSettings::group(),
        ])
        ->and($manifest['contributes'])->toContain([
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
