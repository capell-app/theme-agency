<?php

declare(strict_types=1);

use Capell\Core\Models\Page;
use Capell\FilamentPeek\Actions\CreatePagePreviewSnapshotAction;
use Illuminate\Support\Facades\URL;

it('rejects unsigned preview URLs', function (): void {
    $user = $this->createUserWithRole('super_admin');
    $this->actingAs($user);

    $page = Page::factory()->create();
    $snapshot = CreatePagePreviewSnapshotAction::run($page, ['name' => 'Preview'])['snapshot'];

    $this->get('/capell-filament-peek/preview/' . $snapshot->token)->assertForbidden();
});

it('rejects snapshots owned by another user', function (): void {
    $owner = $this->createUserWithRole('super_admin');
    $otherUser = $this->createUserWithRole('super_admin', ['email' => 'other@example.test']);
    $this->actingAs($owner);

    $page = Page::factory()->create();
    $snapshot = CreatePagePreviewSnapshotAction::run($page, ['name' => 'Preview'])['snapshot'];

    $this->actingAs($otherUser);

    $this->get(URL::signedRoute('capell-filament-peek.preview', ['token' => $snapshot->token]))
        ->assertForbidden();
});
