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

use OnePica\Affirm\Api\OrderServiceManagerInterface;
use Magento\Sales\Model\OrderFactory;

/**
 * Class OrderServiceManager
 * This class is responsible for getting increment id
 * for registered customers
 *
 * @package OnePica\Affirm\Model
 */
class OrderServiceManager implements OrderServiceManagerInterface
{
    /**
     * Order factory
     *
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * Init order service manager class
     * inject order factory
     *
     * @param OrderFactory $orderFactory
     */
    public function __construct(OrderFactory $orderFactory)
    {
        $this->orderFactory = $orderFactory;
    }

    /**
     * Get order increment id
     *
     * @param int $quoteId
     * @return string
     */
    public function getIncrementId($quoteId)
    {
        if ($quoteId) {
            $order = $this->orderFactory->create()->load($quoteId, 'quote_id');
            if ($order->getId()) {
                $incrementId = $order->getIncrementId();
                return $incrementId;
            }
        }
        return false;
    }
}
