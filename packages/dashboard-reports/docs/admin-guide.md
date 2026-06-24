# Using Dashboard Reports

This guide is for the people who read the admin dashboard: owners and editors who want to see at a glance how publishing is going and whether any pages need attention. No technical knowledge needed. Dashboard Reports adds two cards to your admin dashboard and, if you want it, a regular email with the same numbers. Every step uses the labels you see on screen.

## Using Dashboard Reports (how-to)

### How to read the Publishing trend card

1. Open your admin dashboard. This is the first screen you see when you sign in.
2. Find the **Publishing trend** card.
3. The card shows two series over the current reporting window: **Published pages** (pages that went live) and **Scheduled pages** (pages set to go live later).
4. Compare the two to see whether your team has been publishing more or less than usual, and how much is still queued up to go out.

![An administrator checks whether publishing and scheduling activity changed during the current reporting window.](screenshots/publishing-trend-dashboard-widget.png)

### How to read the Content health card

1. On the admin dashboard, find the **Content health** card.
2. It lists pages that may need attention, grouped by issue: **Expired pages**, **Pages without URLs**, **Scheduled pages**, and **Stale pages**.
3. Each issue shows how many pages it affects. A count of zero means nothing to do for that issue.
4. Use this card as your daily or weekly check before a publishing review.

![An editor spots page health issues that need attention before the next publishing review.](screenshots/content-health-dashboard-widget.png)

### How to act on content health issues

1. On the **Content health** card, note which issue has a count above zero, for example **Expired pages** or **Stale pages**.
2. Go to your page list.
3. Use the **Content health** filter at the top of the list to show only the pages flagged for that issue.
4. Open each flagged page and fix what it needs: give a page without a URL a proper address, refresh a stale page, or review an expired one.
5. Come back to the dashboard later to confirm the count has gone down.

### How to show or hide each card

1. Go to your dashboard settings.
2. Find the **Dashboard Reports** group.
3. Turn **Publishing trend** on or off, and turn **Content health** on or off, depending on what you want to see.
4. Save. The cards you turned off no longer appear on the dashboard, so it stays focused on what matters to you.

![A site owner confirms the Dashboard Reports widgets can be enabled or hidden through dashboard settings.](screenshots/dashboard-report-settings.png)

### How to export the numbers

1. From a report, choose to export.
2. You get a file you can open in a spreadsheet, holding the underlying numbers behind the cards.
3. The export includes columns such as **Bucket**, **Label**, **Count**, **Published pages**, **Scheduled pages**, and **Total**, so you have a record you can keep, share, or chart yourself.

### How to understand the scheduled digest email

1. If the digest is switched on, you receive an email with the subject **Dashboard Reports digest** on a regular schedule.
2. It contains the same **Publishing trend** and **Content health** numbers you see on the dashboard.
3. Use it as a reminder to check in without having to open the admin yourself. If the numbers look off, open the dashboard for the full picture.

## Rolling out Dashboard Reports (for owners)

### Turn on first

- **Content health.** This is the card that tells you when something needs fixing. Start here so your team gets in the habit of clearing flagged pages.

### Add when needed

| Need                                           | Enable                                   |
| ---------------------------------------------- | ---------------------------------------- |
| See how publishing activity is trending        | The **Publishing trend** card            |
| Spot pages that need attention before a review | The **Content health** card              |
| Get the numbers without opening the admin      | The **Dashboard Reports digest** email   |
| Keep a record or build your own chart          | The CSV export of the underlying numbers |

### Keep it focused

- Use the **Dashboard Reports** settings group to hide any card a given team does not use. A dashboard with only the cards people act on gets read more often than a cluttered one.

### Who does what

| Role       | First useful screen                                                               |
| ---------- | --------------------------------------------------------------------------------- |
| Editor     | The dashboard **Content health** card: see what needs fixing, then open the pages |
| Site owner | The dashboard **Publishing trend** card: see whether publishing is keeping pace   |

## Troubleshooting

| What you see                                | What it means                                                       | What to do                                                                                         |
| ------------------------------------------- | ------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------- |
| A card is missing from the dashboard        | It has been turned off in settings                                  | Open the **Dashboard Reports** settings group and turn the card back on                            |
| **Content health** shows pages without URLs | Those pages have no public address yet                              | Filter the page list by **Content health**, open each page, and give it a URL                      |
| **Stale pages** count keeps growing         | Pages have not been updated in a while                              | Filter for them, review the content, and refresh or retire each one                                |
| A number on a card looks wrong              | The card counts a specific thing over the current reporting window  | Open the page list and use the **Content health** filter to see the exact pages behind the count   |
| The **Published pages** line looks flat     | Little has gone live in this window, or work is still **Scheduled** | Check the **Scheduled pages** series to see what is queued to publish later                        |
| No **Dashboard Reports digest** email       | The digest is switched off, or it is not yet due to send            | Confirm the digest is enabled; if it should have arrived, ask your developer to check the schedule |
