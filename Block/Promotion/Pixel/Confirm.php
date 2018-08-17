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

/**
 * Class Confirm
 *
 * @package Astound\Affirm\Block\Promotion
 */
class Confirm extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_salesOrderCollection;

    protected $affirmPixelHelper;

    /**
     * @param Template\Context $context
     * @param ConfigProvider   $configProvider
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderCollection
     * @param Pixel          $helperPixelAffirm
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        ConfigProvider $configProvider,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderCollection,
        Pixel $helperPixelAffirm,
        array $data = []
    ) {
        $this->affirmPixelHelper = $helperPixelAffirm;
        $this->_salesOrderCollection = $salesOrderCollection;
        $this->configProvider = $configProvider;
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
        $orderIds = $this->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }

        $collection = $this->_salesOrderCollection->create();
        $collection->addFieldToFilter('entity_id', ['in' => $orderIds]);
        $result = [];

        foreach ($collection as $order) {
            if ($order->getIsVirtual()) {
                $address = $order->getBillingAddress();
            } else {
                $address = $order->getShippingAddress();
            }

            $result[] = sprintf("affirm.analytics.trackOrderConfirmed({
'affiliation': '%s',
'orderId': '%s',
'currency': '%s',
'total': '%s',
'tax': '%s',
'shipping': '%s',
'paymentMethod': '%s'
},[",
                $this->escapeJsQuote($this->_storeManager->getStore()->getFrontendName()),
                $order->getIncrementId(),
                $order->getOrderCurrencyCode(),
                Util::formatToCents($order->getBaseGrandTotal()),
                Util::formatToCents($order->getBaseTaxAmount()),
                Util::formatToCents($order->getBaseShippingAmount()),
                $order->getPayment()->getMethod()
            );
            foreach ($order->getAllVisibleItems() as $item) {
                $result[] = sprintf("{
'productId': '%s',
'name': '%s',
'price': '%s',
'quantity': '%s'
},",
                    $this->escapeJsQuote($item->getSku()),
                    $this->escapeJsQuote($item->getName()),
                    Util::formatToCents($item->getBasePrice()),
                    $item->getQtyOrdered()
                );
            }
            $result[] = sprintf("]);");
        }
        return implode("\n", $result);
    }

    /**
     * Render GA tracking scripts
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->affirmPixelHelper->isAffirmAnalyticsAvailable()) {
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
                $options['sessionId'] = ($this->getCustomerSessionId())? $this->getCustomerSessionId() : '';
            }
        }
        return json_encode($options);
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
