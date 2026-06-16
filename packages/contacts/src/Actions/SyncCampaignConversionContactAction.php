<?php

declare(strict_types=1);

namespace Capell\Contacts\Actions;

use Capell\Contacts\Actions\Concerns\CoercesContactSourceValues;
use Capell\Contacts\Data\ContactSourceRecordData;
use Capell\Contacts\Data\ContactSourceSyncResultData;
use Capell\Contacts\Enums\ContactActivityType;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

final class SyncCampaignConversionContactAction
{
    use AsAction;
    use CoercesContactSourceValues;

    public function handle(object $event): ?ContactSourceSyncResultData
    {
        $conversion = $event->conversion ?? null;

        if (! $conversion instanceof Model) {
            return null;
        }

        $conversionId = $this->intValue($conversion->getKey());
        $siteId = $this->intValue($conversion->getAttribute('site_id'));

        if ($conversionId === null || $siteId === null) {
            return null;
        }

        $source = $this->relatedModel($conversion, 'source');
        $payload = $this->sourcePayload($source);
        $goal = $this->relatedModel($conversion, 'goal');
        $campaignGroup = $this->relatedModel($conversion, 'campaignGroup');
        $landingPage = $this->relatedModel($conversion, 'landingPage');
        $goalName = $this->stringValue($goal?->getAttribute('name')) ?? __('capell-contacts::generic.campaign_studio.unknown_goal');
        $convertedAt = $conversion->getAttribute('converted_at');

        return SyncContactSourceRecordAction::run(
            new ContactSourceRecordData(
                siteId: $siteId,
                sourceKey: 'campaign_studio',
                sourceIdentifier: 'conversion-' . $conversionId,
                email: $this->email($payload),
                phone: $this->firstString($payload, ['phone', 'telephone', 'mobile']),
                firstName: $this->firstString($payload, ['first_name', 'firstname', 'given_name']),
                lastName: $this->firstString($payload, ['last_name', 'lastname', 'family_name', 'surname']),
                displayName: $this->displayName($payload),
                profile: [
                    'campaign_studio' => [
                        'campaign_group_id' => $this->intValue($campaignGroup?->getKey() ?? $conversion->getAttribute('campaign_group_id')),
                        'campaign_group_name' => $this->stringValue($campaignGroup?->getAttribute('name')),
                        'landing_page_id' => $this->intValue($landingPage?->getKey() ?? $conversion->getAttribute('campaign_landing_page_id')),
                        'goal_id' => $this->intValue($goal?->getKey() ?? $conversion->getAttribute('campaign_conversion_goal_id')),
                        'goal_name' => $goalName,
                        'conversion_id' => $conversionId,
                    ],
                ],
                tags: array_values(array_filter(['campaign_studio', $this->stringValue($campaignGroup?->getAttribute('slug'))])),
                activityType: ContactActivityType::CampaignConversion,
                activitySummary: __('capell-contacts::generic.campaign_studio.activity_summary', ['goal' => $goalName]),
                activityPayload: [
                    'campaign_group_id' => $this->intValue($campaignGroup?->getKey() ?? $conversion->getAttribute('campaign_group_id')),
                    'campaign_group_name' => $this->stringValue($campaignGroup?->getAttribute('name')),
                    'landing_page_id' => $this->intValue($landingPage?->getKey() ?? $conversion->getAttribute('campaign_landing_page_id')),
                    'goal_id' => $this->intValue($goal?->getKey() ?? $conversion->getAttribute('campaign_conversion_goal_id')),
                    'goal_name' => $goalName,
                    'conversion_id' => $conversionId,
                    'source_type' => $this->stringValue($conversion->getAttribute('source_type')),
                    'source_id' => $this->intValue($conversion->getAttribute('source_id')),
                ],
                occurredAt: $convertedAt instanceof CarbonInterface ? $convertedAt : null,
            ),
            $conversion,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function sourcePayload(?Model $source): array
    {
        if (! $source instanceof Model) {
            return [];
        }

        $payload = $source->getAttribute('payload');
        $values = is_object($payload) && property_exists($payload, 'values') ? $payload->values : null;

        if (is_array($values)) {
            return $values;
        }

        if (is_array($payload) && isset($payload['values']) && is_array($payload['values'])) {
            return $payload['values'];
        }

        return is_array($payload) ? $payload : [];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function email(array $payload): ?string
    {
        foreach ($payload as $key => $value) {
            if (! is_string($key)) {
                continue;
            }

            if (! str_contains(mb_strtolower($key), 'email')) {
                continue;
            }

            $email = $this->stringValue($value);

            if ($email !== null && filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
                return $email;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  list<string>  $keys
     */
    private function firstString(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $this->stringValue($payload[$key] ?? null);

            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function displayName(array $payload): ?string
    {
        $explicitName = $this->firstString($payload, ['name', 'full_name', 'display_name']);

        if ($explicitName !== null) {
            return $explicitName;
        }

        $name = Collection::make([
            $this->firstString($payload, ['first_name', 'firstname', 'given_name']),
            $this->firstString($payload, ['last_name', 'lastname', 'family_name', 'surname']),
        ])
            ->filter(fn (?string $value): bool => $value !== null)
            ->implode(' ');

        return $name !== '' ? $name : null;
    }
}
