<?php

declare(strict_types=1);

namespace Capell\DocumentLifecycle\Database\Factories;

use Capell\DocumentLifecycle\Actions\ComputeDocumentContentHashAction;
use Capell\DocumentLifecycle\Models\Document;
use Capell\DocumentLifecycle\Models\DocumentPublication;
use Capell\PublishingStudio\Models\PublishingRevision;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<DocumentPublication>
 */
class DocumentPublicationFactory extends Factory
{
    protected $model = DocumentPublication::class;

    public function definition(): array
    {
        $versionLabel = 'v' . fake()->unique()->numberBetween(1, 9999);
        $content = [
            'title' => fake()->sentence(4),
            'body' => fake()->paragraph(),
            'version' => $versionLabel,
        ];

        return [
            'document_id' => Document::factory()->active(),
            'published_revision_id' => null,
            'version_label' => $versionLabel,
            'content_hash' => ComputeDocumentContentHashAction::run($content),
            'published_actor_type' => null,
            'published_actor_id' => null,
            'published_at' => now(),
            'metadata' => null,
        ];
    }

    public function document(Document $document): static
    {
        return $this->state(['document_id' => $document->getKey()]);
    }

    public function fromRevision(PublishingRevision $revision): static
    {
        return $this->state([
            'published_revision_id' => $revision->getKey(),
            'version_label' => 'r' . $revision->version,
            'content_hash' => ComputeDocumentContentHashAction::run($revision->after_payload ?? []),
            'published_actor_type' => $revision->actor_type,
            'published_actor_id' => $revision->actor_id,
            'published_at' => $revision->created_at ?? now(),
            'metadata' => [
                'event_type' => $revision->event_type->value,
                'publishing_revision_uuid' => $revision->uuid,
                'revisionable_type' => $revision->revisionable_type,
                'revisionable_id' => $revision->revisionable_id,
                'revisionable_uuid' => $revision->revisionable_uuid,
                'workspace_id' => $revision->workspace_id,
                'version_id' => $revision->version_id,
            ],
        ]);
    }

    public function publishedActor(Model $publishedActor): static
    {
        return $this->state([
            'published_actor_type' => $publishedActor->getMorphClass(),
            'published_actor_id' => $publishedActor->getKey(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function metadata(array $metadata): static
    {
        return $this->state(['metadata' => $metadata]);
    }
}
