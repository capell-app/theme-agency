# Using Frontend Optimizer

This guide is for owners and operators. Frontend Optimizer makes your pages load faster by streamlining the files visitors download and by inlining the styles needed for the top of the page. Most of the time it runs on its own and there is nothing to do. Occasionally you may turn an option on or off, or clear the optimised files after a big change. The advanced settings are best left to your developer. No technical knowledge needed. Every step uses the labels you see on screen.

## Using Frontend Optimizer (how-to)

### How to turn faster page loads on

1. Open **Frontend Optimizer** in the admin to reach its settings.
2. Under **Critical CSS**, switch on **Enable critical CSS**. This inlines the styles needed for the top of the page so visitors see content sooner, before the rest of the stylesheets load.
3. Save.

### How to let the optimiser keep itself up to date

1. Open the **Frontend Optimizer** settings.
2. Switch on **Automatic generation**. When a page is missing its optimised styles, the optimiser queues the work to rebuild them in the background.
3. Save. From now on the optimised files refresh on their own as pages are visited.

If your developer has not set up background processing, leave **Automatic generation** off and ask them before turning it on, so the heavy work does not run inside a visitor's page load.

### How to choose how widely optimisation applies

1. Open the **Frontend Optimizer** settings.
2. Set the **Profile scope** to decide how the optimised styles are grouped (for example per layout). A broader scope reuses one optimised set across more pages; a narrower scope tailors it more closely.
3. Save.

### How to clear optimised files after a big change

1. After a large design or content change, the streamlined files may be out of date.
2. Ask whoever manages the site to clear the optimised files, or use the clear option if it is available to you.
3. The files rebuild on their own as visitors load pages, so clearing them is safe.

### How to leave the advanced settings alone

1. The **Frontend Optimizer** settings include advanced options such as **Max inline CSS bytes**, **Viewports**, **Fold multiplier**, **Extra fold pixels**, **Playwright wait strategy**, and **Playwright timeout**.
2. These control exactly how the top-of-page styles are measured and built. The safe defaults work for most sites.
3. Do not change these unless your developer advises it.

## Rolling out Frontend Optimizer (for owners)

### Turn on first

- **Enable critical CSS.** This is the main speed-up and is safe to switch on. Get it working before touching anything else.

### Add when needed

| Need                                          | What to use                                                   |
| --------------------------------------------- | ------------------------------------------------------------- |
| Show content sooner at the top of the page    | **Enable critical CSS**                                       |
| Keep optimised files fresh automatically      | **Automatic generation** (needs background processing set up) |
| Control how optimised styles are grouped      | **Profile scope**                                             |
| Fine-tune how top-of-page styles are measured | The advanced settings, with developer guidance only           |

### Who does what

| Role       | What they do                                                                                |
| ---------- | ------------------------------------------------------------------------------------------- |
| Site owner | Switches on **Enable critical CSS**, leaves advanced settings at default                    |
| Developer  | Sets up background processing, tunes the advanced options, confirms the optimiser is active |

## Troubleshooting

| What you see                                    | What it means                                                | What to do                                                                           |
| ----------------------------------------------- | ------------------------------------------------------------ | ------------------------------------------------------------------------------------ |
| A page looks wrong after a big change           | The optimised files are out of date                          | Clear the optimised files so they rebuild as pages load                              |
| You are unsure about a setting                  | It is one of the advanced tuning options                     | Leave the safe defaults on and ask your developer before changing it                 |
| The site still feels slow                       | Optimisation may be off, or the cause is elsewhere           | Confirm **Enable critical CSS** is on; raise persistent slowness with your developer |
| Turning on **Automatic generation** worries you | The background work needs proper queueing to run out of band | Confirm with your developer that background processing is set up first               |
