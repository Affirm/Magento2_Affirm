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
        if (($checkout['checkout_variant'] ?? null) !== self::VARIANT_EXPRESS) {
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
        if (!is_array($response) || empty($response['checkout_id'])) {
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
        $address->addData($this->nameFields($shipping));

        $option = $checkout['shipping_option'] ?? [];
        $method = $option['shipping_type'] ?? ($checkout['shipping_type'] ?? null);
        if (!$method) {
            throw new LocalizedException(__('Affirm did not return a shipping method for this checkout.'));
        }

        $address->setCollectShippingRates(true);
        $address->collectShippingRates();
        if (!$address->getShippingRateByCode($method)) {
            throw new LocalizedException(__('The shipping method chosen in Affirm is no longer available.'));
        }
        $address->setShippingMethod($method);
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
        $data = $this->nameFields($source) + [
            'street' => array_values(array_filter([$fields['line1'], $fields['line2'] ?? null])),
            'city' => $fields['city'],
            'postcode' => $fields['zipcode'],
            'country_id' => $fields['country'],
            'region' => $fields['state'],
        ];
        $email = $billing['email'] ?? ($shipping['email'] ?? null);
        if ($email) {
            $data['email'] = $email;
            $quote->setCustomerEmail($email);
        }
        if (!empty($source['phone_number'])) {
            $data['telephone'] = $source['phone_number'];
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
     * @return array
     */
    private function nameFields(array $contact)
    {
        $name = $contact['name'] ?? [];
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
