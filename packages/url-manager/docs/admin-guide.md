# Using URL Manager

This guide is for editors who keep links working and owners deciding how to handle moved pages. Every step uses the labels you see on screen.

## Using URL Manager (editor how-to)

### How to create a redirect

1. Go to **URL Manager**.
2. Click **Create redirect**.
3. Enter the old URL (the address that should move) and the new URL (where it should go).
4. **Activate redirect** and save. Visitors to the old address now land on the new one.

### How to fix a link that moved

1. Find the old address under **404 opportunities** or create a redirect directly.
2. Use **Convert to redirect** to point it at the correct page.
3. Test the old link to confirm it now lands in the right place.

### How to import many redirects at once

1. In **URL Manager**, open the import tool.
2. Use **Download template** to get the correct spreadsheet format.
3. Fill it in, then **Preview import** to check it before applying.
4. **Import redirects** to add them all.

### How to export your redirects

1. In **URL Manager**, choose **Export redirects**.
2. Save the file as a backup or to review your redirects in a spreadsheet.

## Rolling out URL Manager (for owners)

### Turn on first

- **Redirects for any moved or renamed pages.** Before or right after a site change, redirect old URLs so no links break.

### Add when needed

| Need | Enable |
| --- | --- |
| Recover visitors from dead links | **404 opportunities** to redirect |
| Migrate many URLs at once | **Import redirects** from a spreadsheet |

### Don't enable yet

- Don't create redirects you don't need. Each one is a rule to maintain. Add them when a page actually moves.

### Who does what

| Role | First useful screen |
| --- | --- |
| Editor | **Create redirect** and **404 opportunities** |
| Site owner | The **Redirects** list: keep it tidy and loop-free |

## Troubleshooting for editors

| What you see | What it means | What to do |
| --- | --- | --- |
| A redirect isn't working | It isn't active | Open it and **Activate redirect** |
| The page keeps reloading or errors | A redirect loop (two redirects point at each other) | Remove one side of the loop so the chain ends at a real page |
| A bulk import didn't apply | The file format was wrong, or preview wasn't confirmed | Use **Download template**, fix the file, **Preview import**, then import |
| Visitors still hit a 404 | The old URL has no redirect yet | Find it in **404 opportunities** and add a redirect |
