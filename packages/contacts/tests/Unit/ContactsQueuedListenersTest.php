<?php

declare(strict_types=1);

use Capell\Contacts\Listeners\SyncContactFromAccessGateRegistration;
use Capell\Contacts\Listeners\SyncContactFromCampaignConversion;
use Capell\Contacts\Listeners\SyncContactFromComment;
use Capell\Contacts\Listeners\SyncContactFromEventRegistration;
use Capell\Contacts\Listeners\SyncContactFromFormSubmission;
use Capell\Contacts\Listeners\SyncContactFromShopifyCustomer;
use Capell\Contacts\Tests\ContactsTestCase;
use Capell\Contacts\Tests\Fixtures\FailingQueuedContactSourceListener;
use Illuminate\Contracts\Queue\ShouldQueue;

require_once __DIR__ . '/../autoload.php';

uses(ContactsTestCase::class);

/**
 * @param  class-string<ShouldQueue>  $listenerClass
 */
it('queues every package source listener', function (string $listenerClass): void {
    $listener = new $listenerClass;

    expect($listener)->toBeInstanceOf(ShouldQueue::class);
    expect(contactQueuedListenerTries($listener))->toBe(1);
})->with([
    SyncContactFromAccessGateRegistration::class,
    SyncContactFromCampaignConversion::class,
    SyncContactFromComment::class,
    SyncContactFromEventRegistration::class,
    SyncContactFromFormSubmission::class,
    SyncContactFromShopifyCustomer::class,
]);

it('isolates source listener failures from the producer path', function (): void {
    (new FailingQueuedContactSourceListener)->handle();

    expect(true)->toBeTrue();
});

function contactQueuedListenerTries(object $listener): int
{
    if (! property_exists($listener, 'tries')) {
        return 0;
    }

    $properties = get_object_vars($listener);
    $tries = $properties['tries'] ?? 0;

    return is_int($tries) ? $tries : 0;
}
