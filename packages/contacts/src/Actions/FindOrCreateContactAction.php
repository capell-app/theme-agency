<?php

declare(strict_types=1);

namespace Capell\Contacts\Actions;

use Capell\Contacts\Data\ContactIdentityData;
use Capell\Contacts\Enums\ContactStatus;
use Capell\Contacts\Models\Contact;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Lorisleiva\Actions\Concerns\AsAction;

final class FindOrCreateContactAction
{
    use AsAction;

    public function handle(ContactIdentityData $identity, ?Model $source = null): Contact
    {
        $seenAt = $identity->seenAt?->toImmutable() ?? CarbonImmutable::now();
        $contact = $this->findExistingContact($identity, $source) ?? new Contact([
            'site_id' => $identity->siteId,
            'status' => ContactStatus::Active,
            'first_seen_at' => $seenAt,
        ]);

        if ($source instanceof Model && $contact->source_id === null && $contact->source_type === null) {
            $contact->source()->associate($source);
        }

        $this->fillWhenPresent($contact, 'email', $identity->email);
        $this->fillWhenPresent($contact, 'phone', $identity->phone);
        $this->fillWhenPresent($contact, 'first_name', $identity->firstName);
        $this->fillWhenPresent($contact, 'last_name', $identity->lastName);
        $this->fillWhenPresent($contact, 'display_name', $identity->displayName);
        $this->fillWhenPresent($contact, 'source_key', $identity->sourceKey);
        $this->fillWhenPresent($contact, 'source_identifier', $identity->sourceIdentifier);

        if ($identity->profile !== null && $identity->profile !== []) {
            $contact->profile = array_replace($contact->profile ?? [], $identity->profile);
        }

        $contact->last_seen_at = $seenAt;
        $contact->save();

        return $contact;
    }

    private function findExistingContact(ContactIdentityData $identity, ?Model $source): ?Contact
    {
        $emailHash = Contact::emailHash($identity->email);

        if ($emailHash !== null) {
            return Contact::query()
                ->where('site_id', $identity->siteId)
                ->where('email_hash', $emailHash)
                ->first();
        }

        $phoneHash = Contact::phoneHash($identity->phone);

        if ($phoneHash !== null) {
            return Contact::query()
                ->where('site_id', $identity->siteId)
                ->where('phone_hash', $phoneHash)
                ->first();
        }

        $sourceIdentifierHash = Contact::sourceIdentifierHash($identity->sourceIdentifier);

        if ($identity->sourceKey !== null && trim($identity->sourceKey) !== '' && $sourceIdentifierHash !== null) {
            return Contact::query()
                ->where('site_id', $identity->siteId)
                ->where('source_key', trim($identity->sourceKey))
                ->where('source_identifier_hash', $sourceIdentifierHash)
                ->first();
        }

        if (! $source instanceof Model) {
            return null;
        }

        return Contact::query()
            ->where('site_id', $identity->siteId)
            ->where('source_type', $source->getMorphClass())
            ->where('source_id', $source->getKey())
            ->first();
    }

    private function fillWhenPresent(Contact $contact, string $attribute, ?string $value): void
    {
        if ($value === null || trim($value) === '') {
            return;
        }

        if (is_string($contact->getAttribute($attribute)) && trim($contact->getAttribute($attribute)) !== '') {
            return;
        }

        $contact->setAttribute($attribute, $value);
    }
}
