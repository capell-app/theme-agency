# Agent Delivery Overview

Agent Delivery is the public-safe machine-readable delivery layer from the AI-era web roadmap.

It exposes published page content in two forms:

- Page manifests for compact public page facts.
- Semantic chunks for long-form or documentation-style consumption.

It does not run prompts, expose admin capabilities, create drafts, or authenticate agents. Those concerns stay in `capell-app/agent-bridge` and `capell-app/ai-orchestrator`.

The committed JSON response captures remain in `docs/screenshots.json` as runner evidence for the public API contract. They are not promoted as buyer-facing marketplace screenshots until Agent Delivery has a styled endpoint explorer, admin surface, or other Capell UI scenario that explains the output without showing raw JSON as product media.
