# Knowledge Base

Knowledge Base is the content-system foundation for Capell docs: collections, versioned articles, feedback, related article links, public navigation data, search weighting, and AI-readable docs output.

It ships Filament resources for collections and articles, public `/docs` routes, `/docs/llms.txt` AI-readable output, and Blade views backed by hydrated DTOs. The public routes use Capell Frontend's route middleware registry when it is available, so frontend resolution and compatible cache/model-event middleware can participate in docs delivery. When Capell Search is installed, Knowledge Base registers a `knowledge-base` searchable source backed by public-safe article payloads. When Site Discovery is installed, published articles are contributed as `/docs/*` public URLs for sitemap/discovery coverage. Public consumers should use the package actions and DTOs so editor metadata stays out of cached frontend output.

Public article feedback is throttled by `capell-knowledge-base.feedback.throttle` (`30,1` by default) and repeat feedback from the same visitor for the same article version updates the existing row instead of creating duplicate votes.

## Demo Content

Run `capell:knowledge-base-demo` from a host Capell app to seed two public collections, published articles, a next-step related article link, and sample reader feedback. The command stores only portable article HTML and is safe to rerun without duplicating the demo articles.

## Screenshots

The package includes runner-backed admin and public route captures under `docs/screenshots/`. The marketplace gallery promotes the Capell admin article index and article edit/version-history screenshots; the public `/docs` captures remain route evidence for documentation and QA.
