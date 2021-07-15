<?php
/**
 *
 *  * BSD 3-Clause License
 *  *
 *  * Copyright (c) 2018, Affirm
 *  * All rights reserved.
 *  *
 *  * Redistribution and use in source and binary forms, with or without
 *  * modification, are permitted provided that the following conditions are met:
 *  *
 *  *  Redistributions of source code must retain the above copyright notice, this
 *  *   list of conditions and the following disclaimer.
 *  *
 *  *  Redistributions in binary form must reproduce the above copyright notice,
 *  *   this list of conditions and the following disclaimer in the documentation
 *  *   and/or other materials provided with the distribution.
 *  *
 *  *  Neither the name of the copyright holder nor the names of its
 *  *   contributors may be used to endorse or promote products derived from
 *  *   this software without specific prior written permission.
 *  *
 *  * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 *  * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 *  * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 *  * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 *  * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 *  * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 *  * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 *  * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 *  * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 *  * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */

namespace Astound\Affirm\Block\Promotion\Pixel;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\View\Element\Template;
use Astound\Affirm\Model\Ui\ConfigProvider;
use Astound\Affirm\Gateway\Helper\Util;
use Astound\Affirm\Helper\Pixel;
use Astound\Affirm\Logger\Logger;

/**
 * Class Confirm
 *
 * @package Astound\Affirm\Block\Pixel
 */
class Confirm extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_salesOrderCollection;

    protected $affirmPixelHelper;

    /**
     * @param Template\Context $context
     * @param ConfigProvider   $configProvider
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderCollection
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param Pixel          $helperPixelAffirm
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        ConfigProvider $configProvider,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderCollection,
        \Magento\Checkout\Model\Session $checkoutSession,
        Pixel $helperPixelAffirm,
        Logger $logger,
        array $data = []
    ) {
        $this->affirmPixelHelper = $helperPixelAffirm;
        $this->_salesOrderCollection = $salesOrderCollection;
        $this->configProvider = $configProvider;
        $this->_checkoutSession = $checkoutSession;
        $this->logger = $logger;
        parent::__construct($context, $data);
    }

    /**
     * Render information about specified orders and their items
     *
     *
     * @return string|void
     */
    public function getOrdersTrackingCode()
    {
        $result = array();
        $result['method'] = 'trackOrderConfirmed';

        $order = $this->_checkoutSession->getLastRealOrder();

        $result['parameter'][0] = array();
        $result['parameter'][0]['orderId'] = $order->getIncrementId();
        $result['parameter'][0]['currency'] = $order->getOrderCurrencyCode();
        $result['parameter'][0]['total'] = Util::formatToCents($order->getBaseGrandTotal());
        $result['parameter'][0]['paymentMethod'] = $order->getPayment()->getMethod();

        $result['parameter'][1] = null;

        $strictBool = True;
        foreach ($result['parameter'][0] as $item) {
            if (empty($item)) {
                $strictBool = False;
                break;
            }
        }

        $result['parameter'][2] = $strictBool;

        $this->logger->debug('Astound\Affirm\Block\Promotion\Pixel\Confirm::Confirm', $result);
        return $result;
    }

    /**
     * Render GA tracking scripts and return empty if pixel tracking is not enabled or if sandbox mode is enabled
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->affirmPixelHelper->isTrackPixelEnabledConfig() || $this->affirmPixelHelper->isSandboxMode()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * Get options for
     *
     * @return string
     */
    public function getOptions()
    {
        $options = [];
        $configProvider = $this->configProvider->getConfig();
        if ($configProvider['payment'][ConfigProvider::CODE]) {
            $config = $configProvider['payment'][ConfigProvider::CODE];
            if ($config && isset($config['script']) && isset($config['apiKeyPublic'])) {
                $options['script'] = $config['script'];
                $options['public_api_key'] = $config['apiKeyPublic'];
            }
        }
        return $options;
    }

    public function getCustomerSessionId()
    {
        $orderIds = $this->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }
        $collection = $this->_salesOrderCollection->create();
        $collection->addFieldToFilter('entity_id', ['in' => $orderIds]);

        foreach ($collection as $order) {
                $customerId = ($order->getCustomerId()) ? $order->getCustomerId() : $guestId = "CUSTOMER-" . $this->affirmPixelHelper->getDateMicrotime();
            }
        return $customerId;
    }
}
