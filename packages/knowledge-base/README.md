# Knowledge Base

Knowledge Base is the content-system foundation for Capell docs: collections, versioned articles, feedback, related article links, public navigation data, search weighting, and AI-readable docs output.

It ships Filament resources for collections and articles, public `/docs` routes, and Blade views backed by hydrated DTOs. The public routes use Capell Frontend's route middleware registry when it is available, so frontend resolution and compatible cache/model-event middleware can participate in docs delivery. Public consumers should use the package actions and DTOs so editor metadata stays out of cached frontend output.

Public article feedback is throttled by `capell-knowledge-base.feedback.throttle` (`30,1` by default) and repeat feedback from the same visitor for the same article version updates the existing row instead of creating duplicate votes.
