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
 * Base for Affirm Express Checkout button layout settings. The default markup lives in its own file under
 * etc/express_checkout/ and is shown in the admin and saved whenever the stored value is empty.
 *
 * @package Astound\Affirm\Model\Config\Backend
 */
abstract class AbstractLayout extends Value
{
    /**
     * Default markup file name inside etc/express_checkout/; set by each layout setting.
     */
    const DEFAULT_LAYOUT_FILE = '';

    /**
     * Read the default layout markup from its file. Static so consumers (e.g. Config) can use it
     * without instantiating the backend model.
     *
     * @return string
     */
    public static function getDefaultLayout()
    {
        $path = dirname(__DIR__, 3) . '/etc/express_checkout/' . static::DEFAULT_LAYOUT_FILE;

        return rtrim((string)file_get_contents($path));
    }

    /**
     * Show the default layout in the admin when nothing is stored.
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        if (trim((string)$this->getValue()) === '') {
            $this->setValue(static::getDefaultLayout());
        }

        return parent::_afterLoad();
    }

    /**
     * Replace an empty value with the default layout before it is saved.
     *
     * @return $this
     */
    public function beforeSave()
    {
        if (trim((string)$this->getValue()) === '') {
            $this->setValue(static::getDefaultLayout());
        }

        return parent::beforeSave();
    }
}
