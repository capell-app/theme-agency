@php
    use Capell\Core\Support\Security\PublicUrlSanitizer;
    use Illuminate\Support\Facades\Blade;

    $columns = collect(data_get($section, 'items', data_get($section, 'columns', [])))
        ->map(function (mixed $column): array {
            $links = collect(data_get($column, 'links', []))
                ->map(fn (mixed $link): array => [
                    'label' => is_array($link) ? data_get($link, 'label', data_get($link, 'title')) : $link,
                    'url' => is_array($link) ? PublicUrlSanitizer::sanitize(data_get($link, 'url', data_get($link, 'href'))) : null,
                ])
                ->filter(fn (array $link): bool => is_string($link['label']) && filled($link['label']))
                ->values();

            return [
                'title' => data_get($column, 'title', data_get($column, 'heading')),
                'links' => $links,
            ];
        })
        ->filter(fn (array $column): bool => $column['links']->isNotEmpty())
        ->values();
    $brand = data_get($section, 'brand', data_get($section, 'brandName'));
    $brandSummary = data_get($section, 'summary');

    // Footer-area widgets are configured in the Layout Builder independently of this
    // section's own chrome, so they render even when no brand, summary or links exist.
    $footerArea = trim(Blade::render('<x-capell::layout.area area="footer" />'));
@endphp

@if (filled($brand) || filled($brandSummary) || $columns->isNotEmpty())
    <footer class="ppc-section ppc-section-dark">
        <div class="ppc-section-inner">
            <div class="ppc-footer-grid">
                @if (filled($brand) || filled($brandSummary))
                    <div class="ppc-footer-brand">
                        @if (filled($brand))
                            <p class="ppc-footer-wordmark">{{ $brand }}</p>
                        @endif

                        @if (filled($brandSummary))
                            <p class="ppc-footer-summary">{{ $brandSummary }}</p>
                        @endif
                    </div>
                @endif

                @foreach ($columns as $column)
                    <section>
                        @if (filled($column['title']))
                            <h3 class="ppc-footer-heading">
                                {{ $column['title'] }}
                            </h3>
                        @endif

                        <ul class="ppc-footer-links">
                            @foreach ($column['links'] as $link)
                                <li>
                                    @if (filled($link['url']))
                                        <a href="{{ $link['url'] }}">
                                            {{ $link['label'] }}
                                        </a>
                                    @else
                                        {{ $link['label'] }}
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endforeach
            </div>
        </div>

        {!! $footerArea !!}
    </footer>
@elseif (filled($footerArea))
    <footer class="ppc-section ppc-section-dark">{!! $footerArea !!}</footer>
@endif
