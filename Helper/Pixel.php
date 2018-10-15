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

namespace Astound\Affirm\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Astound\Affirm\Model\Config as Config;

/**
 * Pixel helper
 *
 * @package Astound\Affirm\Helper
 */
class Pixel
{
    /**
     * Scope config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Init
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param Config $configAffirm
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Config $configAffirm
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->affirmPaymentConfig = $configAffirm;
    }

    public function getDateMicrotime()
    {
        $microtime = explode(' ', microtime());
        $msec = $microtime[0];
        $msecArray = explode('.', $msec);
        $date = date('Y-m-d-H-i-s') . '-' . $msecArray[1];
        return $date;
    }

    /**
     * Returns is pixel placement for search query enabled
     *
     * @return bool
     */
    public function isSearchTrackPixelEnabledConfig()
    {
        return $this->affirmPaymentConfig->getPixelValue('add_search');
    }

    /**
     * Returns is pixel placement for product list page enabled
     *
     * @return bool
     */
    public function isProductListTrackPixelEnabledConfig()
    {
        return $this->affirmPaymentConfig->getPixelValue('add_product_list');
    }

    /**
     * Returns is pixel placement for product page enabled
     *
     * @return bool
     */
    public function isProductViewTrackPixelEnabledConfig()
    {
        return $this->affirmPaymentConfig->getPixelValue('add_product_view');
    }

    /**
     * Returns is pixel placement for add to cart action enabled
     *
     * @return bool
     */
    public function isAddCartTrackPixelEnabledConfig()
    {
        return $this->affirmPaymentConfig->getPixelValue('add_cart');
    }

    /**
     * Returns is pixel placement for checkout start action enabled
     *
     * @return bool
     */
    public function isAddChkStartTrackPixelEnabledConfig()
    {
        return $this->affirmPaymentConfig->getPixelValue('add_checkout_start');
    }


    /**
     * Returns is pixel placement for confirmation page enabled
     *
     * @return bool
     */
    public function isCheckoutSuccessPixelEnabledConfig()
    {
        return $this->affirmPaymentConfig->getPixelValue('add_checkout_success');
    }

    /**
     * Add slashes to string and prepares string for javascript.
     *
     * @param string $str
     * @return string
     */
    public function escapeSingleQuotes($str)
    {
        return str_replace("'", "\'", $str);
    }

    /**
    * Based on provided configuration path returns configuration value.
    *
    * @param string $configPath
    * @param string|int $scope
    * @return string
    */
    public function getConfig($configPath, $scope = 'default')
    {
        return $this->scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $scope
        );
    }
}
