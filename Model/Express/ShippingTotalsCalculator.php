<?php
namespace Astound\Affirm\Model\Express;

use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\RegionFactory;

/**
 * Computes shipping options and per-option totals for a quote and an Affirm shipping address.
 *
 * The quote is only mutated in memory and is NEVER saved: the shopper's persisted quote is unchanged.
 */
class ShippingTotalsCalculator
{
    /** Currencies without a minor unit. */
    const ZERO_DECIMAL_CURRENCIES = [
        'BIF', 'CLP', 'DJF', 'GNF', 'ISK', 'JPY', 'KMF', 'KRW', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF',
    ];

    /** @var CountryFactory */
    private $countryFactory;

    /** @var RegionFactory */
    private $regionFactory;

    public function __construct(CountryFactory $countryFactory, RegionFactory $regionFactory)
    {
        $this->countryFactory = $countryFactory;
        $this->regionFactory = $regionFactory;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param array $shipping Affirm shipping object (line1, line2, city, state, zipcode, country)
     * @param string $currency
     * @return array shipping_options[]
     * @throws ExpressException
     */
    public function calculate($quote, array $shipping, $currency)
    {
        $this->applyAddress($quote, $shipping);

        $address = $quote->getShippingAddress();
        $address->setCollectShippingRates(true);
        $address->collectShippingRates();

        $rates = [];
        foreach ($address->getGroupedAllShippingRates() as $carrierRates) {
            foreach ($carrierRates as $rate) {
                if (!$rate->getErrorMessage()) {
                    $rates[] = $rate;
                }
            }
        }
        if (!$rates) {
            throw new ExpressException(
                ExpressException::SHIPPING_METHOD_UNAVAILABLE,
                'No shipping options available for this address'
            );
        }

        $options = [];
        foreach ($rates as $rate) {
            $code = $rate->getCarrier() . '_' . $rate->getMethod();
            $address->setShippingMethod($code);
            $quote->setTotalsCollectedFlag(false);
            $quote->collectTotals();
            $this->assertInStock($quote);

            $options[] = [
                'shipping_type' => $code,
                'shipping_label' => trim($rate->getCarrierTitle() . ' - ' . $rate->getMethodTitle(), ' -'),
                'shipping_amount' => $this->toMinorUnits($address->getShippingAmount(), $currency),
                'tax_amount' => $this->toMinorUnits($address->getTaxAmount(), $currency),
                'total' => $this->toMinorUnits($quote->getGrandTotal(), $currency),
            ];
        }
        return $options;
    }

    /**
     * Item cost excluding tax and shipping, in minor units
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param string $currency
     * @return int
     */
    public function getSubtotal($quote, $currency)
    {
        return $this->toMinorUnits($quote->getSubtotal(), $currency);
    }

    /**
     * @param float|string|null $amount
     * @param string $currency
     * @return int
     */
    public function toMinorUnits($amount, $currency)
    {
        $factor = in_array(strtoupper((string)$currency), self::ZERO_DECIMAL_CURRENCIES, true) ? 1 : 100;
        return (int)round((float)$amount * $factor);
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param array $shipping
     * @return void
     * @throws ExpressException
     */
    private function applyAddress($quote, array $shipping)
    {
        $country = $this->countryFactory->create()->loadByCode((string)$shipping['country']);
        $countryId = $country->getCountryId();
        if (!$countryId) {
            throw new ExpressException(
                ExpressException::INVALID_SHIPPING_ADDRESS,
                'Unknown country',
                ['shipping.country']
            );
        }
        $allowed = $quote->getStore()->getConfig('general/country/allow');
        if ($allowed !== null && !in_array($countryId, explode(',', (string)$allowed), true)) {
            throw new ExpressException(
                ExpressException::UNSUPPORTED_SHIPPING_ZONE,
                'Shipping to this country is not supported',
                ['shipping.country']
            );
        }

        $region = $this->regionFactory->create()->loadByCode((string)$shipping['state'], $countryId);
        if (!$region->getId()) {
            $region = $this->regionFactory->create()->loadByName((string)$shipping['state'], $countryId);
        }

        $street = [$shipping['line1']];
        if (!empty($shipping['line2'])) {
            $street[] = $shipping['line2'];
        }
        $quote->getShippingAddress()->addData([
            'street' => $street,
            'city' => $shipping['city'],
            'postcode' => $shipping['zipcode'],
            'country_id' => $countryId,
            'region' => $region->getId() ? $region->getName() : $shipping['state'],
            'region_id' => $region->getId() ?: null,
        ]);
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return void
     * @throws ExpressException
     */
    private function assertInStock($quote)
    {
        foreach ($quote->getAllItems() as $item) {
            if ($item->getHasError()) {
                throw new ExpressException(
                    ExpressException::INVENTORY_UNAVAILABLE,
                    'One or more items are unavailable'
                );
            }
        }
    }
}
