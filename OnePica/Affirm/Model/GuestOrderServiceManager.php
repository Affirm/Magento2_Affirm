<?php
/**
 * OnePica
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to codemaster@onepica.com so we can send you a copy immediately.
 *
 * @category  Affirm
 * @package   OnePica_Affirm
 * @copyright Copyright (c) 2016 One Pica, Inc. (http://www.onepica.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace OnePica\Affirm\Model;

use OnePica\Affirm\Api\GuestOrderServiceManagerInterface;
use Magento\Sales\Model\OrderFactory;

/**
 * Class GuestOrderServiceManager
 * This class is responsible for getting data about
 * product increment id for a guest users.
 *
 * @package OnePica\Affirm\Model
 */
class GuestOrderServiceManager implements GuestOrderServiceManagerInterface
{
    /**
     * Injected quote mask factory
     *
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * Order factory
     *
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * Init service manager
     * Inject quote id mask factory and order factory
     *
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     * @param OrderFactory                            $orderFactory
     */
    public function __construct(
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        OrderFactory $orderFactory
    ) {
        $this->orderFactory = $orderFactory;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * Get increment id by mask id
     * Retrieve order increment id for current order
     *
     * @param string $quoteId
     * @return string
     */
    public function getIncrementId($quoteId)
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($quoteId, 'masked_id');
        if ($quoteIdMask->getQuoteId()) {
            $quoteId = $quoteIdMask->getQuoteId();
            $order = $this->orderFactory->create()->load($quoteId, 'quote_id');
            if ($order->getId()) {
                $incrementId = $order->getIncrementId();
                return $incrementId;
            }
        }
        return false;
    }
}
