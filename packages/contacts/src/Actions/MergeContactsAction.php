<?php

declare(strict_types=1);

namespace Capell\Contacts\Actions;

use Capell\Contacts\Data\ContactActivityData;
use Capell\Contacts\Enums\ContactActivityType;
use Capell\Contacts\Models\Contact;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use RuntimeException;

/**
 * @method static Contact run(Contact $source, Contact $target, string $reason = 'manual')
 */
final class MergeContactsAction
{
    use AsAction;

    public function handle(Contact $source, Contact $target, string $reason = 'manual'): Contact
    {
        if ($source->is($target)) {
            throw new RuntimeException((string) __('capell-contacts::generic.merge.same_contact'));
        }

        if ((int) $source->site_id !== (int) $target->site_id) {
            throw new RuntimeException((string) __('capell-contacts::generic.merge.cross_site'));
        }

        return DB::transaction(function () use ($reason, $source, $target): Contact {
            $source = Contact::query()->lockForUpdate()->whereKey($source->getKey())->firstOrFail();
            $target = Contact::query()->lockForUpdate()->whereKey($target->getKey())->firstOrFail();

            $mergedAttributes = $this->mergedAttributes($source, $target);

            $this->clearSourceIdentityColumns($source);
            $this->moveDependentRows($source, $target);
            $this->moveOrganisationMemberships($source, $target);
            $this->moveTags($source, $target);

            $target->forceFill($mergedAttributes)->save();

            RecordContactActivityAction::run($target, new ContactActivityData(
                type: ContactActivityType::Note,
                summary: (string) __('capell-contacts::generic.merge.activity_summary'),
                payload: [
                    'source' => 'contact_merge',
                    'reason' => $reason,
                    'merged_contact_id' => $source->getKey(),
                ],
            ));

            $source->delete();

            return $target->refresh();
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function mergedAttributes(Contact $source, Contact $target): array
    {
        return [
            'email' => $target->email ?? $source->email,
            'phone' => $target->phone ?? $source->phone,
            'first_name' => $target->first_name ?? $source->first_name,
            'last_name' => $target->last_name ?? $source->last_name,
            'display_name' => $target->display_name ?? $source->display_name,
            'source_key' => $target->source_key ?? $source->source_key,
            'source_identifier' => $target->source_identifier ?? $source->source_identifier,
            'profile' => $this->mergeProfiles($source->profile, $target->profile),
            'first_seen_at' => $this->earliest($source->first_seen_at, $target->first_seen_at),
            'last_seen_at' => $this->latest($source->last_seen_at, $target->last_seen_at),
        ];
    }

    private function clearSourceIdentityColumns(Contact $source): void
    {
        $source->forceFill([
            'email' => null,
            'phone' => null,
            'source_identifier' => null,
            'source_key' => null,
        ])->save();
    }

    private function moveDependentRows(Contact $source, Contact $target): void
    {
        $source->leads()->update(['contact_id' => $target->getKey()]);
        $source->activities()->update(['contact_id' => $target->getKey()]);
    }

    private function moveOrganisationMemberships(Contact $source, Contact $target): void
    {
        foreach ($source->organisations()->withPivot(['role', 'is_primary'])->get() as $organisation) {
            if ($target->organisations()->whereKey($organisation->getKey())->exists()) {
                continue;
            }

            $pivot = $organisation->pivot;
            $role = $pivot instanceof Pivot && is_string($pivot->getAttribute('role'))
                ? $pivot->getAttribute('role')
                : null;
            $isPrimary = $pivot instanceof Pivot ? (bool) $pivot->getAttribute('is_primary') : false;

            $target->organisations()->attach($organisation->getKey(), [
                'role' => $role,
                'is_primary' => $isPrimary,
            ]);
        }
    }

    private function moveTags(Contact $source, Contact $target): void
    {
        $tagTable = $source->tags()->getRelated()->getTable();

        $target->tags()->syncWithoutDetaching($source->tags()->pluck($tagTable . '.id')->all());
    }

    /**
     * @param  array<string, mixed>|null  $sourceProfile
     * @param  array<string, mixed>|null  $targetProfile
     * @return array<string, mixed>|null
     */
    private function mergeProfiles(?array $sourceProfile, ?array $targetProfile): ?array
    {
        if ($sourceProfile === null) {
            return $targetProfile;
        }

        if ($targetProfile === null) {
            return $sourceProfile;
        }

        return array_replace_recursive($sourceProfile, $targetProfile);
    }

    private function earliest(mixed $source, mixed $target): mixed
    {
        if (! $source instanceof CarbonImmutable) {
            return $target;
        }

        if (! $target instanceof CarbonImmutable) {
            return $source;
        }

        return $source->lessThan($target) ? $source : $target;
    }

    private function latest(mixed $source, mixed $target): mixed
    {
        if (! $source instanceof CarbonImmutable) {
            return $target;
        }

        if (! $target instanceof CarbonImmutable) {
            return $source;
        }

        return $source->greaterThan($target) ? $source : $target;
    }
}
