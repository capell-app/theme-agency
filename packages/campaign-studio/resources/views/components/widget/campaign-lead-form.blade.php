@props ([
    'container',
    'containerKey',
    'containerWidth' => null,
    'loop',
    'widget',
])

@php
    use Capell\CampaignStudio\Actions\SanitizeCampaignHtmlAction;

    $campaignLeadFormContent = is_string($widget->translation?->content ?? null)
        ? SanitizeCampaignHtmlAction::run($widget->translation->content)
        : '';
@endphp

<x-capell-theme-foundation::widget.wrapper
    class="capell-widget-campaign-lead-form widget-campaign-lead-form"
    :$container
    :$containerKey
    :$containerWidth
    :index="$loop->index"
    :$widget
>
    <section
        class="campaign-lead-form px-6 py-12"
        data-campaign-goal="{{ $widget->getMeta('goal_key') }}"
        data-campaign-location="lead-form"
    >
        @if ($widget->translation?->title)
            <h2 class="mb-4 text-3xl font-bold">
                {{ $widget->translation->title }}
            </h2>
        @endif

        @if ($campaignLeadFormContent !== '')
            <div class="mb-6">{!! $campaignLeadFormContent !!}</div>
        @endif

        @if ($widget->getMeta('form_handle'))
            <livewire:capell-form-builder.form
                :handle="$widget->getMeta('form_handle')"
            />
        @endif
    </section>
</x-capell-theme-foundation::widget.wrapper>
