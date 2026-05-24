# Crawler Governance

Crawler governance separates ordinary search visibility from AI search inclusion, user-triggered fetches, model training, grounding, and broad dataset crawling.

## Default Postures

SEO Suite ships crawler policy presets through AI Discovery:

- Search-visible, training-restricted: allow AI search and user-triggered fetchers while disallowing model-training and broad dataset crawlers.
- Open: allow all seeded AI crawler user agents.
- Restrictive: disallow all seeded AI crawler user agents.

Use the preset as the site baseline, then add site-specific crawler rule rows only when a provider or path needs a deliberate override.

## Provider Families

- Normal search bots should follow the site's ordinary search and robots policy.
- OpenAI search and training are separate controls; search inclusion and training reuse should not be treated as the same decision.
- Google Search and Google Extended controls are separate; restricting training or grounding controls should not be described as blocking normal Google Search.
- Bing still relies on ordinary crawlability and can benefit from Site Discovery sitemap output and IndexNow change notifications.
- Broad crawlers such as Common Crawl should usually be reviewed separately from answer-engine search crawlers.

Provider names and crawler behavior can change. Review upstream crawler documentation before changing seeded defaults or publishing guidance.

## Site-Owner Checklist

- Decide whether the site should be visible in normal search.
- Decide whether AI search and answer engines may cite public pages.
- Decide whether model-training crawlers may reuse content.
- Decide whether broad dataset crawlers may crawl the site.
- Preview `robots.txt` after changing the policy.
- Confirm `llms.txt`, page Markdown, and sitemaps match the intended public surface.

## Implementation Notes

- Global seeded crawler rows define the default package stance.
- Site-specific rows override global rows with the same provider, user agent, and path.
- Disable one seeded rule for one site with a site-specific override instead of changing package defaults for every site.
- Keep crawler policy UI copy clear about the search/training split.
