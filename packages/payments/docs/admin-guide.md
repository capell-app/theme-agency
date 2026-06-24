# Using Payments

This guide is for staff who handle transactions and owners deciding how to go live safely. Every step uses the labels you see on screen.

## Using Payments (editor how-to)

### How to view transactions

1. Go to **Payments**.
2. Open **Transactions** to see every payment and its status.
3. Click a transaction for its full detail and receipt.

### How to monitor checkout sessions

1. Open the **Checkout sessions** list.
2. Each row shows the session status, mode, amount, and whether it has been fulfilled.
3. Look for sessions that are still open or expired to spot purchases that did not complete.
4. Open a session for its full detail.

![An administrator monitors checkout session status, mode, amount, and fulfilment state.](screenshots/payments-checkout-sessions.png)

### How to check webhook health

1. Open the **Webhook events** list.
2. Each row shows the event, when it arrived, and whether it has been processed.
3. If events are stuck or failing, your processor connection or webhook secret may need attention.
4. Use this to confirm your processor is talking to the site before and after going live.

![An operator verifies payment webhook intake health and processing status.](screenshots/payments-webhook-events.png)

### How to issue a refund

1. Open the transaction you need to refund.
2. Click **Issue refund**.
3. Confirm the amount. The refund shows under **Refunds** once processed.

### How to reconcile

1. Compare the **Transactions** list with your processor's records.
2. Investigate any payment that doesn't match.
3. Use the transaction detail and refund records to resolve differences.

### How to configure payments before launch

1. Open the payments **Settings** screen.
2. Choose the processor mode (test or live), and enter the publishable key and webhook secret.
3. Set your checkout defaults and the return hosts you allow.
4. Save, then run a test payment before switching to live mode.

![An administrator configures checkout and webhook settings before launch.](screenshots/payments-settings.png)

## Rolling out Payments (for owners)

### Turn on first

- **Sandbox (test) mode.** Connect your processor in test mode and run test payments before taking real money.

### Add when needed

| Need               | Enable                                              |
| ------------------ | --------------------------------------------------- |
| Take real payments | Live mode, once test payments work end to end       |
| Handle returns     | A clear refund policy and the **Issue refund** flow |

### Don't enable yet

- Don't switch to live mode until a full test payment and refund have worked in sandbox.

### Who does what

| Role       | First useful screen                                       |
| ---------- | --------------------------------------------------------- |
| Staff      | **Transactions**: view payments, issue refunds            |
| Site owner | **Connection settings**: manage the processor and go-live |

## Troubleshooting for editors

| What you see                 | What it means                           | What to do                                                     |
| ---------------------------- | --------------------------------------- | -------------------------------------------------------------- |
| A payment is missing         | It may still be processing, or failed   | Check the transaction status; failed payments show a reason    |
| A refund didn't go through   | The refund failed at the processor      | Open the transaction and retry; check the processor connection |
| The processor won't connect  | Keys or connection settings are wrong   | Re-check the connection settings with your developer           |
| A customer disputes a charge | The transaction detail holds the record | Open the transaction for the receipt and details to respond    |
