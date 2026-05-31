@props([
    'campaignGroup' => null,
    'conversionGoal' => null,
    'location' => null,
])

@if ($campaignGroup)
        data-campaign="{{ $campaignGroup->slug }}"
@endif

@if ($conversionGoal)
        data-campaign-goal="{{ $conversionGoal->key }}"
@endif

@if ($location)
        data-campaign-location="{{ $location }}"
@endif
