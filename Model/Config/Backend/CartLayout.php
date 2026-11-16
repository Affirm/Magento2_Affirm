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

namespace Astound\Affirm\Model\Config\Backend;

use Magento\Framework\App\Config\Value;

/**
 * Shopping Cart layout for the Affirm Express Checkout button. An empty value falls back to the
 * default markup, which must stay in sync with the default in etc/config.xml.
 *
 * @package Astound\Affirm\Model\Config\Backend
 */
class CartLayout extends Value
{
    const DEFAULT_LAYOUT = <<<'HTML'
<!-- ko if: isVisible() -->
<div class="affirm-express-checkout">
    <button type="button"
            id="affirm-express-btn-cart"
            class="action primary affirm-express-checkout-button"
            data-bind="click: startExpressCheckout, attr: {title: $t('Checkout with Affirm Express')}">
        <span><!-- ko i18n: 'Checkout with Affirm Express' --><!-- /ko --></span>
    </button>
</div>
<!-- /ko -->
HTML;

    /**
     * Replace an empty value with the default layout before it is saved.
     *
     * @return $this
     */
    public function beforeSave()
    {
        if (trim((string)$this->getValue()) === '') {
            $this->setValue(self::DEFAULT_LAYOUT);
        }

        return parent::beforeSave();
    }
}
