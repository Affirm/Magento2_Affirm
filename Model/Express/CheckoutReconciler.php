<?php
namespace Astound\Affirm\Model\Express;

use Astound\Affirm\Gateway\Helper\Request\Action;
use Astound\Affirm\Gateway\Http\Client\ClientService;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;

/**
 * Express Checkout, step 4: after the shopper confirms in Affirm, read the checkout back and make the
 * Magento quote match what they chose (addresses and shipping method), then recalculate totals.
 *
 * The gateway's pre_authorize / order_authorize commands that run on submit then compare the
 * recalculated grand total with Affirm's total (in cents) before anything is authorized, so a mismatch
 * stops the order without leaving an authorized transaction behind.
 */
class CheckoutReconciler
{
    const VARIANT_EXPRESS = 'express';

    /** Temporary fixed shipping method for Express orders (see applyShipping) */
    const TEMP_SHIPPING_METHOD = 'flatrate_flatrate';

    /** @var TransferFactoryInterface */
    private $transferFactory;

    /** @var ClientService */
    private $client;

    /** @var ShippingTotalsCalculator */
    private $calculator;

    public function __construct(
        TransferFactoryInterface $transferFactory,
        ClientService $client,
        ShippingTotalsCalculator $calculator
    ) {
        $this->transferFactory = $transferFactory;
        $this->client = $client;
        $this->calculator = $calculator;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param string $token
     * @param string $countryCode Affirm country code (USA / CAN)
     * @return bool True when the quote was reconciled (Express checkout), false for a standard checkout
     * @throws LocalizedException
     */
    public function reconcile($quote, $token, $countryCode)
    {
        $checkout = $this->readCheckout($token, $countryCode);
        // Affirm marks Express checkouts as merchant.checkout_variant in the read-checkout response
        $variant = $checkout['merchant']['checkout_variant'] ?? ($checkout['checkout_variant'] ?? null);
        if ($variant !== self::VARIANT_EXPRESS) {
            return false;
        }

        $shipping = $checkout['shipping'] ?? [];
        $shippingAddress = $this->addressFields($shipping);
        if (!$quote->getIsVirtual()) {
            if (!$shippingAddress) {
                throw new LocalizedException(__('Affirm did not return a shipping address for this checkout.'));
            }
            $this->applyShipping($quote, $shipping, $shippingAddress, $checkout);
        }
        $this->applyBilling($quote, $checkout['billing'] ?? [], $shipping);

        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();
        return true;
    }

    /**
     * @param string $token
     * @param string $countryCode
     * @return array
     * @throws LocalizedException
     */
    private function readCheckout($token, $countryCode)
    {
        try {
            $transfer = $this->transferFactory->create([
                'body' => '',
                'path' => $token,
                'method' => ClientService::GET,
                'country_code' => $countryCode,
            ]);
            $response = $this->client->placeRequest($transfer);
        } catch (\Exception $e) {
            throw new LocalizedException(__('We could not read your checkout from Affirm. Please try again.'), $e);
        }
        // A real read-checkout response has no checkout_id; errors carry status_code/type/code instead
        if (!is_array($response) || isset($response['status_code']) || empty($response['checkout_status'])) {
            throw new LocalizedException(__('We could not read your checkout from Affirm. Please try again.'));
        }
        return $response;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param array $shipping
     * @param array $addressFields
     * @param array $checkout
     * @throws LocalizedException
     */
    private function applyShipping($quote, array $shipping, array $addressFields, array $checkout)
    {
        try {
            $this->calculator->applyAddress($quote, $addressFields);
        } catch (ExpressException $e) {
            throw new LocalizedException(__('The shipping address from Affirm cannot be used: %1', $e->getMessage()));
        }

        $address = $quote->getShippingAddress();
        // Affirm can return an empty shipping name; the billing name is the shopper's name then
        $address->addData($this->nameFields($shipping, $checkout['billing'] ?? []));

        // TODO: temporary. Affirm's sandbox returns its own sample shipping options (e.g. standard, two-day)
        // unless it can reach our shipping callback, so use flat rate for now instead of Affirm's shipping_type.
        $method = self::TEMP_SHIPPING_METHOD;

        $address->setCollectShippingRates(true);
        $address->collectShippingRates();
        if (!$address->getShippingRateByCode($method)) {
            throw new LocalizedException(__('The shipping method chosen in Affirm is no longer available.'));
        }
        $address->setData('shipping_method', $method);
    }

    /**
     * Billing falls back to the shipping address when Affirm returns none
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param array $billing
     * @param array $shipping
     */
    private function applyBilling($quote, array $billing, array $shipping)
    {
        $source = $this->addressFields($billing) ? $billing : $shipping;
        $fields = $this->addressFields($source);
        if (!$fields) {
            return;
        }
        $data = $this->nameFields($billing, $shipping) + [
            'street' => array_values(array_filter([$fields['line1'], $fields['line2'] ?? null])),
            'city' => $fields['city'],
            'postcode' => $fields['zipcode'],
            'country_id' => $fields['country'],
            'region' => $fields['state'],
        ];
        $email = $billing['email'] ?? ($shipping['email'] ?? null);
        if ($email) {
            $data['email'] = $email;
            $quote->setData('customer_email', $email);
        }
        $phone = $billing['phone_number'] ?? ($shipping['phone_number'] ?? null);
        if (!empty($phone)) {
            $data['telephone'] = $phone;
        }
        $quote->getBillingAddress()->addData($data);
    }

    /**
     * Affirm address object -> flat line1/line2/city/state/zipcode/country, or [] when incomplete
     *
     * @param array $contact
     * @return array
     */
    private function addressFields(array $contact)
    {
        $address = $contact['address'] ?? $contact;
        foreach (['line1', 'city', 'state', 'zipcode', 'country'] as $required) {
            if (empty($address[$required])) {
                return [];
            }
        }
        return [
            'line1' => $address['line1'],
            'line2' => $address['line2'] ?? '',
            'city' => $address['city'],
            'state' => $address['state'],
            'zipcode' => $address['zipcode'],
            'country' => $address['country'],
        ];
    }

    /**
     * @param array $contact
     * @param array $fallback Contact whose name is used when $contact has none
     * @return array
     */
    private function nameFields(array $contact, array $fallback = [])
    {
        $name = $contact['name'] ?? [];
        if (!array_filter($name) && $fallback) {
            $name = $fallback['name'] ?? [];
        }
        $first = $name['first'] ?? null;
        $last = $name['last'] ?? null;
        if ($first === null && $last === null && !empty($name['full'])) {
            $parts = explode(' ', trim($name['full']), 2);
            $first = $parts[0];
            $last = $parts[1] ?? $parts[0];
        }
        return array_filter(['firstname' => $first, 'lastname' => $last], 'strlen');
    }
}
