<?php
namespace OnePica\Affirm\Api;

/**
 * Interface OrderServiceManagerInterface
 *
 * @package OnePica\Affirm\Api
 * @api
 */
interface GuestOrderServiceManagerInterface
{
    /**
     * Get Increment id
     *
     * @param string $quoteId
     * @return string
     */
    public function getIncrementId($quoteId);
}
