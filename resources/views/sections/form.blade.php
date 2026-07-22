<section
    id="contact-form"
    class="ppc-section ppc-section-field"
>
    <div class="ppc-section-inner ppc-split">
        <div>
            <p class="ppc-kicker">
                {{ __('capell-theme-agency::sections.form.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-agency::sections.form.heading')) }}
            </h2>
            <p class="ppc-lede">
                {{ data_get($section, 'summary', __('capell-theme-agency::sections.form.summary')) }}
            </p>
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
