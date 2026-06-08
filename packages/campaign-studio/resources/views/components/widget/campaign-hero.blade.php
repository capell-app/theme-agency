@props([
    'title' => $widget->translation?->title,
    'content' => $widget->translation?->content,
    'eyebrow' => $widget->getMeta('eyebrow'),
    'primaryButtonText' => $widget->getMeta('primary_button_text'),
    'primaryButtonUrl' => $widget->getMeta('primary_button_url', '#'),
    'secondaryButtonText' => $widget->getMeta('secondary_button_text'),
    'secondaryButtonUrl' => $widget->getMeta('secondary_button_url', '#'),
    'goalKey' => $widget->getMeta('goal_key'),
    'utmSource' => $widget->getMeta('utm_source'),
    'utmMedium' => $widget->getMeta('utm_medium'),
    'utmCampaign' => $widget->getMeta('utm_campaign'),
    'utmTerm' => $widget->getMeta('utm_term'),
    'utmContent' => $widget->getMeta('utm_content'),
    'container',
    'containerKey',
    'containerWidth' => null,
    'loop',
    'widget',
])

@php
    use Capell\CampaignStudio\Actions\BuildCampaignUrlAction;
    use Capell\CampaignStudio\Actions\SanitizeCampaignHtmlAction;
    use Capell\CampaignStudio\Data\UtmData;

    $campaignHeroContent = is_string($content) ? SanitizeCampaignHtmlAction::run($content) : '';
    $campaignHeroUtm = new UtmData(
        source: is_scalar($utmSource) ? (string) $utmSource : null,
        medium: is_scalar($utmMedium) ? (string) $utmMedium : null,
        campaign: is_scalar($utmCampaign) ? (string) $utmCampaign : null,
        term: is_scalar($utmTerm) ? (string) $utmTerm : null,
        content: is_scalar($utmContent) ? (string) $utmContent : null,
    );

    $primaryButtonUrl = is_string($primaryButtonUrl) ? BuildCampaignUrlAction::run($primaryButtonUrl, $campaignHeroUtm) : '#';
    $secondaryButtonUrl = is_string($secondaryButtonUrl) ? BuildCampaignUrlAction::run($secondaryButtonUrl, $campaignHeroUtm) : '#';
@endphp

<x-capell-foundation-theme::widget.wrapper
    class="capell-widget-campaign-hero widget-campaign-hero"
    :$container
    :$containerKey
    :$containerWidth
    :index="$loop->index"
    :$widget
>
    <section class="campaign-hero px-6 py-16">
        <div class="mx-auto max-w-5xl">
            @if ($eyebrow)
                <p class="mb-3 text-sm font-semibold tracking-wide uppercase">
                    {{ $eyebrow }}
                </p>
            @endif

            @if ($title)
                <h1 class="max-w-3xl text-4xl font-bold">{{ $title }}</h1>
            @endif

            @if ($campaignHeroContent !== '')
                <div class="mt-5 max-w-2xl text-lg">
                    {!! $campaignHeroContent !!}
                </div>
            @endif

            <div class="mt-8 flex flex-wrap gap-3">
                @if ($primaryButtonText)
                    <a
                        href="{{ $primaryButtonUrl }}"
                        class="layout-builder-btn layout-builder-btn-primary"
                        data-campaign-goal="{{ $goalKey }}"
                        data-campaign-location="hero-primary"
                    >
                        {{ $primaryButtonText }}
                    </a>
                @endif

                @if ($secondaryButtonText)
                    <a
                        href="{{ $secondaryButtonUrl }}"
                        class="layout-builder-btn layout-builder-btn-secondary"
                        data-campaign-location="hero-secondary"
                    >
                        {{ $secondaryButtonText }}
                    </a>
                @endif
            </div>
        </div>
    </section>
</x-capell-foundation-theme::widget.wrapper>
