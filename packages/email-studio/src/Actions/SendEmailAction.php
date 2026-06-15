<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Actions;

use Capell\EmailStudio\Data\EmailAddressData;
use Capell\EmailStudio\Data\EmailAttachmentData;
use Capell\EmailStudio\Data\EmailContextData;
use Capell\EmailStudio\Data\EmailHeaderData;
use Capell\EmailStudio\Data\ResolvedEmailTemplateData;
use Capell\EmailStudio\Data\SendEmailData;
use Capell\EmailStudio\Enums\EmailMessageStatus;
use Capell\EmailStudio\Enums\EmailRecipientStatus;
use Capell\EmailStudio\Exceptions\EmailStudioSendingException;
use Capell\EmailStudio\Jobs\SendEmailJob;
use Capell\EmailStudio\Models\EmailMessage;
use Capell\EmailStudio\Support\EmailAddressNormalizer;
use Capell\EmailStudio\Support\EmailProfileResolver;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static EmailMessage run(SendEmailData $data)
 */
class SendEmailAction
{
    use AsAction;

    public function handle(SendEmailData $data): EmailMessage
    {
        if ($this->recipientRows($data)->isEmpty()) {
            throw EmailStudioSendingException::noRecipients($data->templateKey);
        }

        $profile = resolve(EmailProfileResolver::class)->resolve($data->siteScopeKey, $data->emailProfileId)
            ?? throw EmailStudioSendingException::profileNotFound($data->siteScopeKey);

        $resolvedTemplate = RenderResolvedEmailTemplateAction::run(
            templateKey: $data->templateKey,
            context: new EmailContextData(variables: $data->variables),
            siteId: $data->siteId,
            siteScopeKey: $data->siteScopeKey,
            locale: $data->locale,
        );

        /** @var EmailMessage $message */
        $message = EmailMessage::query()->create([
            'site_id' => $data->siteId,
            'site_scope_key' => $data->siteScopeKey,
            'email_profile_id' => $profile->getKey(),
            'email_template_id' => $resolvedTemplate->template?->getKey(),
            'email_template_variant_id' => $resolvedTemplate->variant?->getKey(),
            'status' => $data->queue ? EmailMessageStatus::Queued : EmailMessageStatus::Requested,
            'subject' => $resolvedTemplate->rendered->subject,
            'preview_text' => $resolvedTemplate->rendered->previewText,
            'rendered_html' => $resolvedTemplate->rendered->html,
            'rendered_text' => $resolvedTemplate->rendered->text,
            'context_snapshot' => $data->variables,
            'headers' => $this->headersToArray($data),
            'attachments' => $this->attachmentsToArray($data),
            'triggered_by_type' => $data->triggeredByType,
            'triggered_by_id' => $data->triggeredById,
            'queued_at' => $data->queue ? now()->toImmutable() : null,
        ]);

        $this->createRecipients($message, $data, $resolvedTemplate);

        if ($data->queue && $message->recipients()->where('status', EmailRecipientStatus::Queued->value)->exists()) {
            dispatch(new SendEmailJob((int) $message->getKey()))->onQueue((string) config('capell-email-studio.queue'));
        }

        if (! $data->queue) {
            return DeliverEmailMessageAction::run($message);
        }

        return $message->fresh(['profile', 'template', 'templateVariant', 'recipients']) ?? $message;
    }

    private function createRecipients(EmailMessage $message, SendEmailData $data, ResolvedEmailTemplateData $resolvedTemplate): void
    {
        $normalizer = resolve(EmailAddressNormalizer::class);

        foreach ($this->recipientRows($data, $resolvedTemplate) as $recipientRow) {
            $normalizedEmail = $normalizer->normalize($recipientRow['address']->email);
            $suppressed = (new CheckEmailSuppressionAction)->handle($recipientRow['address']->email, $data->siteScopeKey);

            $message->recipients()->create([
                'site_id' => $data->siteId,
                'site_scope_key' => $data->siteScopeKey,
                'type' => $recipientRow['type'],
                'email' => $recipientRow['address']->email,
                'normalized_email' => $normalizedEmail,
                'email_hash' => $normalizer->hash($recipientRow['address']->email),
                'name' => $recipientRow['address']->name,
                'status' => $suppressed ? EmailRecipientStatus::Suppressed : EmailRecipientStatus::Queued,
                'suppressed_at' => $suppressed ? now()->toImmutable() : null,
            ]);
        }
    }

    /**
     * @return Collection<int, array{type: string, address: EmailAddressData}>
     */
    private function recipientRows(SendEmailData $data, ?ResolvedEmailTemplateData $resolvedTemplate = null): Collection
    {
        $templateCc = $resolvedTemplate instanceof ResolvedEmailTemplateData ? $resolvedTemplate->cc : [];
        $templateBcc = $resolvedTemplate instanceof ResolvedEmailTemplateData ? $resolvedTemplate->bcc : [];

        return collect([
            'to' => $data->to->items(),
            'cc' => [
                ...$data->cc->items(),
                ...$this->addressDataFromRows($templateCc),
            ],
            'bcc' => [
                ...$data->bcc->items(),
                ...$this->addressDataFromRows($templateBcc),
            ],
        ])->flatMap(fn (mixed $addresses, string $type): array => collect($addresses)
            ->map(fn (EmailAddressData $address): array => [
                'type' => $type,
                'address' => $address,
            ])
            ->all())
            ->values();
    }

    /**
     * @param  array<int, array{email: string, name?: string|null}>  $addresses
     * @return list<EmailAddressData>
     */
    private function addressDataFromRows(array $addresses): array
    {
        $addressData = [];

        foreach ($addresses as $address) {
            $addressData[] = new EmailAddressData(
                email: $address['email'],
                name: is_string($address['name'] ?? null) ? $address['name'] : null,
            );
        }

        return $addressData;
    }

    /**
     * @return array<string, string>
     */
    private function headersToArray(SendEmailData $data): array
    {
        return collect($data->headers->items())
            ->mapWithKeys(fn (EmailHeaderData $header): array => [$header->name => $header->value])
            ->all();
    }

    /**
     * @return array<int, array{disk: string, path: string, name: string, mime: string}>
     */
    private function attachmentsToArray(SendEmailData $data): array
    {
        if ($data->attachments === null) {
            return [];
        }

        return collect($data->attachments->items())
            ->map(fn (EmailAttachmentData $attachment): array => [
                'disk' => $attachment->disk,
                'path' => $attachment->path,
                'name' => $attachment->name,
                'mime' => $attachment->mime,
            ])
            ->values()
            ->all();
    }
}
