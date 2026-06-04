<?php

declare(strict_types=1);

namespace Capell\Contacts\Console\Commands;

use Capell\Contacts\Actions\AnonymizeContactWithAuditAction;
use Capell\Contacts\Actions\AuditContactPrivacyExportAction;
use Capell\Contacts\Models\Contact;
use Illuminate\Console\Command;

final class ContactPrivacyCommand extends Command
{
    protected $signature = 'capell-contacts:privacy
        {contact? : Contact ID}
        {--email= : Contact email to resolve by identity hash}
        {--site-id= : Site ID required when resolving by email}
        {--export : Output a subject-access export and record an audit activity}
        {--anonymize : Anonymize the contact and record an audit activity}
        {--json : Output a machine-readable operation summary}';

    protected $description = 'Run Contacts subject-access export and anonymization workflows.';

    public function handle(): int
    {
        if ($this->option('export') !== true && $this->option('anonymize') !== true) {
            $this->error((string) __('capell-contacts::generic.privacy.command_requires_operation'));

            return self::FAILURE;
        }

        $contact = $this->resolveContact();

        if (! $contact instanceof Contact) {
            return self::FAILURE;
        }

        $export = null;

        if ($this->option('export') === true) {
            $export = AuditContactPrivacyExportAction::run($contact, 'console');
        }

        if ($this->option('anonymize') === true) {
            $contact = AnonymizeContactWithAuditAction::run($contact, 'console');
        }

        if ($this->option('json') === true) {
            $this->line(json_encode([
                'contact_id' => $contact->getKey(),
                'exported' => $export !== null,
                'anonymized' => $this->option('anonymize') === true,
                'export' => $export,
            ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

            return self::SUCCESS;
        }

        if (is_array($export)) {
            $this->line(json_encode($export, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
        }

        if ($this->option('anonymize') === true) {
            $this->components->info((string) __('capell-contacts::generic.privacy.anonymized_contact', [
                'contact' => $contact->getKey(),
            ]));
        }

        return self::SUCCESS;
    }

    private function resolveContact(): ?Contact
    {
        $contactId = $this->argument('contact');
        $email = $this->option('email');

        if (($contactId === null || $contactId === '') && ($email === null || $email === '')) {
            $this->error((string) __('capell-contacts::generic.privacy.command_requires_contact'));

            return null;
        }

        if (($contactId !== null && $contactId !== '') && ($email !== null && $email !== '')) {
            $this->error((string) __('capell-contacts::generic.privacy.command_single_lookup'));

            return null;
        }

        if ($contactId !== null && $contactId !== '') {
            if (! is_string($contactId) || ! ctype_digit($contactId)) {
                $this->error((string) __('capell-contacts::generic.privacy.command_valid_contact'));

                return null;
            }

            $contact = Contact::query()->find((int) $contactId);

            if (! $contact instanceof Contact) {
                $this->error((string) __('capell-contacts::generic.privacy.command_contact_not_found'));
            }

            return $contact;
        }

        if (! is_string($email) || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $this->error((string) __('capell-contacts::generic.privacy.command_valid_email'));

            return null;
        }

        $siteId = $this->positiveIntegerOption('site-id');

        if ($siteId === null) {
            $this->error((string) __('capell-contacts::generic.privacy.command_requires_site'));

            return null;
        }

        if ($siteId === 0) {
            return null;
        }

        $contact = Contact::query()
            ->where('site_id', $siteId)
            ->where('email_hash', Contact::emailHash($email))
            ->first();

        if (! $contact instanceof Contact) {
            $this->error((string) __('capell-contacts::generic.privacy.command_contact_not_found'));
        }

        return $contact;
    }

    private function positiveIntegerOption(string $name): ?int
    {
        $option = $this->option($name);

        if ($option === null || $option === '') {
            return null;
        }

        if (! is_string($option) && ! is_int($option)) {
            $this->error((string) __('capell-contacts::generic.privacy.command_positive_integer', ['option' => '--' . $name]));

            return 0;
        }

        $value = (string) $option;

        if (! ctype_digit($value) || (int) $value < 1) {
            $this->error((string) __('capell-contacts::generic.privacy.command_positive_integer', ['option' => '--' . $name]));

            return 0;
        }

        return (int) $value;
    }
}
