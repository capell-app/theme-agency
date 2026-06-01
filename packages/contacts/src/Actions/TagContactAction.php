<?php

declare(strict_types=1);

namespace Capell\Contacts\Actions;

use Capell\Contacts\Models\Contact;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

final class TagContactAction
{
    use AsAction;

    /**
     * @param  string|list<string>  $tags
     */
    public function handle(Contact $contact, string|array $tags): Contact
    {
        $profile = $contact->profile ?? [];
        $existingTags = Arr::wrap($profile['tags'] ?? []);
        $nextTags = collect([...$existingTags, ...Arr::wrap($tags)])
            ->filter(static fn (mixed $tag): bool => is_string($tag) && trim($tag) !== '')
            ->map(static fn (string $tag): string => Str::of($tag)->trim()->lower()->toString())
            ->unique()
            ->values()
            ->all();

        $contact->profile = [
            ...$profile,
            'tags' => $nextTags,
        ];
        $contact->save();

        return $contact;
    }
}
