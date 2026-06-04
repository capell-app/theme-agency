<?php

declare(strict_types=1);

use Capell\Comments\Health\CommentsHealthCheck;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Core\Support\Settings\SettingsSchemaRegistry;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

it('reports a compatible capell api version', function (): void {
    expect(CommentsHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('runs real diagnostics returning check results', function (): void {
    $results = CommentsHealthCheck::runDiagnostics();

    expect($results)->toHaveCount(4)
        ->and($results->every(static fn (mixed $result): bool => $result instanceof DoctorCheckResultData))->toBeTrue();
});

it('passes when storage, settings, route, and component wiring are present', function (): void {
    $results = CommentsHealthCheck::runDiagnostics();

    expect(CommentsHealthCheck::passed())->toBeTrue()
        ->and($results->every(static fn (DoctorCheckResultData $result): bool => $result->passed))->toBeTrue();
});

it('fails the storage table check when a comments table is missing', function (): void {
    Schema::drop('comment_tokens');

    $check = new CommentsHealthCheck;

    expect($check->missingTables())->toContain('comment_tokens')
        ->and($check->storageTablesCheck()->passed)->toBeFalse()
        ->and(CommentsHealthCheck::passed())->toBeFalse();
});

it('fails the settings check when the settings registry is missing comments', function (): void {
    app()->instance(SettingsSchemaRegistry::class, new SettingsSchemaRegistry);

    $check = new CommentsHealthCheck;

    expect($check->settingsAreRegistered())->toBeFalse()
        ->and($check->settingsRegistrationCheck()->passed)->toBeFalse();
});

it('fails the route check when the thread route is missing', function (): void {
    Route::getRoutes()->refreshNameLookups();
    Route::getRoutes()->getByName('capell-comments.thread')?->name('capell-comments.thread.missing');
    Route::getRoutes()->refreshNameLookups();

    $check = new CommentsHealthCheck;

    expect($check->threadRouteIsRegistered())->toBeFalse()
        ->and($check->threadRouteCheck()->passed)->toBeFalse();
});

it('confirms the public thread Livewire component is registered', function (): void {
    $check = new CommentsHealthCheck;

    expect($check->threadComponentIsRegistered())->toBeTrue()
        ->and($check->threadComponentCheck()->passed)->toBeTrue();
});
