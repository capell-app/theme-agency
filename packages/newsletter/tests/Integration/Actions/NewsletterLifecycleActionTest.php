<?php

declare(strict_types=1);

use Capell\Contacts\Models\Contact;
use Capell\Contacts\Models\ContactActivity;
use Capell\FormBuilder\Data\SubmissionMetaData;
use Capell\FormBuilder\Data\SubmissionPayloadData;
use Capell\FormBuilder\Events\FormSubmitted;
use Capell\FormBuilder\Models\Form;
use Capell\FormBuilder\Models\Submission;
use Capell\Newsletter\Actions\CreateUnsubscribeTokenAction;
use Capell\Newsletter\Actions\SubscribeFromFormSubmissionAction;
use Capell\Newsletter\Enums\SubscriberStatus;
use Capell\Newsletter\Models\ConsentEvent;
use Capell\Newsletter\Models\FormMapping;
use Capell\Newsletter\Models\Subscriber;
use Capell\Newsletter\Notifications\ConfirmNewsletterSubscriptionNotification;
use Capell\Tags\Models\Tag;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

it('creates a pending subscriber from a mapped form submission and requests double opt in', function (): void {
    Notification::fake();

    $site = $this->createNewsletterSite();
    $tag = Tag::query()->create([
        'name' => ['en' => 'Product Updates'],
        'slug' => ['en' => 'product-updates'],
        'type' => 'newsletter',
    ]);
    $form = Form::query()->create([
        'site_id' => $site->getKey(),
        'name' => 'Newsletter signup',
        'handle' => 'newsletter',
        'schema' => [],
        'settings' => [],
        'is_active' => true,
    ]);
    FormMapping::query()->create([
        'site_id' => $site->getKey(),
        'form_id' => $form->getKey(),
        'name' => 'Newsletter signup',
        'email_field' => 'email',
        'consent_field' => 'consent',
        'fixed_tag_ids' => [$tag->getKey()],
        'requires_double_opt_in' => true,
        'confirmation_mode' => 'capell_owned',
        'is_active' => true,
    ]);
    $submission = Submission::query()->create([
        'form_id' => $form->getKey(),
        'site_id' => $site->getKey(),
        'payload' => new SubmissionPayloadData([
            'email' => 'Reader@example.com',
            'consent' => true,
        ]),
        'meta' => new SubmissionMetaData(
            ipAddress: '127.0.0.1',
            userAgent: 'Pest',
            url: 'https://example.test/signup',
        ),
        'status' => 'new',
        'submitted_at' => now(),
    ]);

    $subscriber = SubscribeFromFormSubmissionAction::run(new FormSubmitted($form, $submission));

    expect($subscriber)->toBeInstanceOf(Subscriber::class)
        ->and($subscriber->status)->toBe(SubscriberStatus::Pending)
        ->and($subscriber->email_hash)->toBe(hash('sha256', 'reader@example.com'))
        ->and(ConsentEvent::query()->where('subscriber_id', $subscriber->getKey())->count())->toBe(2)
        ->and($subscriber->tags()->pluck('tags.id')->all())->toContain((int) $tag->getKey());

    Notification::assertSentOnDemand(ConfirmNewsletterSubscriptionNotification::class);
});

it('keeps the same email isolated per site', function (): void {
    $firstSite = $this->createNewsletterSite('First Site');
    $secondSite = $this->createNewsletterSite('Second Site');

    $firstSubscriber = Subscriber::factory()->create([
        'site_id' => $firstSite->getKey(),
        'email' => 'same@example.com',
    ]);
    $secondSubscriber = Subscriber::factory()->create([
        'site_id' => $secondSite->getKey(),
        'email' => 'same@example.com',
    ]);

    expect($firstSubscriber->email_hash)->toBe($secondSubscriber->email_hash)
        ->and($firstSubscriber->getKey())->not->toBe($secondSubscriber->getKey());
});

it('confirms and unsubscribes with one-use public tokens', function (): void {
    $subscriber = Subscriber::factory()->create([
        'site_id' => $this->createNewsletterSite()->getKey(),
        'status' => SubscriberStatus::Pending,
    ]);
    $confirmToken = Str::random(64);
    $subscriber->publicTokens()->create([
        'type' => 'confirm',
        'token_hash' => hash('sha256', $confirmToken),
        'expires_at' => now()->addHour(),
    ]);

    $this->get(route('capell-newsletter.confirm', ['token' => $confirmToken]))
        ->assertOk()
        ->assertSee(__('capell-newsletter::messages.confirmed'));

    expect(Contact::query()->first()?->profile)->toMatchArray([
        'newsletter' => [
            'subscriber_id' => $subscriber->getKey(),
            'status' => SubscriberStatus::Subscribed->value,
            'source_form_id' => null,
            'source_form_handle' => null,
        ],
    ]);

    $this->get(route('capell-newsletter.confirm', ['token' => $confirmToken]))
        ->assertNotFound();

    $unsubscribeToken = CreateUnsubscribeTokenAction::run($subscriber->refresh());

    $this->get(route('capell-newsletter.unsubscribe', ['token' => $unsubscribeToken]))
        ->assertOk()
        ->assertSee(__('capell-newsletter::messages.unsubscribed'));

    expect($subscriber->refresh()->status)->toBe(SubscriberStatus::Unsubscribed);

    expect(Contact::query()->first()?->profile)->toMatchArray([
        'newsletter' => [
            'subscriber_id' => $subscriber->getKey(),
            'status' => SubscriberStatus::Unsubscribed->value,
            'source_form_id' => null,
            'source_form_handle' => null,
        ],
    ])
        ->and(ContactActivity::query()->count())->toBe(2)
        ->and(ContactActivity::query()->latest('id')->first()?->payload)->toMatchArray([
            'subscriber_id' => $subscriber->getKey(),
            'status' => SubscriberStatus::Unsubscribed->value,
        ]);
});
