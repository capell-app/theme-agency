<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Actions;

use Capell\CampaignStudio\Data\ConversionAttributionData;
use Capell\CampaignStudio\Events\CampaignConverted;
use Capell\CampaignStudio\Models\CampaignConversion;
use Capell\CampaignStudio\Models\CampaignConversionGoal;
use Capell\CampaignStudio\Models\CampaignGroup;
use Capell\CampaignStudio\Models\CampaignLandingPage;
use Capell\Core\Models\Page;
use Capell\Core\Models\PageUrl;
use Capell\Insights\Actions\RecordConversionAction;
use Capell\Insights\Models\InsightsVisit;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Lorisleiva\Actions\Concerns\AsAction;
use RuntimeException;
use Throwable;

final class RecordCampaignConversionAction
{
    use AsAction;

    public function handle(
        CampaignConversionGoal $goal,
        ?Model $visit = null,
        ?Model $event = null,
        ?CampaignLandingPage $landingPage = null,
        ?Model $source = null,
        ?ConversionAttributionData $attribution = null,
        ?string $url = null,
    ): ?CampaignConversion {
        if (! $goal->is_active) {
            return null;
        }

        $convertedAt = $this->convertedAt($event);
        $visit = $this->visitWithinAttributionWindow($visit, $convertedAt);
        $campaignGroup = $goal->campaignGroup;
        throw_unless($campaignGroup instanceof CampaignGroup, RuntimeException::class, 'Campaign conversion goal must belong to a campaign group.');

        $identity = [
            'campaign_conversion_goal_id' => $goal->getKey(),
            'insights_visit_id' => $visit?->getKey(),
            'insights_event_id' => $event?->getKey(),
            'source_type' => $source?->getMorphClass(),
            'source_id' => $source?->getKey(),
        ];

        $values = [
            'campaign_group_id' => $campaignGroup->getKey(),
            'campaign_landing_page_id' => $landingPage?->getKey(),
            'site_id' => $event?->getAttribute('site_id') ?? $visit?->getAttribute('site_id') ?? $goal->site_id,
            'language_id' => $event?->getAttribute('language_id') ?? $visit?->getAttribute('language_id'),
            'attribution' => $attribution ?? BuildConversionAttributionAction::run($visit, $event),
            'converted_at' => $convertedAt,
        ];

        $conversion = $this->hasIdentity($identity)
            ? CampaignConversion::query()->firstOrCreate($identity, $values)
            : CampaignConversion::query()->create([...$identity, ...$values]);

        if ($conversion instanceof CampaignConversion && $conversion->wasRecentlyCreated) {
            $this->recordInsightsConversion($conversion, $goal, $campaignGroup, $visit, $event, $landingPage, $url);

            event(new CampaignConverted($conversion));
        }

        return $conversion instanceof CampaignConversion ? $conversion : null;
    }

    /**
     * @param  array<string, mixed>  $identity
     */
    private function hasIdentity(array $identity): bool
    {
        return $identity['insights_visit_id'] !== null
            || $identity['insights_event_id'] !== null
            || $identity['source_type'] !== null
            || $identity['source_id'] !== null;
    }

    private function convertedAt(?Model $event): CarbonImmutable
    {
        $occurredAt = $event?->getAttribute('occurred_at');

        if ($occurredAt instanceof CarbonInterface) {
            return $occurredAt->toImmutable();
        }

        return now()->toImmutable();
    }

    private function visitWithinAttributionWindow(?Model $visit, CarbonImmutable $convertedAt): ?Model
    {
        if (! $visit instanceof Model) {
            return null;
        }

        $lookbackDays = $this->attributionLookbackDays();

        if ($lookbackDays === null) {
            return $visit;
        }

        $visitedAt = $this->timestampAttribute($visit, 'last_seen_at') ?? $this->timestampAttribute($visit, 'started_at');

        if (! $visitedAt instanceof CarbonImmutable) {
            return $visit;
        }

        return $visitedAt->lessThan($convertedAt->subDays($lookbackDays)) ? null : $visit;
    }

    private function attributionLookbackDays(): ?int
    {
        $lookbackDays = config('capell-campaign-studio.attribution.lookback_days', 30);

        if ($lookbackDays === null) {
            return null;
        }

        if (is_numeric($lookbackDays) && (int) $lookbackDays > 0) {
            return (int) $lookbackDays;
        }

        return 30;
    }

    private function timestampAttribute(Model $model, string $attribute): ?CarbonImmutable
    {
        $value = $model->getAttribute($attribute);

        if ($value instanceof CarbonInterface) {
            return $value->toImmutable();
        }

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (Throwable) {
            return null;
        }
    }

    private function recordInsightsConversion(
        CampaignConversion $conversion,
        CampaignConversionGoal $goal,
        CampaignGroup $campaignGroup,
        ?Model $visit,
        ?Model $event,
        ?CampaignLandingPage $landingPage,
        ?string $url,
    ): void {
        $visitUuid = $visit instanceof InsightsVisit ? $visit->uuid : null;

        if ($visitUuid === null || trim($visitUuid) === '') {
            return;
        }

        RecordConversionAction::run(
            visitUuid: $visitUuid,
            eventName: $this->insightsEventName($campaignGroup, $goal),
            url: $this->conversionUrl($url, $event, $visit, $landingPage),
            label: trim($campaignGroup->name . ': ' . $goal->name),
            sourcePackage: 'capell-app/campaign-studio',
            value: $this->numericValue($goal->getAttribute('value_amount')),
            occurredAt: $conversion->converted_at instanceof CarbonInterface
                ? $conversion->converted_at->toIso8601String()
                : null,
        );
    }

    private function insightsEventName(CampaignGroup $campaignGroup, CampaignConversionGoal $goal): string
    {
        $goalKey = $goal->getAttribute('key');
        $normalizedGoalKey = is_string($goalKey) && trim($goalKey) !== ''
            ? trim($goalKey)
            : 'goal-' . $goal->getKey();

        return sprintf('campaign.%s.%s', $campaignGroup->slug, $normalizedGoalKey);
    }

    private function conversionUrl(
        ?string $url,
        ?Model $event,
        ?Model $visit,
        ?CampaignLandingPage $landingPage,
    ): string {
        $candidates = [
            $url,
            $event?->getAttribute('url'),
            $visit?->getAttribute('landing_url'),
            $this->landingPageUrl($landingPage),
            '/',
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                return $candidate;
            }
        }

        return '/';
    }

    private function landingPageUrl(?CampaignLandingPage $landingPage): ?string
    {
        if (! $landingPage instanceof CampaignLandingPage) {
            return null;
        }

        $pageUrl = PageUrl::query()
            ->where('pageable_type', (new Page)->getMorphClass())
            ->where('pageable_id', $landingPage->page_id)
            ->orderBy('id')
            ->value('url');

        return is_string($pageUrl) && trim($pageUrl) !== '' ? $pageUrl : null;
    }

    private function numericValue(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }
}
