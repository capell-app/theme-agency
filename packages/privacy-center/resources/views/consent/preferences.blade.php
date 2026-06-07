@php
    use Capell\PrivacyCenter\Enums\CookieCategory;

    /** @var array<int, CookieCategory> $categories */
@endphp

<section class="privacy-consent" data-privacy-consent>
    <h1>{{ __('capell-privacy-center::privacy.public.preferences.title') }}</h1>
    <p>{{ __('capell-privacy-center::privacy.public.preferences.intro') }}</p>

    @if (session('capell_privacy_center_consent_saved') === true)
        <p role="status">{{ __('capell-privacy-center::privacy.public.preferences.saved') }}</p>
    @endif

    <form method="POST" action="{{ route('capell-privacy-center.consent.store') }}">
        @csrf

        @foreach ($categories as $category)
            <label>
                <input
                    type="checkbox"
                    name="categories[]"
                    value="{{ $category->value }}"
                    @checked($category === CookieCategory::Essential)
                    @disabled($category === CookieCategory::Essential)
                >
                <span>{{ $category->getLabel() }}</span>
                <span>{{ __('capell-privacy-center::privacy.public.categories.' . $category->value) }}</span>
            </label>
        @endforeach

        <button type="submit">{{ __('capell-privacy-center::privacy.public.preferences.save') }}</button>
    </form>
</section>
