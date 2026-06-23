# Using Payments

This guide is for staff who handle transactions and owners deciding how to go live safely. Every step uses the labels you see on screen.

## Using Payments (editor how-to)

### How to view transactions

1. Go to **Payments**.
2. Open **Transactions** to see every payment and its status.
3. Click a transaction for its full detail and receipt.

### How to issue a refund

1. Open the transaction you need to refund.
2. Click **Issue refund**.
3. Confirm the amount. The refund shows under **Refunds** once processed.

### How to reconcile

1. Compare the **Transactions** list with your processor's records.
2. Investigate any payment that doesn't match.
3. Use the transaction detail and refund records to resolve differences.

## Rolling out Payments (for owners)

### Turn on first

- **Sandbox (test) mode.** Connect your processor in test mode and run test payments before taking real money.

### Add when needed

| Need | Enable |
| --- | --- |
| Take real payments | Live mode, once test payments work end to end |
| Handle returns | A clear refund policy and the **Issue refund** flow |

### Don't enable yet

- Don't switch to live mode until a full test payment and refund have worked in sandbox.

### Who does what

| Role | First useful screen |
| --- | --- |
| Staff | **Transactions**: view payments, issue refunds |
| Site owner | **Connection settings**: manage the processor and go-live |

## Troubleshooting for editors

| What you see | What it means | What to do |
| --- | --- | --- |
| A payment is missing | It may still be processing, or failed | Check the transaction status; failed payments show a reason |
| A refund didn't go through | The refund failed at the processor | Open the transaction and retry; check the processor connection |
| The processor won't connect | Keys or connection settings are wrong | Re-check the connection settings with your developer |
| A customer disputes a charge | The transaction detail holds the record | Open the transaction for the receipt and details to respond |
