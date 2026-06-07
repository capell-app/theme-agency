@php
    $business = $section->business ?? [];
    $services = $section->services ?? $section->items ?? [];
    $faqs = $section->faqs ?? [];
    $openingHours = $section->openingHours ?? [];

    $businessName = $business['name'] ?? $section->businessName ?? $section->heading ?? __('capell-theme-local-services::generic.structured_data_business_name');
    $businessUrl = $business['url'] ?? $section->url ?? null;
    $businessPhone = $business['phone'] ?? $section->phone ?? null;
    $businessAddress = $business['address'] ?? $section->address ?? null;
    $businessType = $business['type'] ?? 'LocalBusiness';

    $graph = [
        [
            '@type' => $businessType,
            'name' => $businessName,
            'url' => $businessUrl,
            'telephone' => $businessPhone,
            'address' => $businessAddress,
            'openingHoursSpecification' => array_values(array_map(
                static fn (array $hours): array => [
                    '@type' => 'OpeningHoursSpecification',
                    'dayOfWeek' => $hours['dayOfWeek'] ?? $hours['day'] ?? null,
                    'opens' => $hours['opens'] ?? null,
                    'closes' => $hours['closes'] ?? null,
                ],
                is_array($openingHours) ? $openingHours : [],
            )),
        ],
        [
            '@type' => 'Service',
            'name' => $section->serviceName ?? __('capell-theme-local-services::generic.structured_data_service_name'),
            'provider' => [
                '@type' => $businessType,
                'name' => $businessName,
            ],
            'areaServed' => $section->areaServed ?? null,
            'hasOfferCatalog' => [
                '@type' => 'OfferCatalog',
                'name' => __('capell-theme-local-services::generic.structured_data_service_catalog'),
                'itemListElement' => array_values(array_map(
                    static fn (array $service): array => [
                        '@type' => 'Offer',
                        'itemOffered' => [
                            '@type' => 'Service',
                            'name' => $service['title'] ?? $service['name'] ?? $service['label'] ?? __('capell-theme-local-services::generic.service_label'),
                            'description' => $service['summary'] ?? $service['description'] ?? null,
                        ],
                    ],
                    is_array($services) ? $services : [],
                )),
            ],
            ],
        [
                '@type' => 'FAQPage',
                'mainEntity' => array_values(array_map(
                    static fn (array $faq): array => [
                        '@type' => 'Question',
                        'name' => $faq['question'] ?? $faq['title'] ?? '',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => $faq['answer'] ?? $faq['summary'] ?? '',
                        ],
                    ],
                    is_array($faqs) ? $faqs : [],
                )),
            ],
    ];

    $schema = [
        '@context' => 'https://schema.org',
        '@graph' => $graph,
    ];
@endphp

<script type="application/ld+json">
    {!! json_encode($schema, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
