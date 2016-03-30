<?php
namespace OnePica\Affirm\Api;

/**
 * Interface OrderServiceManagerInterface
 *
 * @package OnePica\Affirm\Api
 * @api
 */
interface OrderServiceManagerInterface
{
    /**
     * Get increment id
     *
     * @param int $quoteId
     * @return string
     */
    public function getIncrementId($quoteId);
}
