# Using Publishing Studio

This guide is for editors who change content under review, reviewers who approve it, and owners deciding how much of the review-and-release process to switch on. Every step uses the labels you see on screen.

## Using Publishing Studio (editor how-to)

### How to start a release workspace and make edits

1. Open **Publishing Studio**.
2. Click **Create release workspace**.
3. Make your content edits inside the workspace. They stay private here.
4. Your changes are staged in the workspace until the release is approved and published.

### How to submit your work for review

1. In your workspace, send it for review. Its status changes to **Submitted**.
2. A reviewer sees it under **Approvals & change requests**.
3. If they ask for changes, the status shows **Changes requested**. Make the edits and submit again.

### How to approve a release (for reviewers)

1. Go to **Approvals & change requests**.
2. Open the submitted workspace and review the staged changes.
3. Mark it **Approved**, or request changes with a note.

### How to schedule a release

1. Once a workspace is **Approved**, open the **Content Scheduler**.
2. Set the date and time you want it to go live.
3. The release publishes itself at that moment. A workspace must be approved before it can be scheduled.

### How to share a preview link

1. In the workspace, create a **Preview link**.
2. Share the link with anyone who needs to see the work before it is live.
3. Revoke the link when you no longer need it.

### How to roll back a release

1. Open **Rollback-ready versions**.
2. Pick the earlier version you want to restore.
3. Roll back. The live site returns to that version in seconds.

## Rolling out Publishing Studio (for owners)

### Turn on first

- **Review-first publishing.** Begin with workspaces and approvals so no change reaches the live site without a second pair of eyes.

### Add when needed

| Need | Enable |
| --- | --- |
| Plan releases ahead of time | The **Content Scheduler** |
| Recover quickly from a bad change | **Rollback-ready versions** |
| Show work to stakeholders early | **Preview links** |

### Don't enable yet

- Hold off on scheduling and rollback until your team is comfortable with the basic review flow. Add them as the team grows.

### Who does what

| Role | First useful screen |
| --- | --- |
| Editor | A **release workspace**: make and submit changes |
| Reviewer | **Approvals & change requests**: approve or request changes |
| Site owner | The **Content Scheduler** and **Rollback-ready versions**: oversee timing and recovery |

## Troubleshooting for editors

| What you see | What it means | What to do |
| --- | --- | --- |
| I can't schedule my release | The workspace isn't approved yet | Submit it for review and wait for a reviewer to mark it **Approved** |
| My edits aren't on the live site | They are still staged in a workspace | Submit, get approval, then publish or schedule the release |
| A preview link stopped working | The link was revoked or expired | Create a new **Preview link** and share that |
| I published the wrong thing | The previous version is still recoverable | Open **Rollback-ready versions** and roll back |
| The release won't publish at its scheduled time | The release window is closed or the workspace is blocked | Check the scheduler message and the readiness notes, then re-check approval and timing |
