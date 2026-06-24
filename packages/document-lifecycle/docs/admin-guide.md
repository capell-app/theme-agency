# Using Document Lifecycle

This guide is for staff who manage controlled documents and owners setting the approval process. Every step uses the labels you see on screen.

## Using Document Lifecycle (editor how-to)

### How to add and version a document

1. Go to **Controlled documents**.
2. Add a document and upload its file.
3. When it changes, upload a new version. The old one stays in **All versions**.

![Review controlled documents and see which policy or legal records have published versions.](screenshots/controlled-documents-index.png)

### How to update a document's details

1. Open a document from **Controlled documents**.
2. Change its title, lifecycle status, or other details. The key field stays fixed so links keep working.
3. Save your changes.

![Update the title, lifecycle status, or details of a controlled document.](screenshots/controlled-document-edit.png)

### How to set review and expiry dates

1. Open the document.
2. Set its review date and **Expires** date.
3. Save. You will be reminded before it expires.

### How to send a document for approval

1. Open the document.
2. Send it for approval to the right person.
3. Track its approval status.

### How to check published versions

1. Open a document and go to its publications.
2. Each published version is locked and kept on record, with its own hash, revision reference, and publish time.
3. Use this list when you need to confirm exactly what was published and when.

![Check locked publication versions, hashes, revision references, and publish times for a document.](screenshots/controlled-document-publications.png)

### How to track acceptances

1. Open the document's acceptances.
2. See who has accepted the current version.
3. Use **Export evidence CSV** if you need a record.

![Confirm which version a person or workflow accepted and when that acceptance was recorded.](screenshots/controlled-document-acceptances.png)

### How to archive a document

1. Open the document.
2. Click **Archive document** when it is retired.
3. It moves out of the active list but stays on record.

## Rolling out Document Lifecycle (for owners)

### Turn on first

- **Versioning and expiry reminders.** Get documents under version control with review dates before adding approvals.

### Add when needed

| Need                        | Enable                                  |
| --------------------------- | --------------------------------------- |
| Require sign-off before use | Approvals                               |
| Prove people read a policy  | Acceptances and **Export evidence CSV** |

### Don't enable yet

- Don't add complex approval chains before the basics (versions and expiry dates) are in routine use.

### Who does what

| Role           | First useful screen                             |
| -------------- | ----------------------------------------------- |
| Document owner | **Controlled documents**: version and set dates |
| Site owner     | Acceptances and evidence exports                |

## Troubleshooting for editors

| What you see                            | What it means                                                | What to do                                                                          |
| --------------------------------------- | ------------------------------------------------------------ | ----------------------------------------------------------------------------------- |
| A document expired without warning      | Its expiry date wasn't set                                   | Open it and set the **Expires** date                                                |
| People are using an old version         | The new version wasn't published, or acceptances are pending | Confirm the current version and chase outstanding acceptances                       |
| I can't prove someone accepted a policy | Acceptance wasn't recorded                                   | Use **Export evidence CSV** for recorded acceptances; ensure acceptance is required |
| A retired document still shows          | It wasn't archived                                           | Open it and **Archive document**                                                    |
