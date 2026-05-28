<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;

it('runs the public actions migrations', function (): void {
    expect(Schema::hasTable('public_actions'))->toBeTrue()
        ->and(Schema::hasTable('public_action_destinations'))->toBeTrue()
        ->and(Schema::hasTable('public_action_submissions'))->toBeTrue()
        ->and(Schema::hasTable('public_action_dispatch_attempts'))->toBeTrue()
        ->and(Schema::hasTable('public_action_integration_tokens'))->toBeTrue();
});

it('creates the columns needed for actions, dispatch, and integration tokens', function (): void {
    expect(Schema::hasColumn('public_actions', 'site_scope_key'))->toBeTrue()
        ->and(Schema::hasColumn('public_actions', 'handler_key'))->toBeTrue()
        ->and(Schema::hasColumn('public_action_destinations', 'endpoint_url'))->toBeTrue()
        ->and(Schema::hasColumn('public_action_submissions', 'payload'))->toBeTrue()
        ->and(Schema::hasColumn('public_action_submissions', 'idempotency_key'))->toBeTrue()
        ->and(Schema::hasColumn('public_action_dispatch_attempts', 'request_hash'))->toBeTrue()
        ->and(Schema::hasColumn('public_action_integration_tokens', 'token_hash'))->toBeTrue()
        ->and(Schema::hasColumn('public_action_integration_tokens', 'abilities'))->toBeTrue();
});
