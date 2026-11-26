# 09 — Payments: HesabPay (Afghanistan) + Stripe (abroad)

## Which gateway for whom

| Student | Gateway | Currency | Notes |
|---|---|---|---|
| Inside Afghanistan | **HesabPay** hosted checkout | AFN | Wallet, AfPay cards and some international cards, depending on merchant setup |
| Inside Afghanistan, no wallet | **Vouchers** sold by agents for cash | AFN | Student types the code at checkout |
| Diaspora, international students, sponsors | **Stripe Checkout** through the US LLC | USD | Cards, Apple Pay, Google Pay |
| Companies / NGOs | Invoice → bank transfer / Stripe invoice | USD or AFN | Manual mark-as-paid in admin |

Show the right options automatically: use the phone country code and IP country to show HesabPay first for +93 numbers, Stripe first for others. Let the user switch.

## HesabPay integration

HesabPay offers a **hosted checkout**: your server creates a payment session with your API key, HesabPay returns a payment URL, the customer pays on HesabPay's page, and HesabPay sends a webhook back.

**Getting access**
1. A HesabPay business (merchant) account must be approved.
2. Log in to the developer portal at `developers.hesab.com` with the business credentials and generate an API key. Get a sandbox key first.
3. Read the current docs at `docs.hesab.com` (payment session, webhooks, API reference). The endpoint used by existing integrations is `https://api.hesab.com/api/v1/payment/create-session`. Confirm the current version in the docs before coding.

**Flow in your LMS**
```
Student taps "Pay with HesabPay"
  → POST /checkout (your server) creates Order(status=pending)
  → HesabPayGateway::createCheckout(order) calls create-session with:
       amount (AFN), order number, item names/qty/price, success_url, cancel_url
  → redirect to the payment URL returned
  → student pays in HesabPay
  → HesabPay calls your webhook  /webhooks/hesabpay
       1. save raw payload in gateway_events (unique event id) → ignore duplicates
       2. verify authenticity as the docs specify (signature/verification call with your API key)
       3. check amount + currency + order number match
       4. mark Payment succeeded, Order paid → fire OrderPaid → create enrollment
  → student returns to success_url → page polls order status until "paid"
```

**Rules**
- Never grant access from the success-page redirect alone; grant it only from a verified webhook.
- Store amounts in AFN as integers.
- A scheduled job re-checks orders that stay pending for more than 30 minutes (if the API provides a status endpoint), then cancels them after 24 hours.
- Keep a daily reconciliation export: your orders vs the HesabPay dashboard.
- Refunds: start them in HesabPay's dashboard and record them in admin, until an API refund is available.
- Memberships: if HesabPay does not offer recurring billing, use **prepaid periods** (1, 3 or 6 months) and send reminders before expiry.

**Example config**
```php
// config/services.php
'hesabpay' => [
    'base_url'   => env('HESABPAY_BASE_URL', 'https://api.hesab.com/api/v1'),
    'api_key'    => env('HESABPAY_API_KEY'),
    'sandbox'    => env('HESABPAY_SANDBOX', true),
],
```

## Stripe integration (via the US LLC)

**Use:** Stripe Checkout (hosted) for one-time course purchases; Stripe Billing (Laravel Cashier) for monthly memberships; Stripe Invoicing for B2B; later **Stripe Connect Express** to pay instructors who live in Stripe-supported countries.

**Compliance first**
- Stripe must follow US OFAC sanctions and keeps a list of high-risk jurisdictions it will not serve. Check that list for Afghanistan when you apply, and design so Stripe handles students and sponsors **outside** Afghanistan.
- Be transparent in your Stripe application about what you sell and where your customers are. An account closed later for undisclosed activity is much worse than a slower approval.
- "Sponsor a learner": the **sponsor** (abroad) pays you for a course seat; you assign the seat to a student. The student never touches Stripe. Get your accountant's view on how to record this.

**Flow**
```
StripeGateway::createCheckout(order) → Stripe Checkout Session (mode=payment, currency=usd,
  metadata[order_id]) → redirect → webhook checkout.session.completed → verify signature with
  STRIPE_WEBHOOK_SECRET → mark order paid → OrderPaid
Memberships → Laravel Cashier subscriptions → webhooks update memberships table
```

## Instructor payouts (marketplace, P3)

| Instructor location | Method |
|---|---|
| Afghanistan | HesabPay transfer to their wallet (manual monthly batch at first) |
| Stripe-supported country | Stripe Connect Express (automatic) |
| Elsewhere | Bank transfer / Wise, monthly, minimum threshold |

Rules: earnings become "available" 14 days after purchase (refund window), monthly payout, minimum $20 / 1,500 AFN, statement for every payout.

## Price display
- Show AFN prices to Afghan users and USD to others. Keep separate price fields; do not auto-convert every day.
- Round to friendly numbers (e.g. 499 AFN, $9.99).

## Checklist
- [ ] HesabPay merchant approved, sandbox key working
- [ ] Webhook verified and idempotent; tested with duplicate and late webhooks
- [ ] Real 10 AFN live test before launch
- [ ] Stripe account approved under the LLC; test mode working; live test with $1
- [ ] Refund, cancel and failed-payment flows tested on both gateways
- [ ] Reconciliation report runs daily
