<section
    id="contact-form"
    class="ppc-section ppc-section-field"
>
    <div class="ppc-section-inner ppc-split">
        <div>
            @if (filled(data_get($section, 'kicker')))
                <p class="ppc-kicker">{{ data_get($section, 'kicker') }}</p>
            @endif

            @if (filled(data_get($section, 'heading')))
                <h2>{{ data_get($section, 'heading') }}</h2>
            @endif

            @if (filled(data_get($section, 'summary')))
                <p class="ppc-lede">{{ data_get($section, 'summary') }}</p>
            @endif
        </div>

        <x-capell::form-embed
            :handle="data_get($section, 'form_handle')"
            :instance-id="(string) data_get($section, 'form_instance_id', 'agency-contact-form')"
            :fallback-message="(string) data_get($section, 'fallback_message', __('capell-theme-agency::sections.form.fallback_message'))"
            :fallback-label="(string) data_get($section, 'fallback_label', __('capell-theme-agency::sections.form.fallback_label'))"
            :fallback-url="(string) data_get($section, 'fallback_url', '')"
            class="ppc-form"
        />
    </div>
</section>
