<?php

declare(strict_types=1);

use Capell\Diagnostics\Filament\Pages\QueueHealthPage;
use Capell\Diagnostics\Models\PendingQueueJob;
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

it('only exposes bulk retry on the failed jobs tab', function (): void {
    $user = $this->createUserWithRole(config('capell.roles.super_admin', 'super_admin'));

    $this->actingAs($user);

    Livewire::test(QueueHealthPage::class)
        ->assertTableBulkActionHidden('retry_failed_jobs')
        ->set('activeTab', 'failed')
        ->assertTableBulkActionVisible('retry_failed_jobs');
});
