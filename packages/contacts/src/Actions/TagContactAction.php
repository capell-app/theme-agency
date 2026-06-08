<?php

declare(strict_types=1);

namespace Capell\Contacts\Actions;

use Capell\Contacts\Models\Contact;
use Capell\Contacts\Models\ContactTag;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use RuntimeException;

/**
 * @method static Contact run(Contact $contact, string|list<string> $tags)
 */
final class TagContactAction
{
    use AsAction;

    /**
     * @param  string|list<string>  $tags
     */
    public function handle(Contact $contact, string|array $tags): Contact
    {
        return DB::transaction(function () use ($contact, $tags): Contact {
            $profile = $contact->profile ?? [];
            $existingTags = Arr::wrap($profile['tags'] ?? []);
            $nextTags = collect([...$existingTags, ...Arr::wrap($tags)])
                ->filter(static fn (mixed $tag): bool => is_string($tag) && trim($tag) !== '')
                ->map(static fn (string $tag): string => ContactTag::slugFor($tag))
                ->filter(static fn (string $tag): bool => $tag !== '')
                ->unique()
                ->values()
                ->all();

            $contact->profile = [
                ...$profile,
                'tags' => $nextTags,
            ];
            $contact->save();

            $tagIds = collect($nextTags)
                ->map(fn (string $tag): int => $this->resolveTagId($contact, $tag))
                ->all();

            $contact->tags()->syncWithoutDetaching($tagIds);

            return $contact->fresh(['tags']) ?? $contact;
        });
    }

    private function resolveTagId(Contact $contact, string $tag): int
    {
        $contactTag = ContactTag::query()->firstOrCreate([
            'site_id' => $contact->site_id,
            'slug' => $tag,
        ], [
            'name' => $tag,
        ]);

        $key = $contactTag->getKey();

        if (is_int($key)) {
            return $key;
        }

        if (is_string($key) && ctype_digit($key)) {
            return (int) $key;
        }

        throw new RuntimeException('Contact tag key must be an integer.');
    }
}
