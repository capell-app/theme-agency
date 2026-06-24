# Using Login Audit

This guide is for owners and operators who watch sign-in activity. Login Audit records who signed in, when, from where, and on which device, keeps a record of failed attempts, and can alert you to suspicious sign-ins. It helps you spot unusual activity and keep a record for compliance. No technical knowledge is needed. Every step uses the labels you see on screen.

## Using Login Audit (how-to)

### How to review sign-in activity

1. In the admin sidebar, open the **Users** group.
2. Click **Access Logs**.
3. The list shows each sign-in with the user who logged in, the **IP address**, the device, the location, and the time.
4. Both successful and failed attempts appear, so you can see the full history at a glance.

![An administrator reviews successful and failed authentication events for the demo admin user.](screenshots/login-audits-admin-index.png)

### How to filter the log to find specific events

1. Open **Users > Access Logs**.
2. Open the filter panel at the top of the list.
3. Filter by access status to show only successful or only failed sign-ins.
4. Filter by a login date range to focus on a particular period.
5. Use the trusted-device filter to separate known devices from new ones, and filter on whether a row was cleared by the user.

![An administrator filters authentication events by success state, login date range, or cleared-by-user state.](screenshots/login-audit-table-filters.png)

### How to export the log for compliance

1. Open **Users > Access Logs**.
2. Apply any filters so the list shows only the period or events you need.
3. Click **Export CSV**.
4. Save the file and share it with whoever requested the records.

### How to add the Access Logs panel to a dashboard

1. Open your dashboard configuration in the admin.
2. In the list of available panels, find **Access Logs**.
3. Add it to a dashboard so recent sign-in activity is visible without opening the full log.

![A site owner confirms that Access Logs is available in dashboard configuration after the package is installed.](screenshots/dashboard-widget.png)

### How to check one user's sign-in history while editing them

1. Open the user's record in the admin (this requires **Show Access Logs on Users** to be turned on in settings).
2. Look at the access summary on the user's edit screen. It shows recent login counts, failed attempts, recent devices, and active sessions.
3. Open the **Access log history** for that user to see each of their authentication attempts, including which devices are trusted and when each was last active.

### How to turn on security alerts

1. Go to **Settings** in the admin and find the **Login Audit security alerts** section.
2. Turn on **Alert on Failed Logins** to be notified about failed sign-in attempts.
3. Turn on **Alert on New Devices** to be notified when someone signs in from a device not seen before.
4. Turn on **Alert on Suspicious Logins** to be notified when a sign-in is flagged as unusual.
5. Save the settings.

### How to tune suspicious-login detection

1. Go to **Settings** and find the **Security & Access** section.
2. Turn on **Detect Suspicious Logins** to flag repeated failures, rapid location changes, and successful logins straight after recent failures.
3. Set the **Failed Login Threshold** and **Failed Login Window** to control how many failed attempts within a period count as suspicious.
4. Turn on **Check Unusual Login Times** only if your team signs in within predictable hours.
5. Save the settings.

![A site owner configures retention, IP tracking, visibility, and the user-resource bridge for login audit data.](screenshots/login-audit-settings-screen.png)

### How to control privacy and what is recorded

1. Go to **Settings > Security & Access**.
2. Turn **Track User IP Addresses** off if your site policy requires access logs without IP retention.
3. Turn **Resolve Geo Location** on only if you have a location provider configured and a lawful basis for keeping location data.
4. Use **Show Access Logs** and **Show Access Logs on Users** to control where the log and the per-user summary appear.
5. Save the settings.

### How to set how long entries are kept

1. Go to **Settings > Security & Access**.
2. Set **Access Log Retention** to the number of days you want to keep entries.
3. Save the settings. Older entries are removed automatically after that many days, so review or export anything you need before it ages out. The **Last Purged** line shows when old entries were last cleared.

## Troubleshooting

| What you see                                         | What it means                                                               | What to do                                                                                                  |
| ---------------------------------------------------- | --------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------- |
| You want to check one person                         | You need that user's sign-ins only                                          | Open **Users > Access Logs** and filter by the user, or open their access summary from the user edit screen |
| Repeated failed sign-ins for one account             | A possible attempt to break in                                              | Treat it as suspicious; contact the user, and consider tightening sign-in rules with your developer         |
| A new-device alert you did not expect                | Someone signed in from an unfamiliar device                                 | Contact the user to confirm it was them; if not, have them reset their password                             |
| The per-user summary is missing from a user's record | **Show Access Logs on Users** is off, or the user model does not support it | Turn on **Show Access Logs on Users** in **Settings > Security & Access**                                   |
| Compliance asks for records                          | You need a file of the log                                                  | Filter **Access Logs** to the period requested and click **Export CSV**                                     |
| Old entries are gone                                 | They aged out past **Access Log Retention**                                 | Increase **Access Log Retention** going forward, and export future records before they age out              |
| No location appears on entries                       | **Resolve Geo Location** is off, or no location provider is set up          | Turn on **Resolve Geo Location** in settings if you have a provider and a lawful basis for it               |
