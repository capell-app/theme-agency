@php
    use Capell\CampaignStudio\Actions\GetCampaignStudioTrackerScriptAction;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Facades\URL;

    $routeName = 'capell-campaigns.conversions';
    $conversionsUrl = Route::has($routeName)
        ? (
            config('capell-insights.require_signed_beacons', false) === true
                ? URL::temporarySignedRoute($routeName, now()->addMinutes((int) config('capell-insights.signed_beacon_ttl_minutes', 60)))
                : route($routeName)
        )
        : null;

    $campaignConfig = [
        'conversionsUrl' => $conversionsUrl,
    ];

    $campaignScript = GetCampaignStudioTrackerScriptAction::run();
@endphp

@if ($conversionsUrl !== null)
    <script
        type="application/json"
        data-campaign-tracker
    >
        {!! json_encode($campaignConfig, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) !!}
    </script>
    <script>
        {!! $campaignScript !!}
    </script>
@endif
