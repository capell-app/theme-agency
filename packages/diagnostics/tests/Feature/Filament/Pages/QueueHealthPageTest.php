<?php

declare(strict_types=1);

use Capell\Diagnostics\Filament\Pages\QueueHealthPage;
use Capell\Diagnostics\Models\FailedJob;
use Capell\Diagnostics\Models\PendingQueueJob;
use Capell\Diagnostics\Models\QueueMonitor;
use Capell\Tests\Support\Concerns\CreatesAdminUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(CreatesAdminUser::class);

beforeEach(function (): void {
    Role::findOrCreate(config('capell.roles.super_admin', 'super_admin'));
    Permission::findOrCreate('View:QueueHealthPage');
    Permission::findOrCreate('accessDiagnostics');
    Permission::findOrCreate('viewDiagnostics');
    Permission::findOrCreate('Manage:QueueHealthPage');
});

it('does not allow generic queue health page permission to view failed job details', function (): void {
    $user = $this->createUserWithPermission('View:QueueHealthPage');

    $this->actingAs($user);

    expect(QueueHealthPage::canAccess())->toBeFalse();
});

it('allows super admins to view queue health', function (): void {
    $user = $this->createUserWithRole(config('capell.roles.super_admin', 'super_admin'));

    $this->actingAs($user);

    expect(QueueHealthPage::canAccess())->toBeTrue();
});

it('renders queue operations for super admins', function (): void {
    $user = $this->createUserWithRole(config('capell.roles.super_admin', 'super_admin'));

    $this->actingAs($user);

    Livewire::test(QueueHealthPage::class)->assertOk();
});

it('allows users with developer tools access to view queue health', function (): void {
    $user = $this->createUserWithPermission('accessDiagnostics');

    $this->actingAs($user);

    expect(QueueHealthPage::canAccess())->toBeTrue();
});

it('allows users with queue management access to view queue health', function (): void {
    $user = $this->createUserWithPermission('Manage:QueueHealthPage');

    $this->actingAs($user);

    expect(QueueHealthPage::canAccess())->toBeTrue();
});

it('only exposes pending jobs tab when the database queue table is available', function (): void {
    config(['queue.default' => 'sync']);

    expect((new QueueHealthPage)->shouldShowPendingJobsTab())->toBeFalse();

    config(['queue.default' => 'database']);

    Schema::dropIfExists('jobs');

    expect((new QueueHealthPage)->shouldShowPendingJobsTab())->toBeFalse();

    Schema::create('jobs', function (Blueprint $table): void {
        $table->id();
        $table->string('queue')->index();
        $table->longText('payload');
        $table->unsignedTinyInteger('attempts');
        $table->unsignedInteger('reserved_at')->nullable();
        $table->unsignedInteger('available_at');
        $table->unsignedInteger('created_at');
    });

    expect((new QueueHealthPage)->shouldShowPendingJobsTab())->toBeTrue();
});

it('filters pending database queue jobs by unix timestamp date ranges', function (): void {
    config(['queue.default' => 'database']);

    Schema::dropIfExists('jobs');
    Schema::create('jobs', function (Blueprint $table): void {
        $table->id();
        $table->string('queue')->index();
        $table->longText('payload');
        $table->unsignedTinyInteger('attempts');
        $table->unsignedInteger('reserved_at')->nullable();
        $table->unsignedInteger('available_at');
        $table->unsignedInteger('created_at');
    });

    DB::table('jobs')->insert([
        'queue' => 'default',
        'payload' => json_encode(['displayName' => 'VisiblePendingJob'], JSON_THROW_ON_ERROR),
        'attempts' => 0,
        'reserved_at' => null,
        'available_at' => now()->timestamp,
        'created_at' => now()->setTime(10, 0)->timestamp,
    ]);

    DB::table('jobs')->insert([
        'queue' => 'default',
        'payload' => json_encode(['displayName' => 'HiddenPendingJob'], JSON_THROW_ON_ERROR),
        'attempts' => 0,
        'reserved_at' => null,
        'available_at' => now()->subDay()->timestamp,
        'created_at' => now()->subDay()->timestamp,
    ]);

    $visibleJob = PendingQueueJob::query()->where('payload', 'like', '%VisiblePendingJob%')->firstOrFail();
    $hiddenJob = PendingQueueJob::query()->where('payload', 'like', '%HiddenPendingJob%')->firstOrFail();

    $user = $this->createUserWithRole(config('capell.roles.super_admin', 'super_admin'));

    $this->actingAs($user);

    Livewire::test(QueueHealthPage::class)
        ->set('activeTab', 'pending')
        ->filterTable('date_range', [
            'from' => now()->toDateString(),
            'until' => now()->toDateString(),
        ])
        ->assertCanSeeTableRecords([$visibleJob])
        ->assertCanNotSeeTableRecords([$hiddenJob]);
});

