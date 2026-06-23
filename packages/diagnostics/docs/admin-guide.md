# Using Diagnostics

This guide is for owners and operators who check whether the site is healthy. Every step uses the labels you see on screen.

## What you're looking at

| Screen or signal | What it tells you |
| --- | --- |
| **System health** | The overall status of your site's checks |
| **Cache health** | Whether caching is working |
| Queue health | Whether background jobs are running |
| **Content graph** | Whether content links and dependencies are healthy |

## What to do when...

| Situation | What to do |
| --- | --- |
| Everything is green | Nothing to do; the site is healthy |
| A cache check warns | Clearing the cache often resolves it; otherwise note it for your developer |
| Queue or background jobs look stuck | This usually needs a developer; share the check result |
| You see a red check you don't understand | Send the check and its message to your developer |

## Settings and retention

- This is a read-only view. Looking at it does not change anything.
- Use it as a first stop when something seems wrong, before escalating.
