<?php
namespace OnePica\Affirm\Api;

/**
 * Interface OrderServiceManagerInterface
 *
 * @package OnePica\Affirm\Api
 * @api
 */
interface AffirmCheckoutManagerInterface
{
    /**
     * Init checkout and get retrieve increment id
     * form affirm checkout
     *
     * @return string
     */
    public function initCheckout();
}
