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
use Magento\Framework\Exception\LocalizedException;

/**
 * Validates that the configured value is an absolute https URL. An empty value falls back to the
 * default callback URL (secure base URL of the scope + affirm/express/shippingtotals).
 *
 * @package Astound\Affirm\Model\Config\Backend
 */
class Url extends Value
{
    /**
     * Reject non-URL values before they are saved. An empty value is replaced by the default URL.
     *
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $value = trim((string)$this->getValue());
        if ($value === '') {
            $value = $this->getDefaultUrl();
        } elseif (!$this->isValidUrl($value)) {
            throw new LocalizedException(
                __('Please enter a valid URL starting with https://, including a full domain name.')
            );
        }
        $this->setValue($value);

        return parent::beforeSave();
    }

    /**
     * Default callback URL: the secure base URL of the scope being saved plus the endpoint path.
     *
     * @return string
     */
    private function getDefaultUrl(): string
    {
        $baseUrl = (string)$this->_config->getValue(
            'web/secure/base_url',
            $this->getScope(),
            $this->getScopeId()
        );

        return rtrim($baseUrl, '/') . '/affirm/express/shippingtotals';
    }

    /**
     * Check for an absolute https URL whose host is a dotted domain name (e.g. example.com) or an IP address.
     *
     * Single-label hosts such as "http://gee" are rejected.
     *
     * @param string $value
     * @return bool
     */
    private function isValidUrl(string $value): bool
    {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            return false;
        }
        $scheme = strtolower((string)parse_url($value, PHP_URL_SCHEME));
        $host = (string)parse_url($value, PHP_URL_HOST);
        if ($scheme !== 'https' || $host === '') {
            return false;
        }
        if (filter_var(trim($host, '[]'), FILTER_VALIDATE_IP)) {
            return true;
        }

        return (bool)preg_match('/^([a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/i', $host);
    }
}
