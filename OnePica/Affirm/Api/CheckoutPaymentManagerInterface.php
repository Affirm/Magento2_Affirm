<?php

namespace OnePica\Affirm\Api;

/**
 * Interface CheckoutPaymentManagerInterface
 *
 * @package OnePica\Affirm\Api
 * @api
 */
interface CheckoutPaymentManagerInterface
{
    /**
     * Init payment
     *
     * @return bool|string
     */
    public function initPayment();

    /**
     * Verify affirm selection
     *
     * @return bool|mixed
     */
    public function verifyAffirm();
}