it('filters tracked queue history by status queue date and job class', function (): void {
    $succeededMonitor = QueueMonitor::query()->create([
        'job_id' => 'history-succeeded',
        'name' => 'ImportSiteJob',
        'queue' => 'imports',
        'started_at' => now()->subDays(2),
        'finished_at' => now()->subDays(2)->addMinutes(3),
        'failed' => false,
        'attempt' => 1,
        'progress' => 100,
        'created_at' => now()->subDays(2),
        'updated_at' => now()->subDays(2),
    ]);
    $failedMonitor = QueueMonitor::query()->create([
        'job_id' => 'history-failed',
        'name' => 'SendNewsletterJob',
        'queue' => 'mail',
        'started_at' => now()->subDay(),
        'finished_at' => now()->subDay()->addMinute(),
        'failed' => true,
        'attempt' => 2,
        'progress' => 80,
        'exception_message' => 'SMTP rejected the recipient.',
        'created_at' => now()->subDay(),
        'updated_at' => now()->subDay(),
    ]);
    $runningMonitor = QueueMonitor::query()->create([
        'job_id' => 'history-running',
        'name' => 'RefreshSearchIndexJob',
        'queue' => 'search',
        'started_at' => now(),
        'finished_at' => null,
        'failed' => false,
        'attempt' => 1,
        'progress' => 25,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $user = $this->createUserWithRole(config('capell.roles.super_admin', 'super_admin'));

    $this->actingAs($user);

    Livewire::test(QueueHealthPage::class)
        ->filterTable('status', 'failed')
        ->assertCanSeeTableRecords([$failedMonitor])
        ->assertCanNotSeeTableRecords([$succeededMonitor, $runningMonitor]);

    Livewire::test(QueueHealthPage::class)
        ->filterTable('queue', 'imports')
        ->assertCanSeeTableRecords([$succeededMonitor])
        ->assertCanNotSeeTableRecords([$failedMonitor, $runningMonitor]);

    Livewire::test(QueueHealthPage::class)
        ->filterTable('job_class', ['name' => 'SearchIndex'])
        ->assertCanSeeTableRecords([$runningMonitor])
        ->assertCanNotSeeTableRecords([$succeededMonitor, $failedMonitor]);

    Livewire::test(QueueHealthPage::class)
        ->filterTable('date_range', [
            'from' => now()->subDay()->toDateString(),
            'until' => now()->subDay()->toDateString(),
        ])
        ->assertCanSeeTableRecords([$failedMonitor])
        ->assertCanNotSeeTableRecords([$succeededMonitor, $runningMonitor]);
});

it('filters failed queue jobs by status date and payload job class', function (): void {
    $failedJobSchema = Schema::connection((new FailedJob)->getConnectionName());
    $failedJobSchema->dropIfExists('failed_jobs');
    $failedJobSchema->create('failed_jobs', function (Blueprint $table): void {
        $table->id();
        $table->string('uuid')->unique();
        $table->text('connection');
        $table->text('queue');
        $table->longText('payload');
        $table->longText('exception');
        $table->timestamp('failed_at')->useCurrent();
    });

    $visibleFailedJob = FailedJob::query()->create([
        'uuid' => 'visible-failed-job',
        'connection' => 'database',
        'queue' => 'imports',
        'payload' => json_encode(['displayName' => 'ImportSiteJob'], JSON_THROW_ON_ERROR),
        'exception' => 'Import failed',
        'failed_at' => now()->subDay(),
    ]);
    $hiddenFailedJob = FailedJob::query()->create([
        'uuid' => 'hidden-failed-job',
        'connection' => 'database',
        'queue' => 'mail',
        'payload' => json_encode(['displayName' => 'SendNewsletterJob'], JSON_THROW_ON_ERROR),
        'exception' => 'Mail failed',
        'failed_at' => now()->subDays(3),
    ]);

    $user = $this->createUserWithRole(config('capell.roles.super_admin', 'super_admin'));

    $this->actingAs($user);

    Livewire::test(QueueHealthPage::class)
        ->set('activeTab', 'failed')
        ->filterTable('status', 'failed')
        ->filterTable('job_class', ['name' => 'ImportSite'])
        ->filterTable('date_range', [
            'from' => now()->subDay()->toDateString(),
            'until' => now()->subDay()->toDateString(),
        ])
        ->assertCanSeeTableRecords([$visibleFailedJob])
        ->assertCanNotSeeTableRecords([$hiddenFailedJob]);

    Livewire::test(QueueHealthPage::class)
        ->set('activeTab', 'failed')
        ->filterTable('status', 'pending')
        ->assertCanNotSeeTableRecords([$visibleFailedJob, $hiddenFailedJob]);
});

it('filters pending database queue jobs by processing status and payload job class', function (): void {
    config(['queue.default' => 'database']);
    config(['capell-diagnostics.queue_monitor.queues' => ['imports', 'mail', 'search']]);

    Schema::dropIfExists('jobs');
    Schema::create('jobs', function (Blueprint $table): void {
        $table->id();
        $table->string('queue')->index();
        $table->longText('payload');
        $table->unsignedTinyInteger('attempts');
        $table->unsignedInteger('reserved_at')->nullable();
        $table->unsignedInteger('available_at');
        $table->unsignedInteger('created_at');
    });

    DB::table('jobs')->insert([
        'queue' => 'imports',
        'payload' => json_encode(['displayName' => 'ProcessImportJob'], JSON_THROW_ON_ERROR),
        'attempts' => 1,
        'reserved_at' => now()->timestamp,
        'available_at' => now()->timestamp,
        'created_at' => now()->timestamp,
    ]);
    DB::table('jobs')->insert([
        'queue' => 'mail',
        'payload' => json_encode(['displayName' => 'SendDigestJob'], JSON_THROW_ON_ERROR),
        'attempts' => 0,
        'reserved_at' => null,
        'available_at' => now()->addHour()->timestamp,
        'created_at' => now()->timestamp,
    ]);
    DB::table('jobs')->insert([
        'queue' => 'search',
        'payload' => json_encode(['displayName' => 'RefreshIndexJob'], JSON_THROW_ON_ERROR),
        'attempts' => 0,
        'reserved_at' => null,
        'available_at' => now()->subMinute()->timestamp,
        'created_at' => now()->timestamp,
    ]);

    $user = $this->createUserWithRole(config('capell.roles.super_admin', 'super_admin'));

    $this->actingAs($user);

    Livewire::test(QueueHealthPage::class)
        ->set('activeTab', 'pending')
        ->filterTable('status', 'processing')
        ->filterTable('job_class', ['name' => 'ProcessImport'])
        ->assertSee('ProcessImportJob')
        ->assertDontSee('SendDigestJob')
        ->assertDontSee('RefreshIndexJob');

    Livewire::test(QueueHealthPage::class)
        ->set('activeTab', 'pending')
        ->filterTable('status', 'delayed')
        ->assertSee('SendDigestJob')
        ->assertDontSee('ProcessImportJob')
        ->assertDontSee('RefreshIndexJob');

    Livewire::test(QueueHealthPage::class)
        ->set('activeTab', 'pending')
        ->filterTable('status', 'pending')
        ->assertSee('RefreshIndexJob')
        ->assertDontSee('ProcessImportJob')
        ->assertDontSee('SendDigestJob');
});

it('only exposes bulk retry on the failed jobs tab', function (): void {
    $user = $this->createUserWithRole(config('capell.roles.super_admin', 'super_admin'));

    $this->actingAs($user);

    Livewire::test(QueueHealthPage::class)
        ->assertTableBulkActionHidden('retry_failed_jobs')
        ->set('activeTab', 'failed')
        ->assertTableBulkActionVisible('retry_failed_jobs');
});

it('hides queue mutation actions from read-only diagnostics viewers', function (): void {
    config(['queue.default' => 'database']);
    config(['capell-diagnostics.queue_monitor.queues' => ['default']]);

    $failedJobSchema = Schema::connection((new FailedJob)->getConnectionName());
    $failedJobSchema->dropIfExists('failed_jobs');
    $failedJobSchema->create('failed_jobs', function (Blueprint $table): void {
        $table->id();
        $table->string('uuid')->unique();
        $table->text('connection');
        $table->text('queue');
        $table->longText('payload');
        $table->longText('exception');
        $table->timestamp('failed_at')->useCurrent();
    });

    Schema::dropIfExists('jobs');
    Schema::create('jobs', function (Blueprint $table): void {
        $table->id();
        $table->string('queue')->index();
        $table->longText('payload');
        $table->unsignedTinyInteger('attempts');
        $table->unsignedInteger('reserved_at')->nullable();
        $table->unsignedInteger('available_at');
        $table->unsignedInteger('created_at');
    });

    $failedJob = FailedJob::query()->create([
        'uuid' => 'read-only-failed-job',
        'connection' => 'database',
        'queue' => 'default',
        'payload' => json_encode(['displayName' => 'ReadOnlyFailedJob'], JSON_THROW_ON_ERROR),
        'exception' => 'Failed',
        'failed_at' => now(),
    ]);
    $pendingJob = PendingQueueJob::query()->create([
        'queue' => 'default',
        'payload' => json_encode(['displayName' => 'ReadOnlyPendingJob'], JSON_THROW_ON_ERROR),
        'attempts' => 0,
        'reserved_at' => null,
        'available_at' => now()->timestamp,
        'created_at' => now()->timestamp,
    ]);

    $user = $this->createUserWithPermission('viewDiagnostics');

    $this->actingAs($user);

    expect(QueueHealthPage::canAccess())->toBeTrue();

    Livewire::test(QueueHealthPage::class)
        ->set('activeTab', 'failed')
        ->assertTableActionHidden('retry', record: (string) $failedJob->getKey())
        ->assertTableBulkActionHidden('retry_failed_jobs');

    Livewire::test(QueueHealthPage::class)
        ->set('activeTab', 'pending')
        ->assertTableActionHidden('delete_pending', record: (string) $pendingJob->getKey());
});
