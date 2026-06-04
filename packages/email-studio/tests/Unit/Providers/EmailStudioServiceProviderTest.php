<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\EmailStudio\Console\Commands\PruneEmailBodiesCommand;
use Capell\EmailStudio\Providers\EmailStudioServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

it('registers the email studio package metadata and config', function (): void {
    $package = CapellCore::getPackage(EmailStudioServiceProvider::$packageName);

    expect($package->name)->toBe(EmailStudioServiceProvider::$packageName)
        ->and(config('capell-email-studio.tables.templates'))->toBe('email_templates')
        ->and(config('capell-email-studio.queue'))->toBe('default');
});

it('registers the email body prune command and daily schedule', function (): void {
    $schedule = new Schedule;
    app()->instance(Schedule::class, $schedule);

    $provider = new EmailStudioServiceProvider(app());
    $provider->packageBooted();

    $event = collect($schedule->events())
        ->first(fn (mixed $scheduledEvent): bool => str_contains((string) $scheduledEvent->command, 'capell-email-studio:prune-bodies'));

    expect(class_exists(PruneEmailBodiesCommand::class))->toBeTrue()
        ->and($event)->not->toBeNull()
        ->and($event?->expression)->toBe('0 0 * * *')
        ->and($event?->withoutOverlapping)->toBeTrue()
        ->and($event?->onOneServer)->toBeTrue();
});
