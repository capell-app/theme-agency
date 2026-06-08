<?php

declare(strict_types=1);

namespace Capell\DocumentLifecycle\Actions;

use Capell\DocumentLifecycle\Models\DocumentAcceptance;
use Lorisleiva\Actions\Concerns\AsAction;

final class BuildDocumentAcceptanceCertificateAction
{
    use AsAction;

    public function handle(DocumentAcceptance $acceptance): string
    {
        $acceptance->loadMissing('publication.document');

        $payload = [
            'certificate_version' => 1,
            'evidence_type' => 'document_acceptance',
            'acceptance' => [
                'id' => (int) $acceptance->getKey(),
                'document_key' => $acceptance->document_key,
                'document_version' => $acceptance->document_version,
                'document_publication_id' => $acceptance->document_publication_id,
                'document_hash' => $acceptance->document_hash,
                'accepted_at' => $acceptance->accepted_at?->toISOString(),
                'context' => $acceptance->context,
                'acceptor_type' => $acceptance->acceptor_type,
                'acceptor_id' => $acceptance->acceptor_id,
                'subject_type' => $acceptance->subject_type,
                'subject_id' => $acceptance->subject_id,
                'ip_hash' => $acceptance->ip_hash,
                'user_agent_hash' => $acceptance->user_agent_hash,
                'legal_bundle_version' => $acceptance->legal_bundle_version,
                'legal_bundle_hash' => $acceptance->legal_bundle_hash,
                'legal_document_versions' => $acceptance->legal_document_versions,
                'metadata' => $acceptance->metadata,
            ],
            'publication' => [
                'version_label' => $acceptance->publication?->version_label,
                'content_hash' => $acceptance->publication?->content_hash,
                'published_revision_id' => $acceptance->publication?->published_revision_id,
                'published_at' => $acceptance->publication?->published_at?->toISOString(),
            ],
            'document' => [
                'key' => $acceptance->publication?->document?->key ?? $acceptance->document_key,
                'title' => $acceptance->publication?->document?->title,
            ],
        ];

        $canonicalPayload = $this->json($payload);

        return $this->json([
            'payload' => $payload,
            'signature' => [
                'algorithm' => 'hmac-sha256',
                'value' => hash_hmac('sha256', $canonicalPayload, $this->signingKey()),
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function json(array $payload, int $flags = 0): string
    {
        return json_encode($payload, JSON_THROW_ON_ERROR | $flags);
    }

    private function signingKey(): string
    {
        $key = (string) config('app.key');

        if (str_starts_with($key, 'base64:')) {
            $decodedKey = base64_decode(substr($key, 7), true);

            if (is_string($decodedKey)) {
                return $decodedKey;
            }
        }

        return $key;
    }
}
