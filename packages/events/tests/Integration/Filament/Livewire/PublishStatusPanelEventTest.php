<?php

declare(strict_types=1);

use Capell\Admin\Filament\Livewire\PublishStatusPanel;
use Capell\Events\Models\Event;
use Capell\Tests\Support\Concerns\CreatesAdminUser;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

uses(CreatesAdminUser::class)->group('events');

beforeEach(function (): void {
    test()->actingAsAdmin();
});

function eventPanel(Event $event): Testable
{
    return Livewire::test(PublishStatusPanel::class, [
        'recordClass' => Event::class,
        'recordId' => $event->getKey(),
    ]);
}

it('shows the publish controls for a live event', function (): void {
    $event = Event::factory()->create([
        'visible_from' => now()->subDay(),
        'visible_until' => null,
    ]);

    eventPanel($event)
        ->assertOk()
        ->assertActionVisible('unpublish')
        ->assertActionHidden('toggleStatus');
});

it('publishes a draft event immediately via the panel', function (): void {
    $event = Event::factory()->create([
        'visible_from' => now()->addYears(100),
        'visible_until' => null,
    ]);

    eventPanel($event)->callAction('publishNow');

    expect($event->fresh()->isPending())->toBeFalse();
});

it('unpublishes a live event via the panel', function (): void {
    $event = Event::factory()->create([
        'visible_from' => now()->subDay(),
        'visible_until' => null,
    ]);

    eventPanel($event)->callAction('unpublish');

    expect($event->fresh()->isExpired())->toBeTrue();
});
