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

namespace Astound\Affirm\Helper\Payment;
use \Magento\Payment\Api\PaymentMethodListInterface;
use \Astound\Affirm\Model\Rule;

class Data extends \Magento\Payment\Helper\Data
{
    public $_allRules = null;

    /**
     * Product collection factory
     *
     * @var \Astound\Affirm\Model\Rule
     */
    public $modelRule;

    /**
     * Product collection factory
     *
     * @var \Magento\Payment\Api\PaymentMethodListInterface
     */
    public $paymentMethodListInterface;

    public function __construct(
        PaymentMethodListInterface $paymentMethodListInterface,
        Rule $modelRule
    )
    {
        $this->paymentMethodListInterface = $paymentMethodListInterface;
        $this->modelRule = $modelRule;
    }


    public function getStoreMethods($store = null, $quote = null)
    {
        $methods = $this->paymentMethodListInterface->getActiveList($store);
        if (!$quote) {
            return $methods;
        }

        $address = $quote->getShippingAddress();
        foreach ($methods as $k => $method) {
            foreach ($this->getRules($address) as $rule) {
                if ($rule->restrict($method)) {
                    if ($rule->validate($address)) {
                        unset($methods[$k]);
                    }
                }
            }
        }

        return $methods;
    }

    public function getRules($address)
    {
        if ($this->_allRules === null) {
            $this->_allRules = $this->modelRule->getCollection()->addAddressFilter($address)->load();
            foreach ($this->_allRules as $rule) {
                $rule->afterLoad();
            }
        }

        return $this->_allRules;
    }
}