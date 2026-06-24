# Using GA4 Reports

This guide is for owners and operators who want to read their Google Analytics numbers inside the admin. Once your analytics account is connected, the figures appear on a single page so you do not have to leave Capell to see how the site is doing. No technical knowledge is needed. Every step uses the labels you see on screen.

## Using GA4 Reports (how-to)

### How to open the reports page

1. In the admin sidebar, open the **Monitoring** group.
2. Click **GA4 Reports**.
3. The page shows your overview stats, a traffic trend, your top pages, and a setup status panel.

![An administrator reviews GA4 overview stats, traffic trends, top pages, and setup status.](screenshots/ga4-reports-dashboard-page.png)

### How to read the overview numbers

1. Open **Monitoring > GA4 Reports**.
2. The **GA4 Reports overview** at the top shows headline figures such as **Users**, **Sessions**, **Views**, **Events**, **Engagement rate**, and **Avg. session duration**.
3. Each figure also shows a **Vs previous** comparison, so you can see whether it went up or down against the period before.
4. Below the overview, the **GA4 Reports traffic trend** plots visits over time, and **Top GA4 pages** lists your most-visited pages by **Page path** and **Page title**.

### How to check whether analytics is connected

1. Open **Monitoring > GA4 Reports**.
2. Find the setup status panel. It tells you whether GA4 Reports is **Configured**, shows the **Credentials file** state, the **Last status** of the sync, the **Last sync** time, and any **Last error**.
3. If it reports that GA4 Reports is not configured, no numbers will appear until you finish the settings below.

![A site owner distinguishes not-configured and configured GA4 states before enabling sync.](screenshots/ga4-reports-setup-status.png)

### How to connect your Google Analytics

1. Go to **Settings** in the admin and find the **GA4 Reports** section.
2. Turn on **Enable GA4 Reports reporting**.
3. Enter your **GA4 property ID** (the numeric property ID, without any prefix).
4. Set the **Service account credentials path** to where the analytics credentials file is stored on the server. Ask your developer for this if you are not sure.
5. Save the settings.

![A site owner configures property ID, credentials path, route slug, sync window, and enabled state.](screenshots/ga4-reports-settings.png)

### How to choose how much data and how often it syncs

1. Go to **Settings > GA4 Reports**.
2. Set the **Sync window** to control how many days of data the reports cover.
3. Set the **Sync schedule** to control how often the figures refresh from Google Analytics. The default refreshes once a day in the early morning.
4. If you want the reports page to live at a different address, change the **Admin page slug**.
5. Save the settings.

### How to refresh the numbers now

1. Open **Monitoring > GA4 Reports**.
2. Click **Sync now**.
3. Wait a moment. When it finishes you will see how many daily and page rows were synced, and the **Last sync** time updates.
4. You do not enter any numbers by hand. They always come from Google Analytics on the schedule you set, and **Sync now** simply fetches the latest early.

## Troubleshooting

| What you see                               | What it means                                                         | What to do                                                                                                                                          |
| ------------------------------------------ | --------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------- |
| No numbers appear on the reports page      | GA4 Reports is not connected yet                                      | Turn on **Enable GA4 Reports reporting** and fill in the **GA4 property ID** and **Service account credentials path** in **Settings > GA4 Reports** |
| The setup status shows a **Last error**    | The last sync to Google Analytics did not work                        | Read the **Last error** text, then check the credentials path and property ID with your developer                                                   |
| **Last sync** shows **Never**              | No sync has run since the connection was set up                       | Click **Sync now**, or wait for the next scheduled sync                                                                                             |
| A message says the sync is already running | A sync started by the schedule or another person is still in progress | Wait for it to finish, then check the **Last sync** time                                                                                            |
| The numbers look out of date               | The latest scheduled sync has not run yet                             | Click **Sync now** to fetch fresh figures, or check the setup status panel                                                                          |
| A metric is unfamiliar                     | The figure measures one part of visitor behaviour                     | Note what it measures before acting; for example **Engagement rate** is the share of sessions that were active                                      |
