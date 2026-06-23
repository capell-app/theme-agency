# Using Login Audit

This guide is for owners and operators who watch sign-in activity. Every step uses the labels you see on screen.

## What you're looking at

| Screen or signal | What it tells you |
| --- | --- |
| **Access Logs** | Every sign-in: who, when, and from where |
| **Failed login** alert | Someone tried and failed to sign in |
| **New login device** alert | A user signed in from a device not seen before |
| **Suspicious login** alert | A sign-in that looks unusual (for example an odd time or place) |

## What to do when...

| Situation | What to do |
| --- | --- |
| You want to check a specific user | Filter **Access Logs** by that user to see their recent sign-ins |
| You see repeated failed logins | Treat it as a possible attack; consider tightening password rules and alerting the user |
| A new device alert is unexpected | Contact the user to confirm it was them; if not, have them reset their password |
| Compliance asks for records | Export the log for the period requested |

## Settings and retention

- Turn on **Alert on Failed Logins**, **Alert on New Devices**, and **Alert on Suspicious Logins** as needed.
- Old entries are removed after the number of days you set, so review or export before they age out.
