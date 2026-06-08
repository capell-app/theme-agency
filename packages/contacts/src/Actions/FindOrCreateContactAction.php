<?php

declare(strict_types=1);

namespace Capell\Contacts\Actions;

use Capell\Contacts\Data\ContactIdentityData;
use Capell\Contacts\Enums\ContactStatus;
use Capell\Contacts\Models\Contact;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static Contact run(ContactIdentityData $identity, ?Model $source = null)
 */
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

        $this->fillFromSourceWhenPresent($contact, 'email', $identity->email, $seenAt);
        $this->fillFromSourceWhenPresent($contact, 'phone', $identity->phone, $seenAt);
        $this->fillFromSourceWhenPresent($contact, 'first_name', $identity->firstName, $seenAt);
        $this->fillFromSourceWhenPresent($contact, 'last_name', $identity->lastName, $seenAt);
        $this->fillFromSourceWhenPresent($contact, 'display_name', $identity->displayName, $seenAt);
        $this->fillWhenMissing($contact, 'source_key', $identity->sourceKey);
        $this->fillWhenMissing($contact, 'source_identifier', $identity->sourceIdentifier);

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

    private function fillFromSourceWhenPresent(
        Contact $contact,
        string $attribute,
        ?string $value,
        CarbonImmutable $seenAt,
    ): void {
        if ($this->blankString($value)) {
            return;
        }

        $existingValue = $contact->getAttribute($attribute);

        if (! is_string($existingValue) || trim($existingValue) === '') {
            $contact->setAttribute($attribute, $value);

            return;
        }

        $lastSeenAt = $contact->last_seen_at;

        if ($lastSeenAt instanceof CarbonImmutable && $seenAt->lessThan($lastSeenAt)) {
            return;
        }

        $contact->setAttribute($attribute, $value);
    }

    private function fillWhenMissing(Contact $contact, string $attribute, ?string $value): void
    {
        if ($this->blankString($value)) {
            return;
        }

        if (is_string($contact->getAttribute($attribute)) && trim($contact->getAttribute($attribute)) !== '') {
            return;
        }

        $contact->setAttribute($attribute, $value);
    }

    private function blankString(?string $value): bool
    {
        return $value === null || trim($value) === '';
    }
}
