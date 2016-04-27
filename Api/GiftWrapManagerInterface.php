<?php
namespace OnePica\Affirm\Api;

/**
 * Interface GiftWrapManagerInterface
 *
 * @package OnePica\Affirm\Api
 */
interface GiftWrapManagerInterface
{
    /**
     * Retrieve all gift wrap items
     *
     * @return mixed
     */
    public function getWrapItems();

    /**
     * Retrieve printed card item
     *
     * @return mixed
     */
    public function getPrintedCardItem();
}
