<?php

declare(strict_types=1);

namespace Capell\DocumentLifecycle\Database\Factories;

use Capell\DocumentLifecycle\Models\DocumentAcceptance;
use Capell\DocumentLifecycle\Models\DocumentPublication;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<DocumentAcceptance>
 */
class DocumentAcceptanceFactory extends Factory
{
    protected $model = DocumentAcceptance::class;

    public function definition(): array
    {
        return [
            'acceptor_type' => null,
            'acceptor_id' => null,
            'subject_type' => null,
            'subject_id' => null,
            'document_key' => 'terms',
            'document_version' => 'v1',
            'document_publication_id' => null,
            'document_hash' => null,
            'legal_bundle_version' => null,
            'legal_bundle_hash' => null,
            'legal_document_versions' => null,
            'accepted_at' => now(),
            'context' => null,
            'ip_hash' => null,
            'user_agent_hash' => null,
            'metadata' => null,
        ];
    }

    public function forPublication(DocumentPublication $publication): static
    {
        return $this->state([
            'document_key' => $publication->document->key,
            'document_version' => $publication->version_label,
            'document_publication_id' => $publication->getKey(),
            'document_hash' => $publication->content_hash,
        ]);
    }

    public function acceptor(Model $acceptor): static
    {
        return $this->state([
            'acceptor_type' => $acceptor->getMorphClass(),
            'acceptor_id' => $acceptor->getKey(),
        ]);
    }

    public function subject(Model $subject): static
    {
        return $this->state([
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
        ]);
    }

    public function context(string $context): static
    {
        return $this->state(['context' => $context]);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function metadata(array $metadata): static
    {
        return $this->state(['metadata' => $metadata]);
    }
}
