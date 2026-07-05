@php
    $heading = data_get($section, 'heading', __('capell-theme-deep-bench::sections.profile.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-deep-bench::sections.profile.summary'));

    $profile = data_get($section, 'profile', []);
    $profileName = data_get($profile, 'name', __('capell-theme-deep-bench::sections.profile.name'));
    $profileRole = data_get($profile, 'role', __('capell-theme-deep-bench::sections.profile.role'));
    $profileBio = data_get($profile, 'bio', __('capell-theme-deep-bench::sections.profile.bio'));
    $profileImage = data_get($profile, 'image', data_get($profile, 'imageUrl'));
    $profileAlt = data_get($profile, 'imageAlt', $profileName);
    $profileUrl = data_get($profile, 'url', data_get($profile, 'href'));
    $profileLabel = data_get($profile, 'label', __('capell-theme-deep-bench::sections.profile.button'));
    $profileTags = data_get($profile, 'tags', []);

    $facts = data_get($profile, 'facts', [
        ['label' => __('capell-theme-deep-bench::sections.profile.location_label'), 'value' => __('capell-theme-deep-bench::sections.profile.location')],
        ['label' => __('capell-theme-deep-bench::sections.profile.availability_label'), 'value' => __('capell-theme-deep-bench::sections.profile.availability')],
        ['label' => __('capell-theme-deep-bench::sections.profile.focus_label'), 'value' => __('capell-theme-deep-bench::sections.profile.focus')],
        ['label' => __('capell-theme-deep-bench::sections.profile.listed_label'), 'value' => __('capell-theme-deep-bench::sections.profile.listed')],
    ]);

    $gallery = collect(data_get($section, 'gallery', data_get($section, 'items', [])))
        ->filter(fn (mixed $entry): bool => filled(data_get($entry, 'image', data_get($entry, 'imageUrl'))))
        ->values();
@endphp

<section
    id="profile-detail"
    class="pfd-section"
>
    <div class="pfd-section-inner">
        <p class="pfd-eyebrow">
            {{ data_get($section, 'eyebrow', __('capell-theme-deep-bench::sections.profile.eyebrow')) }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="pfd-lede">{{ $summary }}</p>

        <div class="pfd-profile">
            <article class="pfd-profile-card">
                <div class="pfd-profile-identity">
                    @if (filled($profileImage))
                        <img
                            src="{{ $profileImage }}"
                            alt="{{ $profileAlt }}"
                            loading="eager"
                            decoding="async"
                            class="pfd-photo pfd-avatar pfd-avatar-lg"
                        />
                    @else
                        <div
                            class="pfd-photo pfd-photo-empty pfd-avatar pfd-avatar-lg"
                            aria-hidden="true"
                        ></div>
                    @endif
                    <div>
                        <h3>
                            @if (filled($profileUrl))
                                <a
                                    class="pfd-title-link"
                                    href="{{ $profileUrl }}"
                                >
                                    {{ $profileName }}
                                </a>
                            @else
                                {{ $profileName }}
                            @endif
                        </h3>
                        <p class="pfd-role">{{ $profileRole }}</p>
                    </div>
                </div>

                <p>{{ $profileBio }}</p>

                @if (is_iterable($profileTags) && collect($profileTags)->isNotEmpty())
                    <ul class="pfd-tags">
                        @foreach ($profileTags as $tag)
                            <li>
                                {{ is_array($tag) ? data_get($tag, 'label', '') : $tag }}
                            </li>
                        @endforeach
                    </ul>
                @endif

                <dl class="pfd-profile-facts">
                    @foreach ($facts as $fact)
                        <div>
                            <dt>{{ data_get($fact, 'label', '') }}</dt>
                            <dd>{{ data_get($fact, 'value', '') }}</dd>
                        </div>
                    @endforeach
                </dl>

                @if (filled($profileUrl))
                    <div class="pfd-actions">
                        <a
                            class="pfd-button"
                            href="{{ $profileUrl }}"
                        >
                            {{ $profileLabel }}
                        </a>
                    </div>
                @endif
            </article>

            <div class="pfd-profile-gallery">
                @forelse ($gallery as $entry)
                    @php
                        $entryImage = data_get($entry, 'image', data_get($entry, 'imageUrl'));
                        $entryAlt = data_get($entry, 'imageAlt', data_get($entry, 'title', $profileName));
                    @endphp

                    <img
                        src="{{ $entryImage }}"
                        alt="{{ $entryAlt }}"
                        loading="lazy"
                        decoding="async"
                        class="pfd-photo"
                    />
                @empty
                    <div
                        class="pfd-photo pfd-photo-empty"
                        aria-hidden="true"
                    ></div>
                    <div
                        class="pfd-photo pfd-photo-empty"
                        aria-hidden="true"
                    ></div>
                    <div
                        class="pfd-photo pfd-photo-empty"
                        aria-hidden="true"
                    ></div>
                @endforelse
                <p class="pfd-meta">
                    {{ __('capell-theme-deep-bench::sections.profile.gallery_caption') }}
                </p>
            </div>
        </div>
    </div>
</section>
