@props([
    'container',
    'containerKey',
    'containerWidth' => null,
    'ctaWidget' => null,
    'loop',
    'widget',
])

@php
    use Capell\CampaignStudio\Actions\BuildCampaignUrlAction;
    use Capell\CampaignStudio\Data\UtmData;
@endphp

<x-capell-foundation-theme::widget.wrapper
    class="capell-widget-campaign-cta-widget widget-campaign-cta-widget"
    :$container
    :$containerKey
    :$containerWidth
    :index="$loop->index"
    :$widget
>
    @if ($ctaWidget)
        <section class="campaign-cta-widget px-6 py-12 text-center">
            @if ($ctaWidget->headline)
                <h2 class="text-3xl font-bold">{{ $ctaWidget->headline }}</h2>
            @endif

            @if ($ctaWidget->body)
                <p class="mx-auto mt-4 max-w-2xl">{{ $ctaWidget->body }}</p>
            @endif

            <div class="mt-8 flex flex-wrap justify-center gap-3">
                @foreach ($ctaWidget->actions ?? [] as $action)
                    <a
                        href="{{ BuildCampaignUrlAction::run($action->url, $action->utm ?? $ctaWidget->default_utm ?? new UtmData) }}"
                        class="layout-builder-btn {{ $action->style === 'secondary' ? 'layout-builder-btn-secondary' : 'layout-builder-btn-primary' }}"
                        data-campaign="{{ $ctaWidget->campaignGroup?->slug }}"
                        data-campaign-cta="{{ $ctaWidget->key }}"
                        data-campaign-goal="{{ $action->goalKey }}"
                        data-campaign-location="cta-widget"
                    >
                        {{ $action->label }}
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</x-capell-foundation-theme::widget.wrapper>
