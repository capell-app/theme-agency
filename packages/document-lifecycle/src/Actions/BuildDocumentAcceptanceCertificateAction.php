<?php

declare(strict_types=1);

namespace Capell\DocumentLifecycle\Actions;

use Capell\DocumentLifecycle\Models\Document;
use Capell\DocumentLifecycle\Models\DocumentAcceptance;
use Capell\DocumentLifecycle\Models\DocumentPublication;
use Illuminate\Database\Eloquent\Model;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static string run(DocumentAcceptance $acceptance)
 */
final class BuildDocumentAcceptanceCertificateAction
{
    use AsAction;

    public function handle(DocumentAcceptance $acceptance): string
    {
        $acceptance->loadMissing('publication.document');
        $publication = $acceptance->publication;
        $document = $publication instanceof DocumentPublication ? $publication->document : null;

        $payload = [
            'certificate_version' => 1,
            'evidence_type' => 'document_acceptance',
            'acceptance' => [
                'id' => $this->modelKey($acceptance),
                'document_key' => $acceptance->document_key,
                'document_version' => $acceptance->document_version,
                'document_publication_id' => $acceptance->document_publication_id,
                'document_hash' => $acceptance->document_hash,
                'accepted_at' => $acceptance->accepted_at->toISOString(),
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
                'version_label' => $publication?->version_label,
                'content_hash' => $publication?->content_hash,
                'published_revision_id' => $publication?->published_revision_id,
                'published_at' => $publication?->published_at->toISOString(),
            ],
            'document' => [
                'key' => $document instanceof Document ? $document->key : $acceptance->document_key,
                'title' => $document instanceof Document ? $document->title : null,
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
        $key = $this->configString('app.key');

        if (str_starts_with($key, 'base64:')) {
            $decodedKey = base64_decode(substr($key, 7), true);

            if (is_string($decodedKey)) {
                return $decodedKey;
            }
        }

        return $key;
    }

    private function configString(string $key): string
    {
        $value = config($key);

        return is_string($value) ? $value : '';
    }

    private function modelKey(Model $model): int
    {
        $key = $model->getKey();

        return is_int($key) ? $key : 0;
    }
}
