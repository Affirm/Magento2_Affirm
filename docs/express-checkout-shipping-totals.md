# Express Checkout: Shipping & Totals endpoint

When a shopper uses the Affirm Express Checkout button, Affirm's servers call this endpoint to get the shipping
options and totals for the address the shopper entered inside Affirm. Affirm documents the contract at
<https://docs.affirm.com/developers/docs/express-checkout-api>.

| | |
|---|---|
| URL | `https://<store>/affirm/express/shippingtotals` (default; editable in **Stores > Configuration > Sales > Payment Methods > Affirm > Affirm Express Checkout Settings**, `payment/affirm_gateway/affirm_store_settings/shipping_and_total_callback_url`, https only) |
| Method | `POST` |
| Implemented by | `Controller/Express/ShippingTotals.php` |
| Called by | Affirm (server to server, no Magento session) |

## Why it is not in Swagger

It is a storefront controller (`routes.xml` front name `affirm`), not a REST route, so it is not declared in
`etc/webapi.xml` and does not appear in `/swagger`. This is intentional. Affirm requires an exact error body
(`{"errors": [{"error_code", "message", "fields"}]}` with HTTP 422). Magento's REST layer renders every error
itself (`message`, `errors[].message/parameters`, `code`, `parameters`) and a service cannot change that shape, so
a REST route could not return the response Affirm expects.

## Request

Headers:

* `Content-Type: application/json` (required, otherwise `415`)
* `X-Affirm-Signature: t=<unix timestamp>,v1=<hex signature>` (required)

The signature is `HMAC-SHA512` over `"<t>.<raw request body>"`, keyed with the store's **Private API Key**
for the configured mode. It is rejected if the timestamp is more than 300 seconds from the server time
(`SignatureValidator::TOLERANCE_SECONDS`).

Body:

```json
{
  "order_id": "000000017",
  "currency": "USD",
  "shipping": {
    "line1": "650 California St",
    "line2": "Floor 12",
    "city": "San Francisco",
    "state": "CA",
    "zipcode": "94108",
    "country": "USA"
  }
}
```

`order_id` is the reserved order increment id of the active quote, `currency` is an uppercase ISO 4217 code, and
`shipping.line1`, `city`, `state`, `zipcode` and `country` (ISO 3166-1 alpha-3) are required. `line2` is optional.

## Success response (`200`)

Amounts are integers in minor currency units (cents).

```json
{
  "order_id": "000000017",
  "currency": "USD",
  "subtotal": 2200,
  "shipping_options": [
    {
      "shipping_type": "flatrate_flatrate",
      "shipping_label": "Flat Rate - Fixed",
      "shipping_amount": 500,
      "tax_amount": 164,
      "total": 2864
    }
  ]
}
```

`shipping_type` is the Magento rate code (`<carrier>_<method>`). Affirm returns the option the shopper picked in
the checkout it later sends back, and that code is what the order uses as its shipping method. `subtotal`
excludes tax and shipping; `total` is the quote grand total with that option applied.

## Error responses

| Status | When | Body |
|---|---|---|
| `404` | Express Checkout is disabled for the website | `[]` |
| `415` | `Content-Type` is not `application/json` | `[]` |
| `401` | Missing or invalid signature, stale timestamp, or no private key configured | `[]` |
| `422` | Anything below | `{"errors": [{"error_code": "...", "message": "...", "fields": ["..."]}]}` (`fields` only when relevant) |

`error_code` values (`Model/Express/ExpressException.php`):

| `error_code` | Meaning |
|---|---|
| `INVALID_SHIPPING_ADDRESS` | Body is malformed, or the address is missing required fields or does not exist |
| `UNSUPPORTED_SHIPPING_ZONE` | Valid address in a country or region the store does not ship to |
| `ORDER_NOT_FOUND` | `order_id` does not match an active quote |
| `CURRENCY_MISMATCH` | `currency` differs from the quote's currency |
| `INVENTORY_UNAVAILABLE` | An item in the quote is no longer available |
| `SHIPPING_METHOD_UNAVAILABLE` | No shipping rate is available for the address |
| `INTERNAL_SERVER_ERROR` | Unexpected failure while calculating (details are in the Magento logs) |

Affirm's page does not specify the response for an invalid signature, so `401` with an empty body is this
module's choice. Confirm it with your Affirm account manager if it matters.

## Testing it locally

Affirm's servers must be able to reach the URL. A local hostname such as `https://magento.test/...` is not
reachable from Affirm, and without a reachable callback the Express flow falls back to Affirm's own sample
shipping options (for example `standard` / `two-day`) instead of your store's rates. Use a public HTTPS tunnel and
set it as the callback URL in the admin setting above.
