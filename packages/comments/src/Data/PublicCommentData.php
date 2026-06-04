<?php

declare(strict_types=1);

namespace Capell\Comments\Data;

use Carbon\CarbonImmutable;
use Livewire\Wireable;
use Spatie\LaravelData\Data;

class PublicCommentData extends Data implements Wireable
{
    /**
     * @param  list<PublicCommentData>  $children
     */
    public function __construct(
        public string $publicId,
        public string $body,
        public string $authorName,
        public CarbonImmutable $submittedAt,
        public string $submittedAtForHumans,
        public int $depth,
        public int $replyCount,
        public bool $hasMoreReplies = false,
        public array $children = [],
    ) {}

    /**
     * @param  array<string, mixed>  $value
     */
    public static function fromLivewire($value): self
    {
        $children = is_array($value['children'] ?? null)
            ? array_values(array_map(
                static fn (mixed $child): self => self::fromLivewire(is_array($child) ? $child : []),
                $value['children'],
            ))
            : [];

        $submittedAt = CarbonImmutable::parse(is_string($value['submittedAt'] ?? null) ? $value['submittedAt'] : 'now');

        return new self(
            publicId: is_string($value['publicId'] ?? null) ? $value['publicId'] : '',
            body: is_string($value['body'] ?? null) ? $value['body'] : '',
            authorName: is_string($value['authorName'] ?? null) ? $value['authorName'] : '',
            submittedAt: $submittedAt,
            submittedAtForHumans: is_string($value['submittedAtForHumans'] ?? null) ? $value['submittedAtForHumans'] : $submittedAt->diffForHumans(),
            depth: is_numeric($value['depth'] ?? null) ? (int) $value['depth'] : 0,
            replyCount: is_numeric($value['replyCount'] ?? null) ? (int) $value['replyCount'] : 0,
            hasMoreReplies: (bool) ($value['hasMoreReplies'] ?? false),
            children: $children,
        );
    }

    /**
     * @return array{
     *     publicId: string,
     *     body: string,
     *     authorName: string,
     *     submittedAt: string,
     *     submittedAtForHumans: string,
     *     depth: int,
     *     replyCount: int,
     *     hasMoreReplies: bool,
     *     children: list<array<string, mixed>>
     * }
     */
    public function toLivewire(): array
    {
        return [
            'publicId' => $this->publicId,
            'body' => $this->body,
            'authorName' => $this->authorName,
            'submittedAt' => $this->submittedAt->toIso8601String(),
            'submittedAtForHumans' => $this->submittedAtForHumans,
            'depth' => $this->depth,
            'replyCount' => $this->replyCount,
            'hasMoreReplies' => $this->hasMoreReplies,
            'children' => array_map(
                static fn (PublicCommentData $child): array => $child->toLivewire(),
                $this->children,
            ),
        ];
    }
}
